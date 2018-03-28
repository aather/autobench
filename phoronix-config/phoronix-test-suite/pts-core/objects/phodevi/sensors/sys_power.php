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

class sys_power extends phodevi_sensor
{
	const SENSOR_TYPE = 'sys';
	const SENSOR_SENSES = 'power';

	private static $battery_sys = false;
	private static $battery_cur = false;
	private static $wattsup_meter = false;
	private static $ipmitool = false;

	public static function get_unit()
	{
		$unit = null;

		if(self::$battery_sys)
		{
			$unit = 'Milliwatts';
		}
		else if(self::$battery_cur)
		{
			$unit = 'microAmps';
		}
		else if(self::$wattsup_meter || self::$ipmitool)
		{
			$unit = 'Watts';
		}

		return $unit;
	}
	public function support_check()
	{
		$test = self::sys_battery_power();
		if(is_numeric($test) && $test != -1)
		{
			self::$battery_sys = true;
			return true;
		}

		$test = self::sys_power_current();
		if(is_numeric($test) && $test != -1)
		{
			self::$battery_cur = true;
			return true;
		}

		if(pts_client::executable_in_path('wattsup'))
		{
			$wattsup = self::watts_up_power_meter();

			if($wattsup > 0.5 && is_numeric($wattsup))
			{
				self::$wattsup_meter = true;
				return true;
			}
		}

		if(pts_client::executable_in_path('ipmitool'))
		{
			$ipmi_read = phodevi_linux_parser::read_ipmitool_sensor('Node Power');

			if($ipmi_read > 0 && is_numeric($ipmi_read))
			{
				self::$ipmitool = true;
				return true;
			}
		}
	}
	public function read_sensor()
	{
		if(self::$battery_sys)
		{
			return self::sys_battery_power();
		}
		else if(self::$battery_cur)
		{
			return self::sys_power_current();
		}
		else if(self::$wattsup_meter)
		{
			return self::watts_up_power_meter();
		}
		else if(self::$ipmitool)
		{
			return phodevi_linux_parser::read_ipmitool_sensor('Node Power');
		}
	}
	private static function watts_up_power_meter()
	{
		$output = trim(shell_exec('wattsup -c 1 ttyUSB0 watts 2>&1'));
		$output = explode(PHP_EOL, $output);

		do
		{
			$value = array_pop($output);
		}
		while(!is_numeric($value) && count($output) > 0);

		return is_numeric($value) ? $value : -1;
	}
	private static function sys_power_current()
	{
		// Returns power consumption rate in uA
		$current = -1;

		if(phodevi::is_linux())
		{
			$raw_current = phodevi_linux_parser::read_sysfs_node('/sys/devices/w1_bus_master1/*/getcurrent', 'NO_CHECK');

			if($raw_current != -1)
			{
				if(substr($raw_current, 0, 1) == '-')
				{
					$current = substr($raw_current, 1);
				}
			}
		}
		else if(phodevi::is_macosx())
		{
			$current = abs(phodevi_osx_parser::read_osx_system_profiler('SPPowerDataType', 'Amperage')); // in mA
		}

		return $current;
	}
	private static function sys_battery_power()
	{
		// Returns power consumption rate in mW
		$rate = -1;

		if(phodevi::is_linux())
		{
			$power_now = phodevi_linux_parser::read_sysfs_node('/sys/class/power_supply/*/power_now', 'POSITIVE_NUMERIC', array('status' => 'Discharging'));

			if($power_now != -1)
			{
				// sysfs power_now seems to be displayed in microWatts
				$rate = pts_math::set_precision($power_now / 1000, 2);
			}

			if($rate == -1)
			{
				$battery = array('/battery/BAT0/state', '/battery/BAT1/state');
				$state = phodevi_linux_parser::read_acpi($battery, 'charging state');
				$power = phodevi_linux_parser::read_acpi($battery, 'present rate');
				$voltage = phodevi_linux_parser::read_acpi($battery, 'present voltage');

				if($state == 'discharging')
				{
					$power_unit = substr($power, strrpos($power, ' ') + 1);
					$power = substr($power, 0, strpos($power, ' '));

					if($power_unit == 'mA')
					{
						$voltage_unit = substr($voltage, strrpos($voltage, ' ') + 1);
						$voltage = substr($voltage, 0, strpos($voltage, ' '));

						if($voltage_unit == 'mV')
						{
							$rate = round(($power * $voltage) / 1000);
						}
					}
					else if($power_unit == 'mW')
					{
						$rate = $power;
					}
				}
			}

			if($rate == -1 && is_file('/sys/class/power_supply/BAT0/voltage_now') && is_file('/sys/class/power_supply/BAT0/current_now'))
			{
				$voltage_now = pts_file_io::file_get_contents('/sys/class/power_supply/BAT0/voltage_now') / 1000;
				$current_now = pts_file_io::file_get_contents('/sys/class/power_supply/BAT0/current_now') / 1000;
				$power_now = $voltage_now * $current_now / 1000;

				if($power_now > 1)
				{
					$rate = $power_now;
				}
			}
			if($rate == -1 && is_file('/sys/class/power_supply/BAT1/voltage_now') && is_file('/sys/class/power_supply/BAT1/current_now'))
			{
				$voltage_now = pts_file_io::file_get_contents('/sys/class/power_supply/BAT1/voltage_now') / 1000;
				$current_now = pts_file_io::file_get_contents('/sys/class/power_supply/BAT1/current_now') / 1000;
				$power_now = $voltage_now * $current_now / 1000;

				if($power_now > 1)
				{
					$rate = $power_now;
				}
			}
		}
		else if(phodevi::is_macosx())
		{
			$amperage = abs(phodevi_osx_parser::read_osx_system_profiler('SPPowerDataType', 'Amperage')); // in mA
			$voltage = phodevi_osx_parser::read_osx_system_profiler('SPPowerDataType', 'Voltage'); // in mV

			if($amperage > 0 && $voltage > 0)
			{
				$rate = round(($amperage * $voltage) / 1000);
			}
			else if(pts_client::executable_in_path('ioreg'))
			{
				$ioreg = trim(shell_exec("ioreg -l | grep LegacyBatteryInfo | cut -d '{' -f 2 | tr -d \} | tr ',' '=' | awk -F'=' '{print ($2*$10/10^22)}' 2>&1"));

				if(is_numeric($ioreg) && $ioreg > 0)
				{
					$rate = $ioreg;
				}
			}
		}
		else if(phodevi::is_solaris())
		{
			$battery = phodevi_solaris_parser::read_hal_property('/org/freedesktop/Hal/devices/pseudo/acpi_drv_0_battery0_0', 'battery.reporting.rate');

			if(is_numeric($battery))
			{
				$rate = $battery;
			}
		}
		else if(phodevi::is_bsd())
		{
			$battery = phodevi_bsd_parser::read_acpiconf('Present rate');

			if($battery && substr($battery, -2) == 'mW')
			{
				$rate = substr($battery, 0, strpos($battery, ' '));
			}
		}

		return $rate;
	}
}

?>
