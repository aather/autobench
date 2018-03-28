<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2010 - 2016, Phoronix Media
	Copyright (C) 2010 - 2016, Michael Larabel

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

class pts_test_run_options
{
	public static function prompt_user_options(&$test_profile, $preset_selections = null)
	{
		$user_args = array();
		$text_args = array();

		if(($cli_presets_env = pts_client::read_env('PRESET_OPTIONS')) != false)
		{
			// To specify test options externally from an environment variable
			// i.e. PRESET_OPTIONS='stream.run-type=Add' ./phoronix-test-suite benchmark stream
			// The string format is <test-name>.<test-option-name-from-XML-file>=<test-option-value>
			// The test-name can either be the short/base name (e.g. stream) or the full identifier (pts/stream) without version postfix
			// Multiple preset options can be delimited with the PRESET_OPTIONS environment variable via a semicolon ;
			$preset_selections = pts_client::parse_value_string_double_identifier($cli_presets_env);
		}

		$identifier_short = $test_profile->get_identifier_base_name();
		$identifier_full = $test_profile->get_identifier(false);

		if(count($test_profile->get_test_option_objects()) > 0)
		{
			pts_client::$display->test_run_configure($test_profile);
		}

		foreach($test_profile->get_test_option_objects() as $i => $o)
		{
			$option_identifier = $o->get_identifier();

			if($o->option_count() == 0)
			{
				// User inputs their option as there is nothing to select
				if(isset($preset_selections[$identifier_short][$option_identifier]))
				{
					$value = $preset_selections[$identifier_short][$option_identifier];
					echo PHP_EOL . '    Using Pre-Set Run Option: ' . $value . PHP_EOL;
				}
				else if(isset($preset_selections[$identifier_full][$option_identifier]))
				{
					$value = $preset_selections[$identifier_full][$option_identifier];
					echo PHP_EOL . '    Using Pre-Set Run Option: ' . $value . PHP_EOL;
				}
				else
				{
					echo PHP_EOL . $o->get_name() . PHP_EOL;
					$value = pts_user_io::prompt_user_input('Enter Value');
				}

				$text_args[] = array($o->format_option_display_from_input($value));
				$user_args[] = array($o->format_option_value_from_input($value));
			}
			else
			{
				// Have the user select the desired option
				if(isset($preset_selections[$identifier_short][$option_identifier]))
				{
					$bench_choice = $preset_selections[$identifier_short][$option_identifier];
					echo PHP_EOL . '    Using Pre-Set Run Option: ' . $bench_choice . PHP_EOL;
				}
				else if(isset($preset_selections[$identifier_full][$option_identifier]))
				{
					$bench_choice = $preset_selections[$identifier_full][$option_identifier];
					echo PHP_EOL . '    Using Pre-Set Run Option: ' . $bench_choice . PHP_EOL;
				}
				else
				{
					$option_names = $o->get_all_option_names_with_messages();

					if(count($option_names) > 1)
					{
						//echo PHP_EOL . $o->get_name() . ':' . PHP_EOL;
						$option_names[] = 'Test All Options';
					}

					$bench_choice = pts_user_io::prompt_text_menu($o->get_name(), $option_names, true, true, pts_client::$display->get_tab() . pts_client::$display->get_tab());
					echo PHP_EOL;
				}

				$bench_choice = $o->parse_selection_choice_input($bench_choice);

				// Format the selected option(s)
				$option_args = array();
				$option_args_description = array();

				foreach($bench_choice as $c)
				{
					$option_args[] = $o->format_option_value_from_select($c);
					$option_args_description[] = $o->format_option_display_from_select($c);
				}

				$text_args[] = $option_args_description;
				$user_args[] = $option_args;
			}
		}

		$test_args = array();
		$test_args_description = array();

		self::compute_all_combinations($test_args, null, $user_args, 0);
		self::compute_all_combinations($test_args_description, null, $text_args, 0, ' - ');

		return array($test_args, $test_args_description);
	}
	public static function default_user_options(&$test_profile)
	{
		// Defaults mode for single test
		$all_args_real = array();
		$all_args_description = array();

		foreach($test_profile->get_test_option_objects() as $o)
		{
			$option_args = array();
			$option_args_description = array();

			$default_entry = $o->get_option_default();

			if($o->option_count() == 2)
			{
				foreach(array(0, 1) as $i)
				{
					$option_args[] = $o->format_option_value_from_select($i);
					$option_args_description[] = $o->format_option_display_from_select($i);
				}
			}
			else
			{
				$option_args[] = $o->format_option_value_from_select($default_entry);
				$option_args_description[] = $o->format_option_display_from_select($default_entry);
			}

			$all_args_real[] = $option_args;
			$all_args_description[] = $option_args_description;
		}

		$test_args = array();
		$test_args_description = array();

		self::compute_all_combinations($test_args, null, $all_args_real, 0);
		self::compute_all_combinations($test_args_description, null, $all_args_description, 0, ' - ');

		return array($test_args, $test_args_description);
	}
	public static function batch_user_options(&$test_profile)
	{
		// Batch mode for single test
		$batch_all_args_real = array();
		$batch_all_args_description = array();

		foreach($test_profile->get_test_option_objects() as $o)
		{
			$option_args = array();
			$option_args_description = array();
			$option_count = $o->option_count();

			for($i = 0; $i < $option_count; $i++)
			{
				$option_args[] = $o->format_option_value_from_select($i);
				$option_args_description[] = $o->format_option_display_from_select($i);
			}

			$batch_all_args_real[] = $option_args;
			$batch_all_args_description[] = $option_args_description;
		}

		$test_args = array();
		$test_args_description = array();

		self::compute_all_combinations($test_args, null, $batch_all_args_real, 0);
		self::compute_all_combinations($test_args_description, null, $batch_all_args_description, 0, ' - ');

		return array($test_args, $test_args_description);
	}
	public static function compute_all_combinations(&$return_arr, $current_string, $options, $counter, $delimiter = ' ')
	{
		// In batch mode, find all possible combinations for test options
		if(count($options) <= $counter)
		{
			$return_arr[] = trim($current_string);
		}
		else
		{
			foreach($options[$counter] as $single_option)
			{
				$new_current_string = $current_string;

				if(!empty($new_current_string))
				{
					$new_current_string .= $delimiter;
				}

				$new_current_string .= $single_option;

				self::compute_all_combinations($return_arr, $new_current_string, $options, $counter + 1, $delimiter);
			}
		}
	}
	public static function auto_process_test_option($test_identifier, $option_identifier, &$option_names, &$option_values, &$option_messages)
	{
		// Some test items have options that are dynamically built
		switch($option_identifier)
		{
			case 'auto-resolution':
				// Base options off available screen resolutions
				if(count($option_names) == 1 && count($option_values) == 1)
				{
					if(PTS_IS_CLIENT && phodevi::read_property('gpu', 'screen-resolution') && phodevi::read_property('gpu', 'screen-resolution') != array(-1, -1) && !defined('PHOROMATIC_SERVER'))
					{
						$available_video_modes = phodevi::read_property('gpu', 'available-modes');
					}
					else
					{
						$available_video_modes = array();
					}

					if(empty($available_video_modes))
					{
						// Use hard-coded defaults
						$available_video_modes = array(array(800, 600), array(1024, 768), array(1280, 768), array(1280, 960), array(1280, 1024), array(1366, 768),
							array(1400, 1050), array(1600, 900), array(1680, 1050), array(1600, 1200), array(1920, 1080), array(2560, 1600), array(3840, 2160));
					}

					$format_name = $option_names[0];
					$format_value = $option_values[0];
					$option_names = array();
					$option_values = array();

					foreach($available_video_modes as $video_mode)
					{
						$this_name = str_replace('$VIDEO_WIDTH', $video_mode[0], $format_name);
						$this_name = str_replace('$VIDEO_HEIGHT', $video_mode[1], $this_name);

						$this_value = str_replace('$VIDEO_WIDTH', $video_mode[0], $format_value);
						$this_value = str_replace('$VIDEO_HEIGHT', $video_mode[1], $this_value);

						$option_names[] = $this_name;
						$option_values[] = $this_value;
					}
				}
				break;
			case 'auto-disk-partitions':
			case 'auto-disk-mount-points':
				// Base options off available disk partitions
				if(PTS_IS_CLIENT == false)
				{
					echo 'ERROR: This option is not supported in this configuration.';
					return;
				}

				/*if(phodevi::is_linux())
				{
					$all_devices = array_merge(pts_file_io::glob('/dev/hd*'), pts_file_io::glob('/dev/sd*'));
				}
				else if(phodevi::is_bsd())
				{
					$all_devices = array_merge(pts_file_io::glob('/dev/ad*'), pts_file_io::glob('/dev/ada*'));
				}
				else
				{
					$all_devices = array();
				}*/
				$all_devices = array_merge(pts_file_io::glob('/dev/hd*'), pts_file_io::glob('/dev/sd*'), pts_file_io::glob('/dev/md*'), pts_file_io::glob('/dev/nvme*'));

				foreach($all_devices as &$device)
				{
					if(!is_numeric(substr($device, -1)))
					{
						unset($device);
					}
				}

				$all_devices = array_merge($all_devices, pts_file_io::glob('/dev/mapper/*'));

				$option_values = array();
				foreach($all_devices as $partition)
				{
					$option_values[] = $partition;
				}

				if($option_identifier == 'auto-disk-mount-points')
				{
					$partitions_d = $option_values;
					$option_values = array();
					$option_names = array();

					$mounts = is_file('/proc/mounts') ? file_get_contents('/proc/mounts') : null;

					$option_values[] = '';
					$option_names[] = 'Default Test Directory';

					foreach($partitions_d as $partition_d)
					{
						$mount_point = substr(($a = substr($mounts, strpos($mounts, $partition_d) + strlen($partition_d) + 1)), 0, strpos($a, ' '));
						if(is_dir($mount_point) && is_writable($mount_point) && !in_array($mount_point, array('/boot', '/boot/efi')))
						{
							$option_values[] = $mount_point;
							$option_names[] = $mount_point; // ' [' . $partition_d . ']'
						}
					}
				}
				else
				{
					$option_names = $option_values;
				}

				break;
			case 'auto-disks':
				// Base options off attached disks
				if(PTS_IS_CLIENT == false)
				{
					echo 'ERROR: This option is not supported in this configuration.';
					return;
				}

				$all_devices = array_merge(pts_file_io::glob('/dev/hd*'), pts_file_io::glob('/dev/sd*'), pts_file_io::glob('/dev/md*'), pts_file_io::glob('/dev/nvme*'));

				foreach($all_devices as $i => &$device)
				{
					if(is_numeric(substr($device, -1)))
					{
						unset($all_devices[$i]);
					}
				}

				$option_values = array();
				foreach($all_devices as $disk)
				{
					$option_values[] = $disk;
				}
				$option_names = $option_values;
				break;
			case 'auto-removable-media':
				if(PTS_IS_CLIENT == false)
				{
					echo 'ERROR: This option is not supported in this configuration.';
					return;
				}

				foreach(array_merge(pts_file_io::glob('/media/*/'), pts_file_io::glob('/Volumes/*/')) as $media_check)
				{
					if(is_dir($media_check) && is_writable($media_check)) // add more checks later on
					{
						$option_names[] = $media_check;
						$option_values[] = $media_check;
					}
				}
				break;
			case 'auto-file-select':
				if(PTS_IS_CLIENT == false)
				{
					echo 'ERROR: This option is not supported in this configuration.';
					return;
				}

				$names = $option_names;
				$values = $option_values;
				$option_names = array();
				$option_values = array();

				for($i = 0; $i < count($names) && $i < count($values); $i++)
				{
					if(is_file($values[$i]))
					{
						$option_names[] = $names[$i];
						$option_values[] = $values[$i];
					}
				}
				break;
			case 'auto-directory-select':
				if(PTS_IS_CLIENT == false)
				{
					echo 'ERROR: This option is not supported in this configuration.';
					return;
				}

				$names = $option_names;
				$values = $option_values;
				$option_names = array();
				$option_values = array();

				for($i = 0; $i < count($names) && $i < count($values); $i++)
				{
					if(is_dir($values[$i]) && is_writable($removable_media[$i]))
					{
						$option_names[] = $names[$i];
						$option_values[] = $values[$i];
					}
				}
				break;
		}
	}
}

?>
