<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2016, Phoronix Media
	Copyright (C) 2008 - 2016, Michael Larabel
	phodevi_motherboard.php: The PTS Device Interface object for the motherboard

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

class phodevi_motherboard extends phodevi_device_interface
{
	public static function read_property($identifier)
	{
		switch($identifier)
		{
			case 'identifier':
				$property = new phodevi_device_property('motherboard_string', phodevi::smart_caching);
				break;
			case 'serial-number':
				$property = new phodevi_device_property('serial_number', phodevi::smart_caching);
				break;
			case 'power-mode':
				$property = new phodevi_device_property('power_mode', phodevi::smart_caching);
				break;
			case 'pci-devices':
				$property = new phodevi_device_property('pci_devices', phodevi::smart_caching);
				break;
			case 'usb-devices':
				$property = new phodevi_device_property('usb_devices', phodevi::std_caching);
				break;
		}

		return $property;
	}
	public static function usb_devices()
	{
		$usb = array();

		if(phodevi::is_linux())
		{
			foreach(pts_file_io::glob('/sys/bus/usb/devices/*-*/manufacturer') as $usb_dir)
			{
				$usb_dir = dirname($usb_dir) . '/';

				if(!is_file($usb_dir . 'product') || !is_file($usb_dir . 'idProduct') || !is_file($usb_dir . 'idVendor'))
				{
					continue;
				}

				$vendor = pts_strings::trim_search_query(pts_strings::strip_string(pts_file_io::file_get_contents($usb_dir . 'manufacturer')));
				$device = pts_strings::trim_search_query(pts_strings::strip_string(str_replace($vendor, null, pts_file_io::file_get_contents($usb_dir . 'product'))));
				$device = pts_strings::keep_in_string($device, pts_strings::CHAR_LETTER | pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DECIMAL | pts_strings::CHAR_SPACE | pts_strings::CHAR_DASH | pts_strings::CHAR_UNDERSCORE | pts_strings::CHAR_COLON | pts_strings::CHAR_COMMA);

				if($vendor == null || $device == null || $vendor == 'Generic')
				{
					continue;
				}

				array_push($usb, array(
					'Class' => pts_file_io::file_get_contents($usb_dir . 'bDeviceClass'),
					'Vendor' => $vendor,
					'Device' => $device,
					'VendorID' => pts_file_io::file_get_contents($usb_dir . 'idVendor'),
					'DeviceID' => pts_file_io::file_get_contents($usb_dir . 'idProduct')
					));
			}
		}

		return $usb;
	}
	public static function is_genuine($mobo)
	{
		return strpos($mobo, ' ') > 1 && !pts_strings::has_in_istring($mobo, array('Virtual', 'Bochs', '440BX', 'Megatrends', 'Award ', 'Software', 'Xen', 'HVM ', 'Notebook', 'OEM ', ' KVM', 'unknown')) && !is_numeric(substr($mobo, 0, strpos($mobo, ' ')));
		// pts_strings::string_contains($mobo, pts_strings::CHAR_NUMERIC);
	}
	public static function pci_devices()
	{
		$pci_devices = array();

		if(phodevi::is_linux() && isset(phodevi::$vfs->lspci))
		{
			$lspci = phodevi::$vfs->lspci;
			$lspci = explode("\n\n", $lspci);

			foreach($lspci as $o => &$lspci_section)
			{
				$lspci_section = explode("\n", $lspci_section);
				$formatted_section = array();

				foreach($lspci_section as $i => &$line)
				{
					$line = explode(':', $line);

					if(count($line) == 2 && in_array($line[0], array('Class', 'Vendor', 'Device', 'Driver', 'Rev', 'Module')))
					{
						$line[1] = trim($line[1]);

						if(($c = strrpos($line[1], ' [')) !== false)
						{
							$id = substr($line[1], ($c + 2));
							$id = '0x' . substr($id, 0, strpos($id, ']'));

							switch($line[0])
							{
								case 'Vendor':
									$formatted_section['VendorID'] = $id;
									break;
								case 'Device':
									$formatted_section['DeviceID'] = $id;
									break;
							}

							$line[1] = substr($line[1], 0, $c);
						}

						if($line[0] == 'Class')
						{
							switch($line[1])
							{
								case 'Ethernet controller':
								case 'Network controller':
									$line[1] = 'Network';
									break;
								case 'VGA compatible controller':
									$line[1] = 'GPU';
									break;
								case 'Audio device':
								case 'Multimedia audio controller':
									$line[1] = 'Audio';
									break;
							//	case 'RAM memory':
							//	case 'Host bridge':
							//		$line[1] = 'Chipset';
							//		break;
								default:
									$line[1] = null;
									break;
							}
						}
						else if($line[0] == 'Device' || $line[0] == 'Vendor')
						{
							$line[1] = pts_strings::trim_search_query(pts_strings::strip_string($line[1]));
							$line[1] = pts_strings::keep_in_string($line[1], pts_strings::CHAR_LETTER | pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DECIMAL | pts_strings::CHAR_SPACE | pts_strings::CHAR_DASH | pts_strings::CHAR_UNDERSCORE | pts_strings::CHAR_COLON | pts_strings::CHAR_COMMA);
						}

						$formatted_section[$line[0]] = $line[1];
					}
				}

				if(count($formatted_section) > 0 && $formatted_section['Class'] != null)
				{
					array_push($pci_devices, $formatted_section);
				}
			}
		}

		return $pci_devices;
	}
	public static function parse_pci_device_data(&$lspci, &$dmesg, $ignore_external_pci_devices = false)
	{
		$pci_devices = explode(PHP_EOL . PHP_EOL, $lspci);
		$sanitized_devices = array();

		foreach($pci_devices as &$device)
		{
			$device .= PHP_EOL;
			$location = substr($device, 0, strpos($device, ' '));

			if(!strpos($location, ':') || !strpos($location, '.'))
			{
				// If it's not a valid PCI bus location (i.e. XX:YY.Z), it's probably not formatted well or wrong
				continue;
			}

			$class = substr($device, ($s = (strpos($device, '[') + 1)), (strpos($device, ']', $s) - $s));

			if(!(isset($class[3]) && !isset($class[4])))
			{
				// class must be 4 characters: 2 for class, 2 for sub-class
				continue;
			}

			// 0300 is GPUs
			if($ignore_external_pci_devices && in_array($class, array('0300')))
			{
				// Don't report external PCI devices
				continue;
			}

			$device_class = substr($class, 0, 2);
			$sub_class = substr($class, 2, 2);

			$device_name = substr($device, ($l = strpos($device, ']:') + 3), ($s = strpos($device, ':', $l)) - $l);
			$device_name = substr($device_name, 0, strrpos($device_name, ' ['));
			$device_name = str_replace('/', '-', str_replace(array('[AMD]', '[SiS]'), null, $device_name));
			$device_name = pts_strings::strip_string($device_name);

			if($device_name == null || strpos($device_name, ' ') === false)
			{
				// it must be junk not worth reporting
				continue;
			}

			$temp = substr($device, $s - 5);
			if($temp[0] != '[' || $temp[10] != ']')
			{
				continue;
			}

			$vendor_id = substr($temp, 1, 4);
			$device_id = substr($temp, 6, 4);

			$drivers = array();
			if(($s = strpos($device, 'Kernel driver in use:')) !== false)
			{
				$temp = substr($device, ($s = $s + 22), (strpos($device, PHP_EOL, $s) - $s));

				if($temp != null)
				{
					array_push($drivers, $temp);
				}
			}
			if(($s = strpos($device, 'Kernel modules:')) !== false)
			{
				$temp = substr($device, ($s = $s + 16), (strpos($device, PHP_EOL, $s) - $s));

				if($temp != null)
				{
					foreach(explode(' ', trim($temp)) as $temp)
					{
						$temp = str_replace(',', null, $temp);
						if($temp != null && !in_array($temp, $drivers))
						{
							array_push($drivers, $temp);
						}
					}
				}
			}

			if(empty($drivers))
			{
				// If there's no drivers, nothing to report
				continue;
			}

			if(!in_array($vendor_id . ':' . $device_id, array_keys($sanitized_devices)))
			{
				$dmesg_example = array();

				if($dmesg != null)
				{
					foreach($drivers as $driver)
					{
						$offset = 1;
						while($offset != false && ($offset = strpos($dmesg, $driver, $offset)) !== false)
						{
							$line = substr($dmesg, 0, strpos($dmesg, "\n", $offset));
							$line = substr($line, strrpos($line, "\n"));
							$line = trim(substr($line, strpos($line, '] ') + 2));

							if($line != null && !isset($line[128]))
							{
								array_push($dmesg_example, $line);
							}
							$offset = strpos($dmesg, "\n", ($offset + 1));
						}
					}
				}

				$sanitized_devices[$vendor_id . ':' . $device_id] = array(
					$vendor_id,
					$device_id,
					$device_name,
					$device_class,
					$sub_class,
					$drivers,
					trim($device),
					implode(PHP_EOL, $dmesg_example)
					);
			}
		}

		return $sanitized_devices;
	}
	public static function power_mode()
	{
		// Returns the power mode
		$return_status = null;

		if(phodevi::is_linux())
		{
			$sysfs_checked = false;

			foreach(pts_file_io::glob('/sys/class/power_supply/AC*/online') as $online)
			{
				if(pts_file_io::file_get_contents($online) == '0')
				{
					$return_status = 'This computer was running on battery power';
					break;
				}
				$sysfs_checked = true;
			}

			if(!$sysfs_checked)
			{
				// There likely was no sysfs power_supply support for that power adapter
				$power_state = phodevi_linux_parser::read_acpi('/ac_adapter/AC/state', 'state');

				if($power_state == 'off-line')
				{
					$return_status = 'This computer was running on battery power';
				}
			}
		}

		return $return_status;
	}
	public static function serial_number()
	{
		$serial = null;

		if(phodevi::is_linux())
		{
			$serial = phodevi_linux_parser::read_dmidecode('system', 'System Information', 'Serial Number', true, array());
		}

		return $serial;
	}
	public static function motherboard_string()
	{
		// Returns the motherboard / system model name or number
		$info = null;

		if(phodevi::is_macosx())
		{
			$info = phodevi_osx_parser::read_osx_system_profiler('SPHardwareDataType', 'ModelName');
		}
		else if(phodevi::is_solaris())
		{
			$manufacturer = phodevi_solaris_parser::read_sun_ddu_dmi_info(array('MotherBoardInformation,Manufacturer', 'SystemInformation,Manufacturer'));
			$product = phodevi_solaris_parser::read_sun_ddu_dmi_info(array('MotherBoardInformation,Product', 'SystemInformation,Product', 'SystemInformation,Model'));

			if(count($manufacturer) == 1 && count($product) == 1)
			{
				$info = $manufacturer[0] . ' ' . $product[0];
			}
		}
		else if(phodevi::is_bsd())
		{
			$vendor = phodevi_bsd_parser::read_kenv('smbios.system.maker');
			$product = phodevi_bsd_parser::read_kenv('smbios.system.product');
			$version = phodevi_bsd_parser::read_kenv('smbios.system.version'); // for at least Lenovo ThinkPads this is where it displays ThinkPad model

			if($vendor != null && ($product != null || $version != null))
			{
				$info = $vendor . ' ' . $product . ' ' . $version;
			}
			else if(($vendor = phodevi_bsd_parser::read_sysctl('hw.vendor')) != false && ($version = phodevi_bsd_parser::read_sysctl(array('hw.version', 'hw.product'))) != false)
			{
				$info = trim($vendor . ' ' . $version);
			}
			else if(($acpi = phodevi_bsd_parser::read_sysctl('dev.acpi.0.%desc')) != false)
			{
				$info = trim($acpi);
			}
		}
		else if(phodevi::is_linux())
		{
			$vendor = phodevi_linux_parser::read_sys_dmi(array('board_vendor', 'sys_vendor'));
			$name = phodevi_linux_parser::read_sys_dmi(array('board_name', 'product_name'));
			$version = phodevi_linux_parser::read_sys_dmi(array('board_version', 'product_version'));

			if($vendor != false && $name != false)
			{
				$info = strpos($name . ' ', $vendor . ' ') === false ? $vendor . ' ' : null;
				$info .= $name;

				if($version != false && strpos($info, $version) === false && pts_strings::string_only_contains($version, pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DECIMAL))
				{
					$info .= (substr($version, 0, 1) == 'v' ? ' ' : ' v') . $version;
				}
			}

			if(empty($info))
			{
				$from_cpuinfo = false;
				if($info == null)
				{
					$hw_string = phodevi_linux_parser::read_cpuinfo('Hardware');

					if(count($hw_string) == 1)
					{
						$info = $hw_string[0];
						$from_cpuinfo = true;
					}
				}

				$bios_vendor = phodevi_linux_parser::read_sys_dmi('bios_vendor');
				$bios_version = phodevi_linux_parser::read_sys_dmi('bios_version');
				if($bios_vendor != null)
				{
					$info = $bios_vendor . ' ' . $bios_version;
				}

				if($info == null)
				{
					$hw_string = phodevi_linux_parser::read_cpuinfo('machine');

					if(count($hw_string) == 1)
					{
						$info = $hw_string[0];
						$from_cpuinfo = true;
					}
				}

				if($from_cpuinfo && is_readable('/sys/firmware/devicetree/base/model'))
				{
					$dt_model = pts_file_io::file_get_contents('/sys/firmware/devicetree/base/model');

					if($info == null || stripos($dt_model, $info) === false)
					{
						$info = trim($info . ' ' . $dt_model);
					}
				}
			}

			if(empty($info))
			{
				$info = phodevi_linux_parser::read_sys_dmi('product_name');
			}

			if(empty($info) && is_file('/sys/bus/soc/devices/soc0/machine'))
			{
				$info = pts_file_io::file_get_contents('/sys/bus/soc/devices/soc0/machine');
			}
			if(empty($info))
			{
				// Works on the MIPS Creator CI20
				$hardware = phodevi_linux_parser::read_cpuinfo('Hardware');

				if(!empty($hardware))
				{
					$info = array_pop($hardware);
				}
			}
		}
		else if(phodevi::is_windows())
		{
			$info = phodevi_windows_parser::read_cpuz('Mainboard Model', null);
		}

		if((strpos($info, 'Mac ') !== false || strpos($info, 'MacBook') !== false) && strpos($info, 'Apple') === false)
		{
			$info = 'Apple ' . $info;
		}

		// ensure words aren't repeated (e.g. VMware VMware Virtual and MSI MSI X58M (MS-7593))
		$info = implode(' ', array_unique(explode(' ', $info)));

		return $info;
	}
}

?>
