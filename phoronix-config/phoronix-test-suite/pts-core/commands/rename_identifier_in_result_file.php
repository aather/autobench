<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2015, Phoronix Media
	Copyright (C) 2009 - 2015, Michael Larabel

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

class rename_identifier_in_result_file implements pts_option_interface
{
	const doc_section = 'Result Management';
	const doc_description = 'This option is used if you wish to change the name of the identifier in a test results file that is shown in the Phoronix Test Suite Results Viewer and the contained graphs.';

	public static function argument_checks()
	{
		return array(
		new pts_argument_check(0, array('pts_types', 'is_result_file'), null)
		);
	}
	public static function invalid_command($passed_args = null)
	{
		pts_tests::recently_saved_results();
	}
	public static function run($r)
	{
		$result = $r[0];

		$result_file = new pts_result_file($result);
		$result_file_identifiers = $result_file->get_system_identifiers();

		$rename_identifier = pts_user_io::prompt_text_menu('Select the test run to rename', $result_file_identifiers);
		$rename_identifier_new = pts_user_io::prompt_user_input('Enter the new identifier');

		foreach(array('test-logs', 'system-logs', 'installation-logs') as $dir_name)
		{
			if(is_dir(PTS_SAVE_RESULTS_PATH . $r[0] . '/' . $dir_name . '/' . $rename_identifier))
			{
				rename(PTS_SAVE_RESULTS_PATH . $r[0] . '/' . $dir_name . '/' . $rename_identifier, PTS_SAVE_RESULTS_PATH . $r[0] . '/' . $dir_name . '/' . $rename_identifier_new);
			}
		}

		$result_file->rename_run($rename_identifier, $rename_identifier_new);
		pts_client::save_test_result($result_file->get_file_location(), $result_file->get_xml());
		pts_client::display_web_page(PTS_SAVE_RESULTS_PATH . $r[0] . '/index.html');
	}
}

?>
