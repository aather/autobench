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

class pts_network
{
	private static $disable_network_support = false;
	private static $disable_internet_support = false;
	private static $network_proxy = false;
	private static $network_timeout = 20;

	public static function is_proxy_setup()
	{
		return self::$network_proxy == false;
	}
	public static function internet_support_available()
	{
		return self::network_support_available() && self::$disable_internet_support == false;
	}
	public static function network_support_available()
	{
		return self::$disable_network_support == false;
	}
	public static function http_get_contents($url, $override_proxy = false, $override_proxy_port = false, $http_timeout = -1)
	{
		if(!pts_network::network_support_available())
		{
			return false;
		}

		$stream_context = pts_network::stream_context_create(null, $override_proxy, $override_proxy_port, $http_timeout);
		$contents = pts_file_io::file_get_contents($url, 0, $stream_context);

		return $contents;
	}
	public static function http_upload_via_post($url, $to_post_data, $supports_proxy = true)
	{
		if(!pts_network::network_support_available())
		{
			return false;
		}

		$upload_data = http_build_query($to_post_data);
		$http_parameters = array('http' => array('method' => 'POST', 'content' => $upload_data));
		if($supports_proxy)
		{
			$stream_context = pts_network::stream_context_create($http_parameters);
		}
		else
		{
			$stream_context = pts_network::stream_context_create($http_parameters, false, -1, -1);
		}
		$opened_url = fopen($url, 'rb', false, $stream_context);
		$response = $opened_url ? stream_get_contents($opened_url) : false;
		// var_dump($url); var_dump($to_post_data);
		return $response;
	}
	public static function download_file($download, $to)
	{
		if(!pts_network::network_support_available())
		{
			return false;
		}

		if(strpos($download, '://') === false)
		{
			$download = 'http://' . $download;
		}
		else if(getenv('NO_HTTPS') != false)
		{
			// On some platforms like DragonFly 4.2 ran into problem of all HTTPS downloads failing
			$download = str_replace('https://', 'http://', $download);
		}

		if(function_exists('curl_init') && stripos(PTS_PHP_VERSION, 'hiphop') === false)
		{
			// XXX: Facebook HipHop HHVM currently seems to have problems with PHP CURL
			$return_state = pts_network::curl_download($download, $to);
		}
		else
		{
			$return_state = pts_network::stream_download($download, $to);
		}

		//echo '\nPHP CURL must either be installed or you must adjust your PHP settings file to support opening FTP/HTTP streams.\n';
		//return false;

		if($return_state == true)
		{
			pts_client::$display->test_install_progress_completed();
		}
	}
	public static function curl_download($download, $download_to, $download_port_number = false)
	{
		if(!function_exists('curl_init'))
		{
			return false;
		}

		// XXX: with curl_multi_init we could do multiple downloads at once...
		$cr = curl_init();
		$fh = fopen($download_to, 'w');

		curl_setopt($cr, CURLOPT_FILE, $fh);
		curl_setopt($cr, CURLOPT_URL, $download);
		curl_setopt($cr, CURLOPT_HEADER, false);
		curl_setopt($cr, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($cr, CURLOPT_CONNECTTIMEOUT, self::$network_timeout);
		curl_setopt($cr, CURLOPT_CAPATH, PTS_CORE_STATIC_PATH . 'certificates/');
		curl_setopt($cr, CURLOPT_BUFFERSIZE, 64000);
		curl_setopt($cr, CURLOPT_USERAGENT, pts_core::codename(true));

		if($download_port_number)
		{
			curl_setopt($ch, CURLOPT_PORT, $port);
		}

		if(stripos($download, 'sourceforge') === false)
		{
			// Setting the referer causes problems for SourceForge downloads
			curl_setopt($cr, CURLOPT_REFERER, 'http://www.phoronix-test-suite.com/');
		}

		if(strpos($download, 'https://openbenchmarking.org/') !== false)
		{
			curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($cr, CURLOPT_CAINFO, PTS_CORE_STATIC_PATH . 'certificates/openbenchmarking-server.pem');
		}
		else if(strpos($download, 'https://www.phoromatic.com/') !== false)
		{
			curl_setopt($cr, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($cr, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($cr, CURLOPT_CAINFO, PTS_CORE_STATIC_PATH . 'certificates/phoromatic-com.pem');
		}

		if(defined('CURLOPT_PROGRESSFUNCTION'))
		{
			// CURLOPT_PROGRESSFUNCTION only seems to work with PHP 5.3+, but is not working with HipHop HHVM ~2.0.1
			curl_setopt($cr, CURLOPT_NOPROGRESS, false);
			curl_setopt($cr, CURLOPT_PROGRESSFUNCTION, array('pts_network', 'curl_status_callback'));
		}

		if(self::$network_proxy)
		{
			curl_setopt($cr, CURLOPT_PROXY, self::$network_proxy['proxy']);
		}

		curl_exec($cr);
		curl_close($cr);
		fclose($fh);

		return true;
	}
	public static function stream_download($download, $download_to, $stream_context_parameters = null, $callback_function = array('pts_network', 'stream_status_callback'))
	{
		$stream_context = pts_network::stream_context_create($stream_context_parameters);

		if(function_exists('stream_context_set_params'))
		{
			// HHVM 2.1 doesn't have stream_context_set_params()
			stream_context_set_params($stream_context, array('notification' => $callback_function));
		}

		/*
		if(strpos($download, 'https://openbenchmarking.org/') !== false)
		{
			stream_context_set_option($stream_context, 'ssl', 'local_cert', PTS_CORE_STATIC_PATH . 'certificates/openbenchmarking-server.pem');
		}
		else if(strpos($download, 'https://www.phoromatic.com/') !== false)
		{
			stream_context_set_option($stream_context, 'ssl', 'local_cert', PTS_CORE_STATIC_PATH . 'certificates/phoromatic-com.pem');
		}
		*/

		$file_pointer = @fopen($download, 'r', false, $stream_context);

		if(is_resource($file_pointer) && file_put_contents($download_to, $file_pointer))
		{
			return true;
		}

		return false;
	}
	public static function stream_context_create($parameters = null, $proxy_address = false, $proxy_port = false, $http_timeout = -1)
	{
		if(!is_array($parameters))
		{
			$parameters = array();
		}

		if($proxy_address == false && $proxy_port == false && self::$network_proxy)
		{
			$proxy_address = self::$network_proxy['address'];
			$proxy_port = self::$network_proxy['port'];
		}

		if($proxy_address != false && $proxy_port != false && is_numeric($proxy_port) && $proxy_port > 1)
		{
			$parameters['http']['proxy'] = 'tcp://' . $proxy_address . ':' . $proxy_port;
			$parameters['http']['request_fulluri'] = true;
		}

		if(is_numeric($http_timeout) && $http_timeout > 1)
		{
			$parameters['http']['timeout'] = $http_timeout;
		}
		else
		{
			$parameters['http']['timeout'] = self::$network_timeout;
		}

		$parameters['http']['user_agent'] = pts_core::codename(true);
		$parameters['http']['header'] = "Content-Type: application/x-www-form-urlencoded\r\n";

		$stream_context = stream_context_create($parameters);

		return $stream_context;
	}

	//
	// Callback Functions
	//

	public static function stream_status_callback($notification_code, $arg1, $message, $message_code, $downloaded, $download_size)
	{
		static $filesize = 0;
		static $last_float = -1;

		switch($notification_code)
		{
			case STREAM_NOTIFY_FILE_SIZE_IS:
				$filesize = $download_size;
				break;
			case STREAM_NOTIFY_PROGRESS:
				$downloaded_float = $filesize == 0 ? 0 : $downloaded / $filesize;

				if(abs($downloaded_float - $last_float) < 0.01)
				{
					return;
				}

				pts_client::$display->test_install_progress_update($downloaded_float);
				$last_float = $downloaded_float;
				break;
		}
	}
	private static function curl_status_callback($download_size, $downloaded)
	{
		static $last_float = -1;
		$downloaded_float = $download_size == 0 ? 0 : $downloaded / $download_size;

		if(abs($downloaded_float - $last_float) < 0.01)
		{
			return;
		}

		pts_client::$display->test_install_progress_update($downloaded_float);
		$last_float = $downloaded_float;
	}
	public static function client_startup()
	{
		if(($proxy_address = pts_config::read_user_config('PhoronixTestSuite/Options/Networking/ProxyAddress', false)) && ($proxy_port = pts_config::read_user_config('PhoronixTestSuite/Options/Networking/ProxyPort', false)))
		{
			// Don't need http:// in address and some people mistakenly do it
			// e.g. https://www.phoronix.com/forums/forum/phoronix/phoronix-test-suite/905211-problem-network-support-is-needed-to-obtain-package
			$proxy_address = str_replace(array('http://', 'https://'), '', $proxy_address);

			self::$network_proxy['proxy'] = $proxy_address . ':' . $proxy_port;
			self::$network_proxy['address'] = $proxy_address;
			self::$network_proxy['port'] = $proxy_port;
		}
		else if(($env_proxy = getenv('http_proxy')) != false && count($env_proxy = pts_strings::colon_explode($env_proxy)) == 2)
		{
			self::$network_proxy['proxy'] = $env_proxy[0] . ':' . $env_proxy[1];
			self::$network_proxy['address'] = $env_proxy[0];
			self::$network_proxy['port'] = $env_proxy[1];
		}

		self::$network_timeout = pts_config::read_user_config('PhoronixTestSuite/Options/Networking/Timeout', 20);

		if(ini_get('allow_url_fopen') == 'Off')
		{
			if(!defined('PHOROMATIC_SERVER'))
			{
				echo PHP_EOL . 'The allow_url_fopen option in your PHP configuration must be enabled for network support.' . PHP_EOL . PHP_EOL;
			}
			self::$disable_network_support = true;
		}
		else if(pts_config::read_bool_config('PhoronixTestSuite/Options/Networking/NoInternetCommunication', 'FALSE'))
		{
			if(!defined('PHOROMATIC_SERVER'))
			{
				echo PHP_EOL . 'Internet Communication Is Disabled Per Your User Configuration.' . PHP_EOL . PHP_EOL;
			}
			self::$disable_internet_support = true;
		}
		else if(pts_config::read_bool_config('PhoronixTestSuite/Options/Networking/NoNetworkCommunication', 'FALSE'))
		{
			if(!defined('PHOROMATIC_SERVER'))
			{
				echo PHP_EOL . 'Network Communication Is Disabled Per Your User Configuration.' . PHP_EOL . PHP_EOL;
			}
			self::$disable_network_support = true;
		}
		else if(!PTS_IS_WEB_CLIENT)
		{
			$server_response = pts_network::http_get_contents('http://openbenchmarking.org/PTS', false, false);

			if($server_response != 'PTS')
			{
				// Failed to connect to PTS server

				// As a last resort, see if it can resolve IP to Google.com as a test for Internet connectivity...
				// i.e. in case Phoronix server is down or some other issue, so just see if Google will resolve
				// If google.com fails to resolve, it will simply return the original string
				if(gethostbyname('google.com') == 'google.com')
				{
					echo PHP_EOL;

					if(PTS_IS_DAEMONIZED_SERVER_PROCESS)
					{
						// Wait some seconds in case network is still coming up
						foreach(array(20, 40) as $time_to_wait)
						{
							sleep($time_to_wait);
							$server_response = pts_network::http_get_contents('http://openbenchmarking.org/PTS', false, false);
							if($server_response != 'PTS' && gethostbyname('google.com') == 'google.com')
							{
								trigger_error('No Internet Connectivity After Wait', E_USER_WARNING);
								self::$disable_internet_support = true;
							}
							else
							{
								self::$disable_internet_support = false;
								break;
							}
						}
					}
					else
					{
						trigger_error('No Internet Connectivity', E_USER_WARNING);
						self::$disable_internet_support = true;
					}
				}
			}
		}

		if(pts_network::network_support_available() == false && ini_get('file_uploads') == 'Off')
		{
			echo PHP_EOL . 'The file_uploads option in your PHP configuration must be enabled for network support.' . PHP_EOL . PHP_EOL;
		}
	}
	public static function get_local_ip()
	{
		$local_ip = false;

		if(($ifconfig = pts_client::executable_in_path('ifconfig')))
		{
			$ifconfig = shell_exec($ifconfig . ' 2>&1');
			$offset = 0;
			while(($ipv4_pos = strpos($ifconfig, 'inet addr:', $offset)) !== false)
			{
				$ipv4 = substr($ifconfig, $ipv4_pos + strlen('inet addr:'));
				$ipv4 = substr($ipv4, 0, strpos($ipv4, ' '));
				$local_ip = $ipv4;

				if($local_ip != '127.0.0.1' && $local_ip != null)
				{
					break;
				}
				$offset = $ipv4_pos + 1;
			}
			if($local_ip == null)
			{
				while(($ipv4_pos = strpos($ifconfig, 'inet ', $offset)) !== false)
				{
					$ipv4 = substr($ifconfig, $ipv4_pos + strlen('inet '));
					$ipv4 = substr($ipv4, 0, strpos($ipv4, ' '));
					$local_ip = $ipv4;

					if($local_ip != '127.0.0.1' && $local_ip != null)
					{
						break;
					}
					$offset = $ipv4_pos + 1;
				}
			}
		}

		return $local_ip;
	}
	public static function get_network_mac()
	{
		$mac = false;

		foreach(pts_file_io::glob('/sys/class/net/*/operstate') as $net_device_state)
		{
			if(pts_file_io::file_get_contents($net_device_state) == 'up')
			{
				$addr = dirname($net_device_state) . '/address';

				if(is_file($addr))
				{
					$mac = pts_file_io::file_get_contents($addr);
					break;
				}
			}
		}

		if(empty($mac) && ($ifconfig = pts_client::executable_in_path('ifconfig')))
		{
			$ifconfig = shell_exec($ifconfig . ' 2>&1');
			$offset = 0;
			while(($hwaddr_pos = strpos($ifconfig, 'HWaddr ', $offset)) !== false || ($hwaddr_pos = strpos($ifconfig, 'ether ', $offset)) !== false)
			{
				$hw_addr = substr($ifconfig, $hwaddr_pos);
				$hw_addr = substr($hw_addr, (strpos($hw_addr, ' ') + 1));
				$hw_addr = substr($hw_addr, 0, strpos($hw_addr, ' '));
				$mac = $hw_addr;

				if($mac != null)
				{
					break;
				}
				$offset = $hwaddr_pos + 1;
			}
		}

		return $mac;
	}
	public static function get_network_wol()
	{
		static $wol_support = null;

		if($wol_support === null)
		{
			$wol_support = array();
			if(is_dir('/sys/class/net') && pts_client::executable_in_path('ethtool'))
			{
				foreach(pts_file_io::glob('/sys/class/net/*') as $net_device)
				{
					if(is_readable($net_device . '/operstate') && trim(file_get_contents($net_device . '/operstate')) != 'up')
					{
						continue;
					}

					$net_name = basename($net_device);
					$ethtool_output = shell_exec('ethtool ' . $net_name . ' 2>&1');
					if(($x = stripos($ethtool_output, 'Supports Wake-on: ')) !== false)
					{
						$ethtool_output = substr($ethtool_output, $x + strlen('Supports Wake-on: '));
						$ethtool_output = trim(substr($ethtool_output, 0, strpos($ethtool_output, PHP_EOL)));
						$wol_support[$net_name] = $net_name . ': ' . $ethtool_output;
					}

				}
			}
		}

		return $wol_support;
	}
	public static function send_wol_packet($ip_address, $mac_address)
	{
		$hwaddr = null;
		foreach(explode(':', $mac_address) as $o)
		{
			$hwaddr .= chr(hexdec($o));
		}

		$packet = null;
		for($i = 1; $i <= 6; $i++)
		{
			$packet .= chr(255);
		}

		for($i = 1; $i <= 16; $i++)
		{
			$packet .= $hwaddr;
		}

		$sock = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
		if($sock)
		{
			$options = socket_set_option($sock, 1, 6, true);

			if($options >= 0)
			{
				$sendto = socket_sendto($sock, $packet, strlen($packet), 0, $ip_address, 7);
				socket_close($sock);
				return $sendto;
			}
		}

		return false;
	}
	public static function find_zeroconf_phoromatic_servers($find_multiple = false)
	{
		if(!pts_network::network_support_available())
		{
			return null;
		}

		$hosts = $find_multiple ? array() : null;

		if(PTS_IS_CLIENT && pts_client::executable_in_path('avahi-browse'))
		{
			$avahi_browse = explode(PHP_EOL, shell_exec('avahi-browse -p -r -t _http._tcp 2>&1'));
			foreach(array_reverse($avahi_browse) as $avahi_line)
			{
				if(strrpos($avahi_line, 'phoromatic-server') !== false)
				{
					$avahi_line = explode(';', $avahi_line);

					if(isset($avahi_line[8]) && ip2long($avahi_line[7]) !== false && is_numeric($avahi_line[8]))
					{
						$server_ip = $avahi_line[7];
						$server_port = $avahi_line[8];
						//echo $server_ip . ':' . $server_port;

						if($find_multiple)
						{
							$hosts[] = array($server_ip, $server_port);
						}
						else
						{
							$hosts = array($server_ip, $server_port);
							break;
						}
					}
				}
			}
		}

		return $hosts;
	}
	public static function mac_to_ip($mac)
	{
		$ip = false;

		if(is_readable('/proc/net/arp') && function_exists('preg_replace'))
		{
			$arp = file_get_contents('/proc/net/arp');

			if(($x = strpos($arp, $mac)) !== false)
			{
				$li = substr($arp, strrpos($arp, PHP_EOL, (0 - strlen($arp) + $x)) + 1);
				$li = substr($li, 0, strpos($li, PHP_EOL));
				$li = explode(' ', preg_replace('!\s+!', ' ', $li));

				if(isset($li[0]) && ip2long($li[0]) !== false)
				{
					$ip = $li[0];
				}
			}
		}

		return $ip;
	}
}

?>
