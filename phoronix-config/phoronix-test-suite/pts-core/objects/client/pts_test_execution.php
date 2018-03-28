<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2017, Phoronix Media
	Copyright (C) 2008 - 2017, Michael Larabel

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

class pts_test_execution
{
	protected static function test_run_error(&$test_run_manager, &$test_run_request, $error_msg)
	{
		$error_obj = array($test_run_manager, $test_run_request, $error_msg);
		pts_module_manager::module_process('__event_run_error', $error_obj);
		pts_client::$display->test_run_error($error_msg);
	}
	protected static function test_run_instance_error(&$test_run_manager, &$test_run_request, $error_msg)
	{
		$error_obj = array($test_run_manager, $test_run_request, $error_msg);
		pts_module_manager::module_process('__event_run_error', $error_obj);
		pts_client::$display->test_run_instance_error($error_msg);
	}
	public static function run_test(&$test_run_manager, &$test_run_request)
	{
		$test_identifier = $test_run_request->test_profile->get_identifier();
		$extra_arguments = $test_run_request->get_arguments();
		$full_output = pts_config::read_bool_config('PhoronixTestSuite/Options/General/FullOutput', 'FALSE');

		// Do the actual test running process
		$test_directory = $test_run_request->test_profile->get_install_dir();

		if(!is_dir($test_directory))
		{
			return false;
		}

		$lock_file = $test_directory . 'run_lock';
		if(pts_client::create_lock($lock_file) == false && $test_run_manager->is_multi_test_stress_run() == false)
		{
			self::test_run_error($test_run_manager, $test_run_request, 'The ' . $test_identifier . ' test is already running.');
			return false;
		}

		$test_run_request->active = new pts_test_result_buffer_active();
		$test_run_request->generated_result_buffers = array();
		$execute_binary = $test_run_request->test_profile->get_test_executable();
		$times_to_run = $test_run_request->test_profile->get_times_to_run();
		$ignore_runs = $test_run_request->test_profile->get_runs_to_ignore();
		$test_type = $test_run_request->test_profile->get_test_hardware_type();
		$allow_cache_share = $test_run_request->test_profile->allow_cache_share() && $test_run_manager->allow_test_cache_share();
		$min_length = $test_run_request->test_profile->get_min_length();
		$max_length = $test_run_request->test_profile->get_max_length();
		$is_monitoring = false;

		if($test_run_request->test_profile->get_environment_testing_size() > 1 && ceil(disk_free_space($test_directory) / 1048576) < $test_run_request->test_profile->get_environment_testing_size())
		{
			// Ensure enough space is available on disk during testing process
			self::test_run_error($test_run_manager, $test_run_request, 'There is not enough space (at ' . $test_directory . ') for this test to run.');
			pts_client::release_lock($lock_file);
			return false;
		}

		$to_execute = $test_run_request->test_profile->get_test_executable_dir();
		$pts_test_arguments = trim($test_run_request->test_profile->get_default_arguments() . ' ' . str_replace($test_run_request->test_profile->get_default_arguments(), '', $extra_arguments) . ' ' . $test_run_request->test_profile->get_default_post_arguments());
		$extra_runtime_variables = pts_tests::extra_environmental_variables($test_run_request->test_profile);

		// Start
		$cache_share_pt2so = $test_directory . 'cache-share-' . PTS_INIT_TIME . '.pt2so';
		$cache_share_present = $allow_cache_share && is_file($cache_share_pt2so);
		pts_module_manager::module_process('__pre_test_run', $test_run_request);

		$time_test_start = time();
		pts_client::$display->test_run_start($test_run_manager, $test_run_request);
		sleep(1);

		if(!$cache_share_present && !$test_run_manager->DEBUG_no_test_execution_just_result_parse)
		{
			$pre_output = pts_tests::call_test_script($test_run_request->test_profile, 'pre', 'Running Pre-Test Script', $pts_test_arguments, $extra_runtime_variables, true);

			if($pre_output != null && (pts_client::is_debug_mode() || $full_output))
			{
				pts_client::$display->test_run_instance_output($pre_output);
			}
			if(is_file($test_directory . 'pre-test-exit-status'))
			{
			  // If the pre script writes its exit status to ~/pre-test-exit-status, if it's non-zero the test run failed
			  $exit_status = pts_file_io::file_get_contents($test_directory . 'pre-test-exit-status');
			  unlink($test_directory . 'pre-test-exit-status');

			  if($exit_status != 0)
			  {
					self::test_run_instance_error($test_run_manager, $test_run_request, 'The pre run script exited with a non-zero exit status.' . PHP_EOL);
					self::test_run_error($test_run_manager, $test_run_request, 'This test execution has been abandoned.');
					return false;
			  }
			}
		}

		pts_client::$display->display_interrupt_message($test_run_request->test_profile->get_pre_run_message());
		$runtime_identifier = time();
		$execute_binary_prepend = '';
		if($test_run_request->exec_binary_prepend != null)
		{
			$execute_binary_prepend = $test_run_request->exec_binary_prepend;
		}

		if(!$cache_share_present && !$test_run_manager->DEBUG_no_test_execution_just_result_parse && $test_run_request->test_profile->is_root_required())
		{
			if(phodevi::is_root() == false)
			{
				pts_client::$display->test_run_error('This test must be run as the root / administrator account.');
			}

			$execute_binary_prepend .= ' ' . PTS_CORE_STATIC_PATH . 'root-access.sh ';
		}

		if($allow_cache_share && !is_file($cache_share_pt2so))
		{
			$cache_share = new pts_storage_object(false, false);
		}

		if($test_run_manager->get_results_identifier() != null && $test_run_manager->get_file_name() != null && pts_config::read_bool_config('PhoronixTestSuite/Options/Testing/SaveTestLogs', 'FALSE'))
		{
			$backup_test_log_dir = PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/active/' . $test_run_manager->get_results_identifier() . '/';
			pts_file_io::delete($backup_test_log_dir);
			pts_file_io::mkdir($backup_test_log_dir, 0777, true);
		}
		else
		{
			$backup_test_log_dir = false;
		}

		//
		// THE MAIN TESTING LOOP
		//

		for($i = 0, $times_result_produced = 0, $abort_testing = false, $time_test_start_actual = time(), $defined_times_to_run = $times_to_run; $i < $times_to_run && $i < 256 && !$abort_testing; $i++)
		{
			if($test_run_manager->DEBUG_no_test_execution_just_result_parse)
			{
				$find_log_file = pts_file_io::glob($test_directory . basename($test_identifier) . '-*.log');
				if(!empty($find_log_file))
				{
					if(!isset($find_log_file[0]) || empty($find_log_file[0]))
					{
						pts_client::test_profile_debug_message('No existing log file found for this test profile. Generate one by using the run/benchmark or debug-run commands.');
						return false;
					}

					$test_log_file = $find_log_file[0];
					pts_client::test_profile_debug_message('Log File: ' . $test_log_file);
				}
			}
			else
			{
				$test_log_file = $test_directory . basename($test_identifier) . '-' . $runtime_identifier . '-' . ($i + 1) . '.log';
			}

			$is_expected_last_run = ($i == ($times_to_run - 1));
			$produced_monitoring_result = false;
			$has_result = false;

			$test_extra_runtime_variables = array_merge($extra_runtime_variables, array(
			'LOG_FILE' => $test_log_file,
			'DISPLAY' => getenv('DISPLAY'),
			'PATH' => getenv('PATH'),
			));

			$restored_from_cache = false;
			if($cache_share_present)
			{
				$cache_share = pts_storage_object::recover_from_file($cache_share_pt2so);

				if($cache_share)
				{
					$test_result_std_output = $cache_share->read_object('test_results_output_' . $i);
					$test_extra_runtime_variables['LOG_FILE'] = $cache_share->read_object('log_file_location_' . $i);

					if($test_extra_runtime_variables['LOG_FILE'] != null)
					{
						file_put_contents($test_extra_runtime_variables['LOG_FILE'], $cache_share->read_object('log_file_' . $i));
						$test_run_time = 0; // This wouldn't be used for a cache share since it would always be the same, but declare the value so the variable is at least initialized
						$restored_from_cache = true;
					}
				}

				unset($cache_share);
			}

			if(!$test_run_manager->DEBUG_no_test_execution_just_result_parse && $restored_from_cache == false)
			{
				pts_client::$display->test_run_instance_header($test_run_request);
				sleep(1);
				$test_run_command = 'cd ' . $to_execute . ' && ' . $execute_binary_prepend . './' . $execute_binary . ' ' . $pts_test_arguments . ' 2>&1';

				pts_client::test_profile_debug_message('Test Run Command: ' . $test_run_command);

				$is_monitoring = pts_test_result_parser::system_monitor_task_check($test_run_request);
				$test_run_time_start = time();

				if(phodevi::is_windows() || pts_client::read_env('USE_PHOROSCRIPT_INTERPRETER') != false)
				{
					$phoroscript = new pts_phoroscript_interpreter($to_execute . '/' . $execute_binary, $test_extra_runtime_variables, $to_execute);
					$phoroscript->execute_script($pts_test_arguments);
					$test_result_std_output = null;
				}
				else
				{
					//$test_result_std_output = pts_client::shell_exec($test_run_command, $test_extra_runtime_variables);
					$descriptorspec = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
					$test_process = proc_open('exec ' . $execute_binary_prepend . './' . $execute_binary . ' ' . $pts_test_arguments . ' 2>&1', $descriptorspec, $pipes, $to_execute, array_merge($_ENV, pts_client::environmental_variables(), $test_extra_runtime_variables));

					if(is_resource($test_process))
					{
						//echo proc_get_status($test_process)['pid'];
						pts_module_manager::module_process('__test_running', $test_process);
						$test_result_std_output = stream_get_contents($pipes[1]);
						fclose($pipes[1]);
						fclose($pipes[2]);
						$return_value = proc_close($test_process);
					}
				}

				$test_run_time = time() - $test_run_time_start;
				$produced_monitoring_result = $is_monitoring ? pts_test_result_parser::system_monitor_task_post_test($test_run_request) : false;
			}
			else
			{
				if($i == 1) // to only display once
				{
					pts_client::$display->test_run_message('Utilizing Data From Shared Cache');
				}
				$test_run_time = 0;
			}


			if(!isset($test_result_std_output[10240]) || pts_client::is_debug_mode() || $full_output)
			{
				pts_client::$display->test_run_instance_output($test_result_std_output);
			}

			if(is_file($test_log_file) && trim($test_result_std_output) == null && (filesize($test_log_file) < 10240 || pts_client::is_debug_mode() || $full_output))
			{
				$test_log_file_contents = file_get_contents($test_log_file);
				pts_client::$display->test_run_instance_output($test_log_file_contents);
				unset($test_log_file_contents);
			}
			$test_run_request->test_result_standard_output = $test_result_std_output;

			$exit_status_pass = true;
			if(is_file($test_directory . 'test-exit-status'))
			{
				// If the test script writes its exit status to ~/test-exit-status, if it's non-zero the test run failed
				$exit_status = pts_file_io::file_get_contents($test_directory . 'test-exit-status');
				unlink($test_directory . 'test-exit-status');

				if($exit_status != 0)
				{
					self::test_run_instance_error($test_run_manager, $test_run_request, 'The test exited with a non-zero exit status.');
					if($is_expected_last_run && is_file($test_log_file))
					{
						$scan_log = pts_file_io::file_get_contents($test_log_file);
						$test_run_error = pts_tests::scan_for_error($scan_log, $test_run_request->test_profile->get_test_executable_dir());

						if($test_run_error)
						{
							self::test_run_instance_error($test_run_manager, $test_run_request, 'E: ' . $test_run_error);
						}
					}
					$exit_status_pass = false;
				}
			}

			if(!in_array(($i + 1), $ignore_runs) && $exit_status_pass)
			{
				// if it was monitoring, active result should already be set
				if(!$produced_monitoring_result) // XXX once single-run-multiple-outputs is supported, this check can be disabled to allow combination of results
				{
					$has_result = pts_test_result_parser::parse_result($test_run_request, $test_extra_runtime_variables['LOG_FILE']);
				}

				$has_result = $has_result || $produced_monitoring_result;

				if($has_result)
				{
					$times_result_produced++;
					if($test_run_time < 2 && $test_run_request->test_profile->get_estimated_run_time() > 60 && !$restored_from_cache && !$test_run_manager->DEBUG_no_test_execution_just_result_parse)
					{
						// If the test ended in less than two seconds, outputted some int, and normally the test takes much longer, then it's likely some invalid run
						self::test_run_instance_error($test_run_manager, $test_run_request, 'The test run ended prematurely.');
						if($is_expected_last_run && is_file($test_log_file))
						{
							$scan_log = pts_file_io::file_get_contents($test_log_file);
							$test_run_error = pts_tests::scan_for_error($scan_log, $test_run_request->test_profile->get_test_executable_dir());

							if($test_run_error)
							{
								self::test_run_instance_error($test_run_manager, $test_run_request, 'E: ' . $test_run_error);
							}
						}
					}
				}
				else if($test_run_request->test_profile->get_display_format() != 'NO_RESULT')
				{
					self::test_run_instance_error($test_run_manager, $test_run_request, 'The test run did not produce a result.');
					if($is_expected_last_run && is_file($test_log_file))
					{
						$scan_log = pts_file_io::file_get_contents($test_log_file);
						$test_run_error = pts_tests::scan_for_error($scan_log, $test_run_request->test_profile->get_test_executable_dir());

						if($test_run_error)
						{
							self::test_run_instance_error($test_run_manager, $test_run_request, 'E: ' . $test_run_error);
						}
					}
				}

				if($allow_cache_share && !is_file($cache_share_pt2so))
				{
					$cache_share->add_object('test_results_output_' . $i, $test_result_std_output);
					$cache_share->add_object('log_file_location_' . $i, $test_extra_runtime_variables['LOG_FILE']);
					$cache_share->add_object('log_file_' . $i, (is_file($test_log_file) ? file_get_contents($test_log_file) : null));
				}
			}

			if($is_expected_last_run && $times_result_produced > floor(($i - 2) / 2) && !$cache_share_present && !$test_run_manager->DEBUG_no_test_execution_just_result_parse && $test_run_manager->do_dynamic_run_count())
			{
				// The later check above ensures if the test is failing often the run count won't uselessly be increasing
				// Should we increase the run count?
				$increase_run_count = false;

				if($defined_times_to_run == ($i + 1) && $times_result_produced > 0 && $times_result_produced < $defined_times_to_run && $i < 64)
				{
					// At least one run passed, but at least one run failed to produce a result. Increase count to try to get more successful runs
					$increase_run_count = $defined_times_to_run - $times_result_produced;
				}
				else if($times_result_produced >= 2)
				{
					// Dynamically increase run count if needed for statistical significance or other reasons
					$first_tr = array_slice($test_run_request->generated_result_buffers, 0, 1);
					$first_tr = array_shift($first_tr);
					$increase_run_count = $test_run_manager->increase_run_count_check($first_tr->active, $defined_times_to_run, $test_run_time); // XXX maybe check all generated buffers to see if to extend?

					if($increase_run_count === -1)
					{
						$abort_testing = true;
					}
					else if($increase_run_count == true)
					{
						// Just increase the run count one at a time
						$increase_run_count = 1;
					}
				}

				if($increase_run_count > 0)
				{
					$times_to_run += $increase_run_count;
					$is_expected_last_run = false;
					//$test_run_request->test_profile->set_times_to_run($times_to_run);
				}
			}

			if($times_to_run > 1 && $i < ($times_to_run - 1))
			{
				if($cache_share_present == false && !$test_run_manager->DEBUG_no_test_execution_just_result_parse)
				{
					$interim_output = pts_tests::call_test_script($test_run_request->test_profile, 'interim', 'Running Interim Test Script', $pts_test_arguments, $extra_runtime_variables, true);

					if($interim_output != null && (pts_client::is_debug_mode() || $full_output))
					{
						pts_client::$display->test_run_instance_output($interim_output);
					}
					//sleep(2); // Rest for a moment between tests
				}

				pts_module_manager::module_process('__interim_test_run', $test_run_request);
			}

			if(is_file($test_log_file))
			{
				if($is_expected_last_run)
				{
					// For now just passing the last test log file...
					// TODO XXX: clean this up with log files to preserve when needed, let multiple log files exist for extra_data, etc
					pts_test_result_parser::generate_extra_data($test_run_request, $test_log_file);
				}
				pts_module_manager::module_process('__test_log_output', $test_log_file);
				if($backup_test_log_dir)
				{
					copy($test_log_file, $backup_test_log_dir . basename($test_log_file));
				}

				if(pts_client::test_profile_debug_message('Log File At: ' . $test_log_file) == false)
				{
					unlink($test_log_file);
				}
			}

			if(is_file(PTS_USER_PATH . 'halt-testing') || is_file(PTS_USER_PATH . 'skip-test'))
			{
				pts_client::release_lock($lock_file);
				return false;
			}

			pts_client::$display->test_run_instance_complete($test_run_request);
		}

		$time_test_end_actual = time();

		if($cache_share_present == false && !$test_run_manager->DEBUG_no_test_execution_just_result_parse)
		{
			$post_output = pts_tests::call_test_script($test_run_request->test_profile, 'post', 'Running Post-Test Script', $pts_test_arguments, $extra_runtime_variables, true);

			if($post_output != null && (pts_client::is_debug_mode() || $full_output))
			{
				pts_client::$display->test_run_instance_output($post_output);
			}
			if(is_file($test_directory . 'post-test-exit-status'))
			{
				// If the post script writes its exit status to ~/post-test-exit-status, if it's non-zero the test run failed
				$exit_status = pts_file_io::file_get_contents($test_directory . 'post-test-exit-status');
				unlink($test_directory . 'post-test-exit-status');

				if($exit_status != 0)
				{
					self::test_run_instance_error($test_run_manager, $test_run_request, 'The post run script exited with a non-zero exit status.' . PHP_EOL);
					$abort_testing = true;
				}
			}
		}

		if($abort_testing && !is_dir('/mnt/c/Windows')) // bash on Windows has issues where this is always called, looks like bad exit status on Windows
		{
			self::test_run_error($test_run_manager, $test_run_request, 'This test execution has been abandoned.');
			return false;
		}

		// End
		$time_test_end = time();
		$time_test_elapsed = $time_test_end - $time_test_start;
		$time_test_elapsed_actual = $time_test_end_actual - $time_test_start_actual;

		if(!empty($min_length))
		{
			if($min_length > $time_test_elapsed_actual)
			{
				// The test ended too quickly, results are not valid
				self::test_run_error($test_run_manager, $test_run_request, 'This test ended prematurely.');
				return false;
			}
		}

		if(!empty($max_length))
		{
			if($max_length < $time_test_elapsed_actual)
			{
				// The test took too much time, results are not valid
				self::test_run_error($test_run_manager, $test_run_request, 'This test run was exhausted.');
				return false;
			}
		}

		if($allow_cache_share && !is_file($cache_share_pt2so) && $cache_share instanceof pts_storage_object)
		{
			$cache_share->save_to_file($cache_share_pt2so);
			unset($cache_share);
		}

		if($test_run_manager->get_results_identifier() != null && (pts_config::read_bool_config('PhoronixTestSuite/Options/Testing/SaveInstallationLogs', 'FALSE')))
		{
			if(is_file($test_run_request->test_profile->get_install_dir() . 'install.log'))
			{
				$backup_log_dir = PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/installation-logs/' . $test_run_manager->get_results_identifier() . '/';
				pts_file_io::mkdir($backup_log_dir, 0777, true);
				copy($test_run_request->test_profile->get_install_dir() . 'install.log', $backup_log_dir . basename($test_identifier) . '.log');
			}
		}

		// Fill in any missing test details
		foreach($test_run_request->generated_result_buffers as &$sub_tr)
		{
			$arguments_description = $sub_tr->get_arguments_description();

			if(empty($arguments_description))
			{
				$arguments_description = $sub_tr->test_profile->get_test_subtitle();
			}

			$file_var_checks = array(
			array('pts-results-scale', 'set_result_scale', null),
			array('pts-results-proportion', 'set_result_proportion', null),
			array('pts-results-quantifier', 'set_result_quantifier', null),
			array('pts-test-version', 'set_version', null),
			array('pts-test-description', null, 'set_used_arguments_description'),
			array('pts-footnote', null, null),
			);

			foreach($file_var_checks as &$file_check)
			{
				list($file, $set_function, $result_set_function) = $file_check;

				if(is_file($test_directory . $file))
				{
					$file_contents = pts_file_io::file_get_contents($test_directory . $file);
					unlink($test_directory . $file);

					if(!empty($file_contents))
					{
						if($set_function != null)
						{
							call_user_func(array($sub_tr->test_profile, $set_function), $file_contents);
						}
						else if($result_set_function != null)
						{
							if($result_set_function == 'set_used_arguments_description')
							{
								$arguments_description = $file_contents;
							}
							else
							{
								call_user_func(array($sub_tr, $result_set_function), $file_contents);
							}
						}
						else if($file == 'pts-footnote')
						{
							$sub_tr->test_profile->test_installation->set_install_footnote($file_contents);
						}
					}
				}
			}

			if(empty($arguments_description))
			{
				$arguments_description = 'Phoronix Test Suite v' . PTS_VERSION;
			}

			foreach(pts_client::environmental_variables() as $key => $value)
			{
				$arguments_description = str_replace('$' . $key, $value, $arguments_description);

				if(!in_array($key, array('VIDEO_MEMORY', 'NUM_CPU_CORES', 'NUM_CPU_JOBS')))
				{
					$extra_arguments = str_replace('$' . $key, $value, $extra_arguments);
				}
			}
			$sub_tr->set_used_arguments_description($arguments_description);
			$sub_tr->set_used_arguments($extra_arguments);
		}

		// Any device notes to add to PTS test notes area?
		foreach(phodevi::read_device_notes($test_type) as $note)
		{
			pts_test_notes_manager::add_note($note);
		}

		// Result Calculation

		// Ending Tasks
		pts_client::$display->display_interrupt_message($test_run_request->test_profile->get_post_run_message());
		$test_successful = self::calculate_end_result_post_processing($test_run_manager, $test_run_request); // Process results

		// End Finalize
		pts_module_manager::module_process('__post_test_run', $test_run_request);
		$report_elapsed_time = $cache_share_present == false && $times_result_produced > 0;
		pts_tests::update_test_install_xml($test_run_request->test_profile, ($report_elapsed_time ? $time_test_elapsed : 0));
		pts_storage_object::add_in_file(PTS_CORE_STORAGE, 'total_testing_time', ($time_test_elapsed / 60));

		if($report_elapsed_time && pts_client::do_anonymous_usage_reporting() && $time_test_elapsed >= 60)
		{
			// If anonymous usage reporting enabled, report test run-time to OpenBenchmarking.org
			pts_openbenchmarking_client::upload_usage_data('test_complete', array($test_run_request, $time_test_elapsed));
		}

		// Remove lock
		pts_client::release_lock($lock_file);
		return $test_successful;
	}
	protected static function calculate_end_result_post_processing(&$test_run_manager, &$root_tr)
	{
		$test_successful = false;

		foreach($root_tr->generated_result_buffers as &$test_result)
		{
			$trial_results = $test_result->active->results;
			$END_RESULT = 0;

			switch($test_result->test_profile->get_display_format())
			{
				case 'NO_RESULT':
					// Nothing to do, there are no results
					break;
				case 'LINE_GRAPH':
				case 'FILLED_LINE_GRAPH':
				case 'TEST_COUNT_PASS':
					// Just take the first result
					$END_RESULT = $trial_results[0];
					break;
				case 'IMAGE_COMPARISON':
					// Capture the image
					$iqc_image_png = $trial_results[0];

					if(is_file($iqc_image_png))
					{
						$img_file_64 = base64_encode(file_get_contents($iqc_image_png, FILE_BINARY));
						$END_RESULT = $img_file_64;
						unlink($iqc_image_png);
					}
					break;
				case 'PASS_FAIL':
				case 'MULTI_PASS_FAIL':
					// Calculate pass/fail type
					$END_RESULT = -1;

					if(count($trial_results) == 1)
					{
						$END_RESULT = $trial_results[0];
					}
					else
					{
						foreach($trial_results as $result)
						{
							if($result == 'FALSE' || $result == '0' || $result == 'FAIL' || $result == 'FAILED')
							{
								if($END_RESULT == -1 || $END_RESULT == 'PASS')
								{
									$END_RESULT = 'FAIL';
								}
							}
							else
							{
								if($END_RESULT == -1)
								{
									$END_RESULT = 'PASS';
								}
							}
						}
					}
					break;
				case 'BAR_GRAPH':
				default:
					// Result is of a normal numerical type
					switch($test_result->test_profile->get_result_quantifier())
					{
						case 'MAX':
							$END_RESULT = max($trial_results);
							break;
						case 'MIN':
							$END_RESULT = min($trial_results);
							break;
						case 'AVG':
						default:
							// assume AVG (average)
							$is_float = false;
							$TOTAL_RESULT = 0;
							$TOTAL_COUNT = 0;

							foreach($trial_results as $result)
							{
								$result = trim($result);

								if(is_numeric($result))
								{
									$TOTAL_RESULT += $result;
									$TOTAL_COUNT++;

									if(!$is_float && strpos($result, '.') !== false)
									{
										$is_float = true;
									}
								}
							}

							$END_RESULT = pts_math::set_precision($TOTAL_RESULT / ($TOTAL_COUNT > 0 ? $TOTAL_COUNT : 1), $test_result->get_result_precision());

							if(!$is_float)
							{
								$END_RESULT = round($END_RESULT);
							}

							if(count($min = $test_result->active->min_results) > 0)
							{
								$min = round(min($min), 2);

								if($min < $END_RESULT && is_numeric($min) && $min != 0)
								{
									$test_result->active->set_min_result($min);
								}
							}
							if(count($max = $test_result->active->max_results) > 0)
							{
								$max = round(max($max), 2);

								if($max > $END_RESULT && is_numeric($max) && $max != 0)
								{
									$test_result->active->set_max_result($max);
								}
							}
							break;
					}
					break;
			}

			$test_result->active->set_result($END_RESULT);

			pts_client::$display->test_run_end($test_result);

			// Finalize / result post-processing to generate save
			if($test_result->test_profile->get_display_format() == 'NO_RESULT')
			{
				$test_successful = true;
			}
			else if($test_result instanceof pts_test_result && $test_result->active)
			{
				$end_result = $test_result->active->get_result();

				// removed count($result) > 0 in the move to pts_test_result
				if(count($test_result) > 0 && ((is_numeric($end_result) && $end_result > 0) || (!is_numeric($end_result) && isset($end_result[3]))))
				{
					pts_module_manager::module_process('__post_test_run_success', $test_result);
					$test_successful = true;

					if($test_run_manager->get_results_identifier() != null)
					{
						$test_result->test_result_buffer = new pts_test_result_buffer();
						$test_result->test_result_buffer->add_test_result($test_run_manager->get_results_identifier(), $test_result->active->get_result(), $test_result->active->get_values_as_string(), pts_test_run_manager::process_json_report_attributes($test_result), $test_result->active->get_min_result(), $test_result->active->get_max_result());
						$test_run_manager->result_file->add_result($test_result);
					}
				}
			}
		}

		if($test_run_manager->get_results_identifier() != null && $test_run_manager->get_file_name() != null && pts_config::read_bool_config('PhoronixTestSuite/Options/Testing/SaveTestLogs', 'FALSE'))
		{
			static $xml_write_pos = 1;
			pts_file_io::mkdir(PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/' . $xml_write_pos . '/');

			if(is_dir(PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/active/' . $test_run_manager->get_results_identifier()))
			{
				$test_log_write_dir = PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/' . $xml_write_pos . '/' . $test_run_manager->get_results_identifier() . '/';
				if(is_dir($test_log_write_dir))
				{
					pts_file_io::delete($test_log_write_dir, null, true);
				}
				rename(PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/active/' . $test_run_manager->get_results_identifier() . '/', $test_log_write_dir);
			}
			$xml_write_pos++;
		}
		pts_file_io::unlink(PTS_SAVE_RESULTS_PATH . $test_run_manager->get_file_name() . '/test-logs/active/');

		return $test_successful;
	}
}

?>
