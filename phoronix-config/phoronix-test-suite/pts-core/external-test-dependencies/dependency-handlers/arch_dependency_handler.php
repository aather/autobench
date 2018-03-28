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

class arch_dependency_handler implements pts_dependency_handler
{
	public static function what_provides($files_needed)
	{
		$packages_needed = array();
		foreach(pts_arrays::to_array($files_needed) as $file)
		{
			if(pts_client::executable_in_path('pkgfile'))
			{
				$pkgfile_provides = self::run_pkgfile_provides($file);
				if($pkgfile_provides != null)
				{
					$packages_needed[$file] = $pkgfile_provides;
				}
				else
				{
					// Try appending common paths
					if(strpos($file, '.h') !== false)
					{
						$pkgfile_provides = self::run_pkgfile_provides('/usr/include/' . $file);
						if($pkgfile_provides != null)
						{
							$packages_needed[$file] = $pkgfile_provides;
						}
					}
					else if(strpos($file, '.so') !== false)
					{
						$pkgfile_provides = self::run_pkgfile_provides('/usr/lib/' . $file);
						if($pkgfile_provides != null)
						{
							$packages_needed[$file] = $pkgfile_provides;
						}
					}
					else
					{
						foreach(array('/usr/bin/', '/bin/', '/usr/sbin') as $possible_path)
						{
							$pkgfile_provides = self::run_pkgfile_provides($possible_path . $file);
							if($pkgfile_provides != null)
							{
								$packages_needed[$file] = $pkgfile_provides;
								break;
							}
						}
					}
				}
			}
		}
		return $packages_needed;
	}
	protected static function run_pkgfile_provides($arg)
	{
		$pkgfile_output = shell_exec('pkgfile ' . $arg . ' 2>/dev/null');

		foreach(explode(PHP_EOL, $pkgfile_output) as $line)
		{
			$line = trim($line);
			if(!empty($line))
			{
				return $line;
			}
		}

		return null;
	}
}


?>

