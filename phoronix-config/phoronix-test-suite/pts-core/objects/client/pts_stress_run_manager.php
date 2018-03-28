<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2015 - 2017, Phoronix Media
	Copyright (C) 2015 - 2017, Michael Larabel

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 3 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

class pts_stress_run_manager extends pts_test_run_manager
{
	private $multi_test_stress_start_time;
	private $stress_tests_executed;
	private $sensor_data_archived;
	private $sensor_data_archived_units;
	private $loop_until_time;
	private $thread_collection_dir;
	private $sensors_to_monitor;
	private $stress_subsystems_active;
	private $stress_child_thread = false;
	private $stress_logger;
	private $stress_log_event_call = false;

	public function multi_test_stress_run_execute($tests_to_run_concurrently = 3, $total_loop_time = false)
	{
		ini_set('memory_limit','8192M'); // XXX testing

		$continue_test_flag = true;
		pts_client::$display->test_run_process_start($this);
		$this->allow_test_cache_share = false;
		$this->disable_dynamic_run_count();
		$this->multi_test_stress_run = $tests_to_run_concurrently;
		$possible_tests_to_run = $this->get_tests_to_run();
		if(is_numeric($total_loop_time))
		{
			$total_loop_time = $total_loop_time * 60;
		}
		$this->loop_until_time = is_numeric($total_loop_time) && $total_loop_time > 1 ? time() + $total_loop_time : false;
		$this->stress_tests_executed = array();
		$this->multi_test_stress_start_time = time();
		$this->thread_collection_dir = pts_client::create_temporary_directory('stress-threads');
		$this->sensors_to_monitor = array();
		$this->sensor_data_archived = array();
		$this->sensor_data_archived_units = array();
		$this->stress_logger = new pts_logger(null, 'phoronix-test-suite-stress-' . date('ymdHi') . '.log');
		$this->stress_logger->log('Log Initialized');
		putenv('FORCE_TIMES_TO_RUN=1');

		// Determine how frequently to print reports / status updates
		$time_report_counter = time();
		if($total_loop_time == 'infinite')
		{
			$report_counter_frequency = 5 * 60;
		}
		else if($total_loop_time > (3 * 60 * 60))
		{
			$report_counter_frequency = 30 * 60;
		}
		else if($total_loop_time > (60 * 60))
		{
			$report_counter_frequency = 10 * 60;
		}
		else if($total_loop_time > (20 * 60))
		{
			$report_counter_frequency = 5 * 60;
		}
		else if($total_loop_time > (10 * 60))
		{
			$report_counter_frequency = 2 * 60;
		}
		else
		{
			$report_counter_frequency = 60;
		}

		// SIGTERM handling
		if(function_exists('pcntl_signal'))
		{
			declare(ticks = 1);
			pcntl_signal(SIGTERM, array($this, 'sig_handler'));
			pcntl_signal(SIGHUP, array($this, 'sig_handler'));
			pcntl_signal(SIGINT, array($this, 'sig_handler'));
		}
		else
		{
			$this->stress_print_and_log('PHP PCNTL support is needed if wishing to gracefully interrupt testing with signals.' . PHP_EOL);
		}

		// SENSOR SETUP WORK
		$sensor_interval_frequency = is_numeric($total_loop_time) && $total_loop_time > 1 ? max($total_loop_time / 1000, 3) : 6;
		$sensor_time_since_last_poll = time();
		foreach(phodevi::supported_sensors(array('cpu_temp', 'cpu_usage', 'gpu_usage', 'gpu_temp', 'hdd_read_speed', 'hdd_write_speed', 'memory_usage', 'swap_usage', 'sys_temp')) as $sensor)
		{
			$supported_devices = call_user_func(array($sensor[2], 'get_supported_devices'));

			if($supported_devices === NULL)
			{
				$supported_devices = array(null);
			}

			foreach($supported_devices as $device)
			{
				$sensor_object = new $sensor[2](0, $device);
				if(phodevi::read_sensor($sensor_object) != -1)
				{
					array_push($this->sensors_to_monitor, $sensor_object);
					$this->sensor_data_archived[phodevi::sensor_object_name($sensor_object)] = array();
					$this->sensor_data_archived_units[phodevi::sensor_object_name($sensor_object)] = phodevi::read_sensor_object_unit($sensor_object);
				}
			}
		}

		$table = array();
		foreach(phodevi::system_hardware(false) as $component => $value)
		{
			$table[] = array($component . ': ', $value);
		}
		foreach(phodevi::system_software(false) as $component => $value)
		{
			$table[] = array($component . ': ', $value);
		}
		$this->stress_print_and_log('SYSTEM INFORMATION: ' . PHP_EOL . phodevi::system_centralized_view() . PHP_EOL . PHP_EOL);

		// BEGIN THE LOOP
		while(!empty($possible_tests_to_run))
		{
			if($continue_test_flag == false)
				break;

			if(($time_report_counter + $report_counter_frequency) <= time() && count(pts_file_io::glob($this->thread_collection_dir . '*')) > 0)
			{
				// ISSUE STATUS REPORT
				$this->stress_print_and_log($this->stress_status_report());
				$time_report_counter = time();
			}

			$this->stress_subsystems_active = array();
			$test_identifiers_active = array();

			while(($waited_pid = pcntl_waitpid(-1, $status, WNOHANG)) > 0)
			{
				pts_file_io::unlink($this->thread_collection_dir . $waited_pid);
			}

			foreach(pts_file_io::glob($this->thread_collection_dir . '*') as $pid_file)
			{
				$pid = basename($pid_file);
				$waited_pid = pcntl_waitpid($pid, $status, WNOHANG);

				if(!file_exists('/proc/' . $pid))
				{
					unlink($pid_file);
					continue;
				}

				$test = new pts_test_profile(file_get_contents($pid_file));

				// Count the number of tests per stress subsystems active
				if(!isset($this->stress_subsystems_active[$test->get_test_hardware_type()]))
				{
					$this->stress_subsystems_active[$test->get_test_hardware_type()] = 1;
				}
				else
				{
					$this->stress_subsystems_active[$test->get_test_hardware_type()] += 1;
				}

				if(!in_array($test->get_identifier(), $test_identifiers_active))
				{
					$test_identifiers_active[] = $test->get_identifier();
				}

			}

			if(!empty($possible_tests_to_run) && count(pts_file_io::glob($this->thread_collection_dir . '*')) < $this->multi_test_stress_run && (!$total_loop_time || $total_loop_time == 'infinite' || $this->loop_until_time > time()))
			{
				shuffle($possible_tests_to_run);

				$test_to_run = false;
				$test_run_index = -1;

				if(getenv('DONT_BALANCE_TESTS_FOR_SUBSYSTEMS') == false)
				{
					// Try to pick a test for a hardware subsystem not yet being explicitly utilized
					foreach($possible_tests_to_run as $i => $test)
					{
						$hw_subsystem_type = $test->test_profile->get_test_hardware_type();

						if(!isset($this->stress_subsystems_active[$hw_subsystem_type]) && !$this->skip_test_check($test))
						{
							$test_run_index = $i;
							$test_to_run = $test;
							break;
						}
					}
				}

				if($test_run_index == -1 && getenv('DONT_TRY_TO_ENSURE_TESTS_ARE_UNIQUE') == false)
				{
					// Try to pick a test from a test profile not currently active
					foreach($possible_tests_to_run as $i => $test)
					{
						if(!in_array($test->test_profile->get_identifier(), $test_identifiers_active) && !$this->skip_test_check($test))
						{
							$test_run_index = $i;
							$test_to_run = $test;
							break;
						}
					}
				}

				if($test_run_index == -1)
				{
					// Last resort, just randomly pick a true "random" test
					$test_run_index = array_rand(array_keys($possible_tests_to_run));
					$test_to_run = $possible_tests_to_run[$test_run_index];

					if($this->skip_test_check($test_to_run))
					{
						continue;
					}
				}

				$pid = pcntl_fork();
				if($pid == -1)
				{
					$this->stress_print_and_log('Forking Failure.');
				}
				if($pid)
				{
					// parent
					$test_identifier = $test_to_run->test_profile->get_identifier();
					file_put_contents($this->thread_collection_dir . $pid, $test_identifier);

					if(!isset($this->stress_tests_executed[$test_identifier]))
					{
						$this->stress_tests_executed[$test_identifier] = 1;
					}
					else
					{
						$this->stress_tests_executed[$test_identifier]++;
					}
				}
				else
				{
					// child
					$this->stress_child_thread = true;
					//echo PHP_EOL . pts_client::cli_colored_text('Starting: ', 'green', true) . $test_to_run->test_profile->get_identifier() . ($test_to_run->get_arguments_description() != null ? ' [' . $test_to_run->get_arguments_description()  . ']' : null) . PHP_EOL;
					$continue_test_flag = $this->process_test_run_request($test_to_run);
					//echo PHP_EOL . pts_client::cli_colored_text('Ended: ', 'red', true) . $test_to_run->test_profile->get_identifier() . ($test_to_run->get_arguments_description() != null ? ' [' . $test_to_run->get_arguments_description()  . ']' : null) . PHP_EOL;
					pts_file_io::unlink($this->thread_collection_dir . getmypid());
					//echo PHP_EOL;
					exit(0);
				}
				if($total_loop_time == false)
				{
					unset($possible_tests_to_run[$test_run_index]);
				}
				else if($total_loop_time == 'infinite')
				{
					//$this->stress_print_and_log('Continuing to test indefinitely' . PHP_EOL);
				}
				else
				{
					if($this->loop_until_time > time())
					{
						$time_left = ceil(($this->loop_until_time - time()) / 60);
					//	echo 'Continuing to test for ' . $time_left . ' more minutes' . PHP_EOL;
					}
				}
			}

			if(is_numeric($this->loop_until_time) && $this->loop_until_time < time())
			{
				// Time to Quit
				$this->stress_print_and_log('TEST TIME EXPIRED; NO NEW TESTS WILL EXECUTE; CURRENT TESTS WILL FINISH' . PHP_EOL);
				// This halt-testing touch will let tests exit early (i.e. between multiple run steps)
				file_put_contents(PTS_USER_PATH . 'halt-testing', 'stress-run is done... This text really is not important, just checking for file presence.');
				// Final report
				$this->stress_print_and_log($this->final_stress_report());
				break;
			}

			if($sensor_time_since_last_poll + $sensor_interval_frequency < time())
			{
				// Time to do a sensor reading
				foreach($this->sensors_to_monitor as &$sensor_object)
				{
					$this->sensor_data_archived[phodevi::sensor_object_name($sensor_object)][] = phodevi::read_sensor($sensor_object);
				}
				$sensor_time_since_last_poll = time();
			}
		}

		putenv('FORCE_TIMES_TO_RUN');
		pts_file_io::delete($this->thread_collection_dir, null, true);

		foreach($this->get_tests_to_run() as $run_request)
		{
			// Remove cache shares
			foreach(pts_file_io::glob($run_request->test_profile->get_install_dir() . 'cache-share-*.pt2so') as $cache_share_file)
			{
				unlink($cache_share_file);
			}
		}

		// Wait for child processes to complete
		//pcntl_waitpid(-1, $status);

		// Restore default handlers
		pcntl_signal(SIGTERM, SIG_DFL);
		pcntl_signal(SIGINT, SIG_DFL);
		pcntl_signal(SIGHUP, SIG_DFL);

		return true;
	}
	protected function skip_test_check(&$test)
	{
		$hw_subsystem_type = $test->test_profile->get_test_hardware_type();
		$subsystem_limit_check = getenv('LIMIT_STRESS_' . strtoupper($hw_subsystem_type) . '_TESTS_COUNT');

		if(isset($this->stress_subsystems_active[$hw_subsystem_type]) && $subsystem_limit_check && $subsystem_limit_check <= $this->stress_subsystems_active[$hw_subsystem_type])
		{
			// e.g. LIMIT_STRESS_GRAPHICS_TESTS_COUNT=2, don't want more than that number per subsystem concurrently
			return true;
		}

		return false;
	}
	public function action_on_stress_log_set($call)
	{
		if(is_callable($call))
		{
			$this->stress_log_event_call = $call;
		}
	}
	protected function stress_print_and_log($msg)
	{
		if($this->stress_logger && $msg != null)
		{
			echo $msg;
			$this->stress_logger->log($msg, false);
		}
		if($this->stress_log_event_call)
		{
			call_user_func($this->stress_log_event_call, $this->stress_logger->get_clean_log());
		}
	}
	public function get_stress_log()
	{
		return $this->stress_logger->get_clean_log();
	}
	public function sig_handler($signo)
	{
		// Time to Quit
		// This halt-testing touch will let tests exit early (i.e. between multiple run steps)
		file_put_contents(PTS_USER_PATH . 'halt-testing', 'stress-run is done... This text really is not important, just checking for file presence.');

		if($this->stress_child_thread == false)
		{
			$this->stress_print_and_log('SIGNAL RECEIVED; QUITTING...' . PHP_EOL);
			// Final report
			$this->stress_print_and_log($this->final_stress_report());
		}
		exit();
	}
	protected function stress_status_report()
	{
		return $this->final_stress_report(false);
	}
	protected function final_stress_report($is_final = true)
	{
		if(!$is_final)
		{
			$report_buffer = PHP_EOL . '###### STRESS RUN INTERIM REPORT ####' . PHP_EOL;
		}
		else
		{
			$report_buffer = PHP_EOL . '###### SUMMARY REPORT ####' . PHP_EOL;
		}

		$report_buffer .= strtoupper(date('F j H:i T')) . PHP_EOL;
		$report_buffer .= pts_client::cli_just_bold('START TIME: ') . date('F j H:i T', $this->multi_test_stress_start_time) . PHP_EOL;
		$report_buffer .= pts_client::cli_just_bold('ELAPSED TIME: ') . pts_strings::format_time(time() - $this->multi_test_stress_start_time) . PHP_EOL;
		if($this->loop_until_time > time())
		{
			$report_buffer .= pts_client::cli_just_bold('TIME REMAINING: ') . pts_strings::format_time($this->loop_until_time - time()) . PHP_EOL;
		}
		else
		{
			$report_buffer .= 'WAITING FOR CURRENT TEST RUN QUEUE TO FINISH.' . PHP_EOL;
		}
		$report_buffer .= pts_client::cli_just_bold('SYSTEM IP: ') . pts_network::get_local_ip() . PHP_EOL;
		$report_buffer .= pts_client::cli_just_bold('HOSTNAME: ') . phodevi::read_property('system', 'hostname') . PHP_EOL;
		$report_buffer .= pts_client::cli_just_bold('# OF CONCURRENT TESTS: ') . $this->multi_test_stress_run . PHP_EOL . PHP_EOL;

		if(!$is_final)
		{
			$report_buffer .= 'TESTS CURRENTLY ACTIVE: ' . PHP_EOL;

			$table = array();
			foreach(pts_file_io::glob($this->thread_collection_dir . '*') as $pid_file)
			{
				$test = pts_file_io::file_get_contents($pid_file);
				$table[] = array($test, '[PID: ' . basename($pid_file) . ']');
			}
			$report_buffer .= pts_user_io::display_text_table($table, '   - ', 2) . PHP_EOL;
		}

		$report_buffer .= PHP_EOL . pts_client::cli_just_bold('TESTS IN RUN QUEUE: ') . PHP_EOL . PHP_EOL;
		$tiq = array();
		foreach($this->get_tests_to_run() as $i => $test)
		{
			$bar = pts_client::cli_colored_text(strtoupper($test->test_profile->get_title()) . ' [' . $test->test_profile->get_identifier() . ']', 'blue', true);
			if(!isset($tiq[$bar]))
			{
				$tiq[$bar] = array();
			}

			array_push($tiq[$bar], $test->get_arguments_description());
		}
		foreach($tiq as $test => $args)
		{
			$report_buffer .= $test;
			foreach($args as $arg)
			{
				if(!empty($arg))
				{
					$report_buffer .= PHP_EOL . '     ' . $arg;
				}
			}
			$report_buffer .= PHP_EOL;
		}
		$report_buffer .= PHP_EOL . pts_client::cli_just_bold('SYSTEM INFORMATION: ') . PHP_EOL;
		$table = array();
		foreach(phodevi::system_hardware(false) as $component => $value)
		{
			$table[] = array(pts_client::cli_just_bold($component . ': '), $value);
		}
		foreach(phodevi::system_software(false) as $component => $value)
		{
			$table[] = array(pts_client::cli_just_bold($component . ': '), $value);
		}
		$report_buffer .= pts_user_io::display_text_table($table, '     ', 1) . PHP_EOL . PHP_EOL;

		if(!empty($this->stress_tests_executed))
		{
			$table = array(array(pts_client::cli_just_bold('TESTS EXECUTED'), pts_client::cli_just_bold('TIMES CALLED')));
			ksort($this->stress_tests_executed);

			foreach($this->stress_tests_executed as $test => $times)
			{
				$table[] = array(pts_client::cli_just_bold($test) . ': ', $times);
			}
			$report_buffer .= pts_user_io::display_text_table($table, '     ', 2) . PHP_EOL . PHP_EOL;
		}

		$report_buffer .= pts_client::cli_just_bold('SENSOR DATA: ') . PHP_EOL;
		$table = array(array(pts_client::cli_just_bold('SENSOR'), pts_client::cli_just_bold('MIN'), pts_client::cli_just_bold('AVG'), pts_client::cli_just_bold('MAX')));
		foreach($this->sensor_data_archived as $sensor_name => &$sensor_data)
		{
			if(empty($sensor_data))
				continue;

			$max_val = max($sensor_data);

			if($max_val > 0)
			{
				$table[] = array(pts_client::cli_just_bold($sensor_name . ': '),
					pts_math::set_precision(min($sensor_data), 2),
					pts_math::set_precision(array_sum($sensor_data) / count($sensor_data), 2),
					pts_math::set_precision($max_val, 2),
					$this->sensor_data_archived_units[$sensor_name]);
			}
		}
		$report_buffer .= pts_user_io::display_text_table($table, '     ', 2) . PHP_EOL;
		$report_buffer .= '######' . PHP_EOL;
		return $report_buffer;
	}
}

?>
