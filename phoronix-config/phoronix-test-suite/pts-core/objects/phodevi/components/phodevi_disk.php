<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2017, Phoronix Media
	Copyright (C) 2008 - 2017, Michael Larabel
	phodevi_disk.php: The PTS Device Interface object for the system disk(s)

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

class phodevi_disk extends phodevi_device_interface
{
	public static function read_property($identifier)
	{
		switch($identifier)
		{
			case 'identifier':
				$property = new phodevi_device_property('hdd_string', phodevi::smart_caching);
				break;
			case 'scheduler':
				$property = new phodevi_device_property('hdd_scheduler', phodevi::no_caching);
				break;
			case 'mount-options':
				$property = new phodevi_device_property('proc_mount_options', phodevi::no_caching);
				break;
			case 'mount-options-string':
				$property = new phodevi_device_property('proc_mount_options_string', phodevi::no_caching);
				break;
			case 'extra-disk-details':
				$property = new phodevi_device_property('extra_disk_details', phodevi::no_caching);
				break;
		}

		return $property;
	}
	public static function device_notes()
	{
		$notes = array();

		if(($disk_scheduler = phodevi::read_property('disk', 'scheduler')) != null)
		{
			array_push($notes, 'Disk Scheduler: ' . $disk_scheduler);
		}

		return $notes;
	}
	public static function proc_mount_options($mount_point = null, $mounts = null)
	{
		$mount_options = false;
		if(phodevi::is_windows())
		{
			// TODO support Windows if relevant?
			// Currently this function hangs Windows client
			return $mount_options;
		}

		if($mount_point == null && PTS_IS_CLIENT)
		{
			$mount_point = pts_client::test_install_root_path();
		}
		if($mounts == null && isset(phodevi::$vfs->mounts))
		{
			$mounts = phodevi::$vfs->mounts;
		}

		do
		{
			$mount_point = dirname($mount_point);
		}
		while(($p = strrpos($mounts, ' ' . $mount_point . ' ')) === false && $mount_point != null && $mount_point != '/');

		if($p)
		{
			if(($x = strrpos($mounts, PHP_EOL, (0 - strlen($mounts) + $p))) !== false)
			{
				$mounts = trim(substr($mounts, $x));
			}

			if(($x = strpos($mounts, PHP_EOL)) !== false)
			{
				$mounts = substr($mounts, 0, $x);
			}

			$mounts = explode(' ', $mounts);

			if(isset($mounts[4]) && $mounts[1] == $mount_point && substr($mounts[0], 0, 1) == '/')
			{
				// Sort mount options alphabetically so it's easier to look at...
				$mounts[3] = explode(',', $mounts[3]);
				sort($mounts[3]);
				$mounts[3] = implode(',', $mounts[3]);

				$mount_options = array(
					'device' => $mounts[0],
					'mount-point' => $mounts[1],
					'file-system' => $mounts[2],
					'mount-options' => $mounts[3]
					);
			}
		}

		return $mount_options;
	}
	public static function proc_mount_options_string($mount_point = null, $mounts = null)
	{
		$mo = phodevi::read_property('disk', 'mount-options');

		if(isset($mo['mount-options']))
		{
			return $mo['mount-options'];
		}

		return null;
	}
	public static function is_genuine($disk)
	{
		return strpos($disk, ' ') > 1 && !pts_strings::has_in_istring($disk, array('VBOX', 'QEMU', 'Virtual'));
		// pts_strings::string_contains($mobo, pts_strings::CHAR_NUMERIC);
	}
	public static function hdd_string()
	{
		$disks = array();

		if(phodevi::is_macosx())
		{
			// TODO: Support reading non-SATA drives and more than one drive
			$capacity = phodevi_osx_parser::read_osx_system_profiler('SPSerialATADataType', 'Capacity');
			$model = phodevi_osx_parser::read_osx_system_profiler('SPSerialATADataType', 'Model');

			if(($cut = strpos($capacity, ' (')) !== false)
			{
				$capacity = substr($capacity, 0, $cut);
			}

			if(($cut = strpos($capacity, ' ')) !== false)
			{
				if(is_numeric(substr($capacity, 0, $cut)))
				{
					$capacity = floor(substr($capacity, 0, $cut)) . substr($capacity, $cut);
				}
			}

			$capacity = str_replace(' GB', 'GB', $capacity);

			if(!empty($capacity) && !empty($model))
			{
				$disks = array($capacity . ' ' . $model);
			}
		}
		else if(phodevi::is_bsd())
		{
			$i = 0;

			do
			{
				$disk = phodevi_bsd_parser::read_sysctl('dev.ad.' . $i . '.%desc');

				if($disk != false && strpos($disk, 'DVD') === false && strpos($disk, 'ATAPI') === false)
				{
					array_push($disks, $disk);
				}
				$i++;
			}
			while(($disk != false || $i < 9) && $i < 128);
			// On some systems, the first drive seems to be at dev.ad.8 rather than starting at dev.ad.0

			if(empty($disks) && pts_client::executable_in_path('camcontrol'))
			{
				$camcontrol = trim(shell_exec('camcontrol devlist 2>&1'));

				foreach(explode(PHP_EOL, $camcontrol) as $line)
				{
					if(substr($line, 0, 1) == '<' && ($model_end = strpos($line, '>')) !== false && strpos($line, 'DVD') === false && strpos($line, 'ATAPI') === false)
					{
						$disk = self::prepend_disk_vendor(substr($line, 1, ($model_end - 1)));
						array_push($disks, $disk);
					}
				}
			}
		}
		else if(phodevi::is_solaris())
		{
			if(is_executable('/usr/ddu/bin/i386/hd_detect'))
			{
				$hd_detect = explode(PHP_EOL, trim(shell_exec('/usr/ddu/bin/i386/hd_detect -l 2>&1')));

				foreach($hd_detect as $hd_line)
				{
					if(isset($hd_line) && ($hd_pos = strpos($hd_line, ':/')) != false)
					{
						$disk = trim(substr($hd_line, 0, $hd_pos));
						$disk = self::prepend_disk_vendor($disk);

						if($disk != 'blkdev')
						{
							array_push($disks, $disk);
						}
					}
				}
			}

		}
		else if(phodevi::is_linux())
		{
			$disks_formatted = array();
			$disks = array();

			foreach(array_merge(pts_file_io::glob('/sys/block/sd*'), pts_file_io::glob('/sys/block/mmcblk*'), pts_file_io::glob('/sys/block/nvme*')) as $sdx)
			{
				if(strpos($sdx, 'boot') !== false)
				{
					// Don't include devices like /sys/block/mmcblk0boot[0,1] as it's repeat of /sys/block/mmcblk0
					continue;
				}

				if((is_file($sdx . '/device/name') || is_file($sdx . '/device/model')) && is_file($sdx . '/size'))
				{
					$disk_size = pts_file_io::file_get_contents($sdx . '/size');
					$disk_model = pts_file_io::file_get_contents($sdx .  (is_file($sdx . '/device/model') ? '/device/model' : '/device/name'));
					$disk_removable = pts_file_io::file_get_contents($sdx . '/removable');

					if($disk_removable == '1')
					{
						// Don't count removable disks
						continue;
					}

					$disk_size = round($disk_size * 512 / 1000000000) . 'GB';
					$disk_model = self::prepend_disk_vendor($disk_model);

					if(strpos($disk_model, $disk_size . ' ') === false && strpos($disk_model, ' ' . $disk_size) === false && $disk_size != '1GB')
					{
						$disk_model = $disk_size . ' ' . $disk_model;
					}

					if($disk_size > 0)
					{
						array_push($disks_formatted, $disk_model);
					}
				}
			}

			for($i = 0; $i < count($disks_formatted); $i++)
			{
				if(!empty($disks_formatted[$i]))
				{
					$times_found = 1;

					for($j = ($i + 1); $j < count($disks_formatted); $j++)
					{
						if($disks_formatted[$i] == $disks_formatted[$j])
						{
							$times_found++;
							$disks_formatted[$j] = '';
						}
					}

					$disk = ($times_found > 1 ? $times_found . ' x '  : null) . $disks_formatted[$i];
					array_push($disks, $disk);
				}
			}
		}

		if(count($disks) == 0)
		{
			$root_disk_size = ceil(disk_total_space('/') / 1073741824);
			$pts_disk_size = ceil(disk_total_space(pts_client::test_install_root_path()) / 1073741824);

			if($pts_disk_size > $root_disk_size)
			{
				$root_disk_size = $pts_disk_size;
			}

			if($root_disk_size > 1)
			{
				$disks = $root_disk_size . 'GB';
			}
			else
			{
				$disks = null;
			}
		}
		else
		{
			$disks = implode(' + ', $disks);
		}

		return $disks;
	}
	protected static function prepend_disk_vendor($disk_model)
	{
		if(isset($disk_model[4]))
		{
			$disk_manufacturer = null;
			$third_char = substr($disk_model, 2, 1);

			switch(substr($disk_model, 0, 2))
			{
				case 'WD':
					$disk_manufacturer = 'Western Digital';

					if(substr($disk_model, 0, 4) == 'WDC ')
					{
						$disk_model = substr($disk_model, 4);
					}
					break;
				case 'MK':
					$disk_manufacturer = 'Toshiba';
					break;
				case 'HD':
					if($third_char == 'T')
					{
						$disk_manufacturer = 'Hitachi';
					}
					break;
				case 'HT':
					$disk_manufacturer = 'Hitachi';
					break;
				case 'HM':
				case 'HN':
					// HM and HN appear to be Samsung series
					$disk_manufacturer = 'Samsung';
					break;
				case 'ST':
					if($third_char == 'T')
					{
						$disk_manufacturer = 'Super Talent';
					}
					else if($third_char != 'E')
					{
						$disk_manufacturer = 'Seagate';
					}
					break;
			}

			if($disk_manufacturer != null && strpos($disk_model, $disk_manufacturer) === false)
			{
				$disk_model = $disk_manufacturer . ' ' . $disk_model;
			}

			// OCZ SSDs aren't spaced
			$disk_model = str_replace('OCZ-', 'OCZ ', $disk_model);
		}

		return $disk_model;
	}
	public static function hdd_scheduler()
	{
		$scheduler = null;
		$device = self::proc_mount_options();
		$device = basename($device['device']);

		if(is_readable('/sys/block/' . ($d = pts_strings::keep_in_string($device, pts_strings::CHAR_LETTER)) . '/queue/scheduler'))
		{
			$scheduler = '/sys/block/' . $d . '/queue/scheduler';
		}
		else if(is_link(($device = '/dev/disk/by-uuid/' . $device)))
		{
			// Go from the disk UUID to the device
			$device = pts_strings::keep_in_string(basename(readlink($device)), pts_strings::CHAR_LETTER);

			if(is_readable('/sys/block/' . $device . '/queue/scheduler'))
			{
				$scheduler = '/sys/block/' . $device . '/queue/scheduler';
			}
		}
		else if(is_readable('/sys/block/sda/queue/scheduler'))
		{
			$scheduler = '/sys/block/sda/queue/scheduler';
		}

		if($scheduler)
		{
			$scheduler = pts_file_io::file_get_contents($scheduler);

			if(($s = strpos($scheduler, '[')) !== false && ($e = strpos($scheduler, ']', $s)) !== false)
			{
				$scheduler = strtoupper(substr($scheduler, ($s + 1), ($e - $s - 1)));
			}
		}

		return $scheduler;
	}
	public static function extra_disk_details()
	{
		$device = self::proc_mount_options();
		$mount_point = $device['mount-point'];
		$extra_details = null;

		if(strtolower($device['file-system']) == 'btrfs' && pts_client::executable_in_path('btrfs'))
		{
			$btrfs_fi_df = shell_exec('btrfs fi df ' . $mount_point . ' 2>&1');
			if(($f = strpos($btrfs_fi_df, 'Data, ')) !== false)
			{
				$btrfs_fi_df = substr($btrfs_fi_df, ($f + strlen('Data, ')));
				$btrfs_fi_df = substr($btrfs_fi_df, 0, strpos($btrfs_fi_df, ': '));

				if(strpos($btrfs_fi_df, 'RAID') !== false)
				{
					$extra_details = $btrfs_fi_df;
				}
			}
		}
		if($extra_details == null && strpos($device['device'], '/dev/md') !== false && is_file('/proc/mdstat'))
		{
			// Show mdstat details
			$md = strstr(file_get_contents('/proc/mdstat'), basename($device['device']));
			$md = substr($md, 0, strpos($md, PHP_EOL));
			if(($x = strpos($md, 'active')) !== false)
			{
				$extra_details = trim(substr($md, $x + 7));
			}
		}


		return $extra_details;
	}
}

?>
