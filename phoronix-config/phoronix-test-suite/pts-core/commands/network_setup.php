<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2009 - 2011, Phoronix Media
	Copyright (C) 2009 - 2011, Michael Larabel

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

class network_setup implements pts_option_interface
{
	const doc_section = 'Other';
	const doc_description = 'This option allows the user to configure how the Phoronix Test Suite connects to OpenBenchmarking.org and other web-services. Connecting through an HTTP proxy can be configured through this option.';

	public static function run($r)
	{
		pts_client::$display->generic_heading('Network Setup');

		if(!pts_user_io::prompt_bool_input('Configure the Phoronix Test Suite to use a HTTP proxy', false))
		{
			return false;
		}

		$proxy_address = pts_user_io::prompt_user_input('Enter IP address / server name of proxy');
		$proxy_port = pts_user_io::prompt_user_input('Enter TCP port for proxy server');

		echo PHP_EOL . 'Testing Proxy Server (' . $proxy_address . ':' . $proxy_port . ')' . PHP_EOL;

		if(pts_network::http_get_contents('http://www.phoronix-test-suite.com/PTS', $proxy_address, $proxy_port) == 'PTS')
		{
			echo PHP_EOL . 'Proxy Setup Completed; Storing Network Settings.' . PHP_EOL;
			pts_config::user_config_generate(array(
				'PhoronixTestSuite/Options/Networking/ProxyAddress' => $proxy_address,
				'PhoronixTestSuite/Options/Networking/ProxyPort' => $proxy_port
				));
		}
		else
		{
			echo PHP_EOL . 'Proxy Setup Failed.' . PHP_EOL;
		}
	}
}

?>
