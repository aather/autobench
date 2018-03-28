<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2013, Phoronix Media
	Copyright (C) 2008 - 2013, Michael Larabel

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

class refresh_graphs implements pts_option_interface
{
	const doc_section = 'Result Management';
	const doc_description = 'This option will re-render and save all result graphs within a saved file. This option can be used when making modifications to the graphing code or its color/option configuration file and testing the changes.';

	public static function command_aliases()
	{
		return array('refresh_graph');
	}
	public static function argument_checks()
	{
		return array(
		new pts_argument_check(0, array('pts_types', 'is_result_file'), null)
		);
	}
	public static function run($r)
	{
		$identifier = $r[0];
		pts_client::regenerate_graphs($identifier, 'The ' . $identifier . ' result file graphs have been refreshed.');
	}
	public static function invalid_command($passed_args = null)
	{
		pts_tests::invalid_command_helper($passed_args);
	}
}

?>
