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

class cpu_voltage extends phodevi_sensor
{
	const SENSOR_TYPE = 'cpu';
	const SENSOR_SENSES = 'voltage';
	const SENSOR_UNIT = 'Volts';

	public function read_sensor()
	{
		$voltage = -1;

		if(phodevi::is_linux())
		{
			$voltage = $this->cpu_voltage_linux();
		}

		return $voltage;
	}
	private function cpu_voltage_linux()
	{
		$voltage = phodevi_linux_parser::read_sensors(array('VCore', 'Vcore'));
		return $voltage;
	}

}

?>
