<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2012 - 2013, Phoronix Media
	Copyright (C) 2012 - 2013, Michael Larabel

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

class list_unsupported_tests implements pts_option_interface
{
	const doc_section = 'Information';
	const doc_description = 'This option will list all available test profiles that are available from the enabled OpenBenchmarking.org repositories but are NOT SUPPORTED on the given hardware/software platform. This is mainly a debugging option for those looking for test profiles to potentially port to new platforms, etc.';

	public static function run($r)
	{
		pts_client::$display->generic_heading('Unsupported Tests');

		foreach(pts_openbenchmarking::available_tests() as $identifier)
		{
			$unsupported = false;
			$test_profile = new pts_test_profile($identifier);

			if($test_profile->is_test_architecture_supported() == false)
			{
				$unsupported = 'UNSUPPORTED ARCHITECTURE';
			}
			else if($test_profile->is_test_platform_supported() == false)
			{
				$unsupported = 'UNSUPPORTED PLATFORM';
			}

			if($unsupported)
			{
				echo sprintf('%-28ls - %-30ls %-9ls', $identifier, $unsupported, $repo_index['tests'][$id]['test_type']) . PHP_EOL;
			}
		}

	}
}

?>
