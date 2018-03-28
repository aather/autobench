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

class list_available_tests implements pts_option_interface
{
	const doc_section = 'Information';
	const doc_description = 'This option will list all test profiles that are available from the enabled OpenBenchmarking.org repositories.';

	public static function command_aliases()
	{
		return array('list_tests', 'list_supported_tests');
	}
	public static function run($r)
	{
		pts_client::$display->generic_heading('Available Tests');
		$test_count = 0;
		foreach(pts_openbenchmarking::available_tests(false) as $identifier)
		{
			$repo = substr($identifier, 0, strpos($identifier, '/'));
			$id = substr($identifier, strlen($repo) + 1);
			$repo_index = pts_openbenchmarking::read_repository_index($repo);

			if((!empty($repo_index['tests'][$id]['supported_platforms']) && !in_array(phodevi::operating_system(), $repo_index['tests'][$id]['supported_platforms'])) || empty($repo_index['tests'][$id]['title']))
			{
				// Don't show unsupported tests
				continue;
			}

			echo sprintf('%-30ls - %-35ls %-9ls', $identifier, $repo_index['tests'][$id]['title'], $repo_index['tests'][$id]['test_type']) . PHP_EOL;
			$test_count++;
		}

		foreach(pts_tests::local_tests() as $identifier)
		{
			$test_profile = new pts_test_profile($identifier);

			if($test_profile->get_title() != null && $test_profile->is_supported(false))
			{
				echo sprintf('%-30ls - %-35ls %-9ls', $test_profile->get_identifier(), $test_profile->get_title(), $test_profile->get_test_hardware_type()) . PHP_EOL;
				$test_count++;
			}
		}

		if($test_count == 0)
		{
			echo PHP_EOL . 'No tests found. Please check that you have Internet connectivity to download test profile data from OpenBenchmarking.org. The Phoronix Test Suite has documentation on configuring the network setup, proxy settings, and PHP network options. Please contact Phoronix Media if you continuing to experience problems.' . PHP_EOL . PHP_EOL;
		}
	}
}

?>
