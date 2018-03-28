<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2010, Phoronix Media
	Copyright (C) 2009 - 2010, Michael Larabel
	phodevi_osx_parser.php: General parsing functions specific to Mac OS X

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

class phodevi_osx_parser
{
	public static function read_osx_system_profiler($data_type, $object, $multiple_objects = false, $ignore_values = array())
	{
		$value = ($multiple_objects ? array() : false);

		if(pts_client::executable_in_path('system_profiler'))
		{
			$info = trim(shell_exec('system_profiler ' . $data_type . ' 2>&1'));
			$lines = explode("\n", $info);

			for($i = 0; $i < count($lines) && ($value == false || $multiple_objects); $i++)
			{
				$line = pts_strings::colon_explode($lines[$i]);

				if(isset($line[0]) == false)
				{
					continue;
				}

				$line_object = str_replace(' ', null, $line[0]);
		
				if(($cut_point = strpos($line_object, '(')) > 0)
				{
					$line_object = substr($line_object, 0, $cut_point);
				}
		
				if(strtolower($line_object) == strtolower($object) && isset($line[1]))
				{
					$this_value = trim($line[1]);
			
					if(!empty($this_value) && !in_array($this_value, $ignore_values))
					{
						if($multiple_objects)
						{
							array_push($value, $this_value);
						}
						else
						{
							$value = $this_value;
						}
					}
				}
			}
		}
	
		return $value;
	}
}

?>
