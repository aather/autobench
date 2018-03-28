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

class pts_user_io
{
	public static function read_user_input()
	{
		return trim(fgets(STDIN));
	}
	public static function strip_ansi_escape_sequences($output)
	{
		if(function_exists('preg_replace'))
		{
			$output = preg_replace('/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/', null, $output);
			$output = preg_replace('/\x1b(\[|\(|\))[;?0-9]*[0-9A-Za-z]/', null, $output);
			$output = preg_replace('/[\x03|\x1a]/', null, $output);
		}

		return $output;
	}
	public static function prompt_user_input($question, $allow_null = false)
	{
		do
		{
			echo PHP_EOL . pts_client::cli_just_bold($question . ': ');
			$answer = pts_user_io::read_user_input();
		}
		while(!$allow_null && empty($answer));

		return $answer;
	}
	public static function display_text_list($list_items, $line_start = '- ')
	{
		$list = null;

		foreach($list_items as &$item)
		{
			$list .= $line_start . $item . PHP_EOL;
		}

		return $list;
	}
	public static function display_text_table(&$table, $prepend_to_lines = null, $extra_width_to_column = 0, $min_width = 0)
	{
		$column_widths = array();
		$formatted_table = $prepend_to_lines;

		for($r = 0; $r < count($table); $r++)
		{
			for($c = 0; $c < count($table[$r]); $c++)
			{
				if(!isset($column_widths[$c]) || isset($table[$r][$c][$column_widths[$c]]))
				{
					$column_widths[$c] = strlen($table[$r][$c]);
				}
			}
		}

		for($r = 0, $r_count = count($table); $r < $r_count; $r++)
		{
			for($c = 0, $rc_count = count($table[$r]); $c < $rc_count; $c++)
			{
				$formatted_table .= $table[$r][$c];

				if(($c + 1) != $rc_count)
				{
					$formatted_table .= str_repeat(' ', (max($min_width, 1 + $extra_width_to_column + $column_widths[$c]) - strlen($table[$r][$c])));
				}
			}

			if(($r + 1) != $r_count)
			{
				$formatted_table .= PHP_EOL . $prepend_to_lines;
			}
		}

		return $formatted_table;
	}
	public static function prompt_bool_input($question, $default = true, $question_id = 'UNKNOWN')
	{
		// Prompt user for yes/no question
		/*if BATCH MODE
		{
			switch($question_id)
			{
				default:
					$auto_answer = 'true';
					break;
			}

			$answer = pts_strings::string_bool($auto_answer);
		}*/
		$question .= ' (' . ($default == true ? 'Y/n' : 'y/N') . '): ';

		do
		{
			pts_client::$display->generic_prompt(pts_client::cli_just_bold($question));
			$input = strtolower(pts_user_io::read_user_input());
		}
		while($input != 'y' && $input != 'n' && $input != '');
		switch($input)
		{
			case 'y':
				$answer = true;
				break;
			case 'n':
				$answer = false;
				break;
			default:
				$answer = $default;
				break;
		}
		return $answer;
	}
	public static function prompt_text_menu($user_string, $options_r, $allow_multi_select = false, $return_index = false, $line_prefix = null)
	{
		$option_count = count($options_r);

		if($option_count == 1)
		{
			return $return_index ? pts_arrays::last_element(array_keys($options_r)) : array_pop($options_r);
		}

		$select = array();

		do
		{
			echo PHP_EOL;
			$key_index = array();
			foreach(array_keys($options_r) as $i => $key)
			{
				$key_index[($i + 1)] = $key;
				echo $line_prefix . pts_client::cli_just_bold(($i + 1) . ': ') . str_repeat(' ', strlen($option_count) - strlen(($i + 1))) . $options_r[$key] . PHP_EOL;
			}
			echo $line_prefix . pts_client::cli_just_bold($user_string . ': ');
			$select_choice = pts_user_io::read_user_input();

			foreach(($allow_multi_select ? pts_strings::comma_explode($select_choice) : array($select_choice)) as $choice)
			{
				if(in_array($choice, $options_r))
				{
					$select[] = array_search($choice, $options_r);
				}
				else if(isset($key_index[$choice]))
				{
					$select[] = $key_index[$choice];
				}
				else if($allow_multi_select && strpos($choice, '-') !== false)
				{
					$choice_range = pts_strings::trim_explode('-', $choice);

					if(count($choice_range) == 2 && is_numeric($choice_range[0]) && is_numeric($choice_range[1]) && isset($key_index[$choice_range[0]]) && isset($key_index[$choice_range[1]]))
					{
						for($i = min($choice_range); $i <= max($choice_range); $i++)
						{
							$select[] = $key_index[$i];
						}
					}
				}
			}
		}
		while(!isset($select[0]));

		if($return_index == false)
		{
			foreach($select as &$index)
			{
				$index = $options_r[$index];
			}
		}

		return implode(',', $select);
	}
}

?>
