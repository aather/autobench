<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2010 - 2017, Phoronix Media
	Copyright (C) 2010 - 2017, Michael Larabel

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

class pts_test_install_request
{
	public $test_profile;
	private $test_files;
	public $install_time_duration = -1;
	public $compiler_mask_dir = false;
	public $install_error = null;
	public $special_environment_vars;

	public function __construct($test)
	{
		if($test instanceof pts_test_profile)
		{
			$this->test_profile = $test;
		}
		else
		{
			$this->test_profile = new pts_test_profile($test);
		}

		$this->test_files = array();
		$this->special_environment_vars = array();
	}
	public static function read_download_object_list($test, $do_file_checks = true)
	{
		// A way to get just the download object list if needed
		$test_install_request = new pts_test_install_request($test);
		$test_install_request->generate_download_object_list($do_file_checks);

		return $test_install_request->get_download_objects();
	}
	public function __toString()
	{
		return $this->test_profile->get_identifier();
	}
	public function get_download_objects()
	{
		return $this->test_files;
	}
	public function get_download_object_count()
	{
		return count($this->test_files);
	}
	public function generate_download_object_list($do_file_checks = true)
	{
		$download_xml_file = $this->test_profile->get_file_download_spec();

		if($download_xml_file != null)
		{
			$xml_options = LIBXML_COMPACT | LIBXML_PARSEHUGE;
			$xml = simplexml_load_file($download_xml_file, 'SimpleXMLElement', $xml_options);

			if($xml->Downloads && $xml->Downloads->Package)
			{
				foreach($xml->Downloads->Package as $pkg)
				{
					// Check for platform compatibility
					$pkg_platforms = isset($pkg->PlatformSpecific) ? $pkg->PlatformSpecific->__toString() : null;
					if(!empty($pkg_platforms) && $do_file_checks)
					{
						$platforms = pts_strings::comma_explode($pkg_platforms);
						if(!in_array(phodevi::operating_system(), $platforms) && !(phodevi::is_bsd() && in_array('Linux', $platforms) && (pts_client::executable_in_path('kldstat') && strpos(shell_exec('kldstat -n linux 2>&1'), 'linux.ko') != false)))
						{
							// This download does not match the operating system
							continue;
						}
					}

					// Check for architecture compatibility
					$pkg_architecture = isset($pkg->ArchitectureSpecific) ? $pkg->ArchitectureSpecific->__toString() : null;
					if(!empty($pkg_architecture) && $do_file_checks)
					{
						$architectures = pts_strings::comma_explode($pkg_architecture);

						if(phodevi::cpu_arch_compatible($architectures) == false)
						{
							// This download does not match the CPU architecture
							continue;
						}
					}

					$pkg_url = isset($pkg->URL) ? $pkg->URL->__toString() : null;
					$pkg_md5 = isset($pkg->MD5) ? $pkg->MD5->__toString() : null;
					$pkg_sha256 = isset($pkg->SHA256) ? $pkg->SHA256->__toString() : null;
					$pkg_filename = isset($pkg->FileName) ? $pkg->FileName->__toString() : null;
					$pkg_filesize = isset($pkg->FileSize) ? $pkg->FileSize->__toString() : null;
					$pkg_architecture = isset($pkg->ArchitectureSpecific) ? $pkg->ArchitectureSpecific->__toString() : null;
					$this->test_files[] = new pts_test_file_download($pkg_url, $pkg_filename, $pkg_filesize, $pkg_md5, $pkg_sha256, $pkg_platforms, $pkg_architecture);
				}
			}
		}
	}
	public static function test_files_available_locally(&$test_profile, $include_extended_test_profiles = true)
	{
		$install_request = new pts_test_install_request($test_profile);

		$remote_files = pts_test_install_manager::remote_files_available_in_download_caches();
		$local_download_caches = pts_test_install_manager::local_download_caches();
		$remote_download_caches = pts_test_install_manager::remote_download_caches();
		$phoromatic_server_caches = pts_test_install_manager::phoromatic_download_server_caches();

		$install_request->generate_download_object_list();
		$install_request->scan_download_caches($local_download_caches, $remote_download_caches, $remote_files, $phoromatic_server_caches);

		foreach($install_request->get_download_objects() as $download_object)
		{
			if($download_object->get_download_location_type() == null)
			{
				return false;
			}
		}

		foreach($install_request->test_profile->extended_test_profiles() as $extended_test_profile)
		{
			if(self::test_files_available_locally($extended_test_profile) == false)
			{
				return false;
			}
		}

		return true;
	}
	public static function test_files_in_cache(&$test_profile, $include_extended_test_profiles = true, $skip_hash_checks = false)
	{
		$install_request = new pts_test_install_request($test_profile);

		$remote_files = false;
		$local_download_caches = pts_test_install_manager::local_download_caches();
		$remote_download_caches = false;
		$phoromatic_server_caches = false;

		$install_request->generate_download_object_list();
		$install_request->scan_download_caches($local_download_caches, $remote_download_caches, $remote_files, $phoromatic_server_caches, $skip_hash_checks);

		foreach($install_request->get_download_objects() as $download_object)
		{
			if($download_object->get_download_location_type() == null)
			{
				return false;
			}
		}

		foreach($install_request->test_profile->extended_test_profiles() as $extended_test_profile)
		{
			if(self::test_files_available_locally($extended_test_profile) == false)
			{
				return false;
			}
		}

		return true;
	}
	public function scan_download_caches($local_download_caches, $remote_download_caches, $remote_files, $phoromatic_server_caches, $skip_hash_checks = false)
	{
		$download_location = $this->test_profile->get_install_dir();
		$main_download_cache = pts_strings::add_trailing_slash(pts_strings::parse_for_home_directory(pts_config::read_user_config('PhoronixTestSuite/Options/Installation/CacheDirectory', PTS_DOWNLOAD_CACHE_PATH)));

		foreach($this->test_files as &$download_package)
		{
			$package_filename = $download_package->get_filename();

			if(is_file($download_location . $package_filename))
			{
				// File is already there in the test/destination directory, must have been previously downloaded
				// Could add an MD5 check here to ensure validity, but if it made it here it was already valid unless user modified it

				if($download_package->get_filesize() == 0)
				{
					$download_package->set_filesize(filesize($download_location . $package_filename));
				}

				$download_package->set_download_location('IN_DESTINATION_DIR');
			}
			else if(is_file($main_download_cache . $package_filename))
			{
				// In main download cache
				if($download_package->get_filesize() == 0)
				{
					$download_package->set_filesize(filesize($main_download_cache . $package_filename));
				}

				$download_package->set_download_location('MAIN_DOWNLOAD_CACHE', array($main_download_cache . $package_filename));
			}
			else if(is_file(PTS_SHARE_PATH . 'download-cache/' . $package_filename))
			{
				// In system's /usr/share download cache
				if($download_package->get_filesize() == 0)
				{
					$download_package->set_filesize(filesize(PTS_SHARE_PATH . 'download-cache/' . $package_filename));
				}

				$download_package->set_download_location('MAIN_DOWNLOAD_CACHE', array(PTS_SHARE_PATH . 'download-cache/' . $package_filename));
			}
			else
			{
				// Scan the local download caches
				foreach($local_download_caches as &$cache_directory)
				{
					if(is_file($cache_directory . $package_filename) && ($skip_hash_checks || $download_package->check_file_hash($cache_directory . $package_filename)))
					{
						if($download_package->get_filesize() == 0)
						{
							$download_package->set_filesize(filesize($cache_directory . $package_filename));
						}

						$download_package->set_download_location('LOCAL_DOWNLOAD_CACHE', array($cache_directory . $package_filename));
						break;
					}
				}

				// Look-aside download cache copy
				// Check to see if the same package name with the same package check-sum is already present in another test installation
				$lookaside_copy = pts_test_install_manager::file_lookaside_test_installations($download_package);
				if($lookaside_copy)
				{
					if($download_package->get_filesize() == 0)
					{
						$download_package->set_filesize(filesize($lookaside_copy));
					}

					$download_package->set_download_location('LOOKASIDE_DOWNLOAD_CACHE', array($lookaside_copy));
				}

				// Check Phoromatic server caches
				if($download_package->get_download_location_type() == null && $phoromatic_server_caches)
				{
					foreach($phoromatic_server_caches as $server_url => $repo)
					{
						if(isset($repo[$package_filename]) && ($skip_hash_checks || $repo[$package_filename]['md5'] == $download_package->get_md5() || $repo[$package_filename]['sha256'] == $download_package->get_sha256() || ($download_package->get_sha256() == null && $download_package->get_md5() == null)))
						{
							$download_package->set_download_location('REMOTE_DOWNLOAD_CACHE', array($server_url . '/download-cache.php?download=' . $package_filename));
							break;
						}
					}
				}

				// If still not found, check remote download caches
				if($download_package->get_download_location_type() == null)
				{
					if(isset($remote_files[$package_filename]))
					{
						$download_package->set_download_location('REMOTE_DOWNLOAD_CACHE', $remote_files[$package_filename]);
					}
					else if(!empty($remote_download_caches))
					{
						// Check for files manually
						foreach($remote_download_caches as $remote_dir)
						{
							$remote_file = $remote_dir . $package_filename;
							$stream_context = pts_network::stream_context_create();
							$file_pointer = fopen($remote_file, 'r', false, $stream_context);

							if($file_pointer !== false)
							{
								$download_package->set_download_location('REMOTE_DOWNLOAD_CACHE', $remote_file);
								break;
							}
						}
					}
				}
			}
		}
	}
	public function get_arguments_description()
	{
		return null;
	}
}

?>
