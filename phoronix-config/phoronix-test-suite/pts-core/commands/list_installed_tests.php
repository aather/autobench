<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2016, Phoronix Media
	Copyright (C) 2008 - 2016, Michael Larabel

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

class list_installed_tests implements pts_option_interface
{
	const doc_section = 'Information';
	const doc_description = 'This option will list all test profiles that are currently installed on the system.';

	public static function run($r)
	{
		$installed_tests = pts_tests::installed_tests();
		pts_client::$display->generic_heading(count($installed_tests) . ' Tests Installed');

		if(count($installed_tests) > 0)
		{
			foreach($installed_tests as $identifier)
			{
				$test_profile = new pts_test_profile($identifier);
				$name = $test_profile->get_title();

				if($name != false)
				{
					echo sprintf('%-26ls - %-30ls' . PHP_EOL, $identifier, $name);
				}
			}
		}
	}
}

?>
