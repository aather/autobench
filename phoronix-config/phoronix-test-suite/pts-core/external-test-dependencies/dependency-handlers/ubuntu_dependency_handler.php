<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2015, Phoronix Media
	Copyright (C) 2015, Michael Larabel

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

class ubuntu_dependency_handler implements pts_dependency_handler
{
	public static function what_provides($files_needed)
	{
		$packages_needed = array();
		foreach(pts_arrays::to_array($files_needed) as $file)
		{
			if(pts_client::executable_in_path('apt-file'))
			{
				if(!defined('APT_FILE_UPDATED'))
				{
					shell_exec('apt-file update 2>&1');
					define('APT_FILE_UPDATED', 1);
				}

				// Try appending common paths
				if(strpos($file, '.h') !== false)
				{
					$apt_provides = self::run_apt_file_provides('/usr/include/' . $file);
					if($apt_provides != null)
					{
						$packages_needed[$file] = $apt_provides;
					}
				}
				else if(strpos($file, '.so') !== false)
				{
					$apt_provides = self::run_apt_file_provides('/usr/lib/' . $file);
					if($apt_provides != null)
					{
						$packages_needed[$file] = $apt_provides;
					}
				}
				else
				{
					foreach(array('/usr/bin/', '/bin/', '/usr/sbin') as $possible_path)
						{
						$apt_provides = self::run_apt_file_provides($possible_path . $file);
						if($apt_provides != null)
						{
							$packages_needed[$file] = $apt_provides;
							break;
						}
					}
				}
			}
		}
		return $packages_needed;
	}
	protected static function run_apt_file_provides($arg)
	{
		$apt_output = shell_exec('apt-file -N search --regex "' . $arg . '$" 2>/dev/null');

		foreach(explode(PHP_EOL, $apt_output) as $line)
		{
			if(($x = strpos($line, ': ')) == false)
			{
				continue;
			}
			return trim(substr($line, 0, $x));
		}

		return null;
	}
}


?>

