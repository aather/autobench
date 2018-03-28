<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2014 - 2015, Phoronix Media
	Copyright (C) 2014 - 2015, Michael Larabel

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


class phoromatic_admin implements pts_webui_interface
{
	public static function page_title()
	{
		return 'Phoromatic Root Administrator';
	}
	public static function page_header()
	{
		return null;
	}
	public static function preload($PAGE)
	{
		return true;
	}
	public static function render_page_process($PATH)
	{
		if($_SESSION['AdminLevel'] != -40)
		{
			header('Location: /?main');
		}
		$main = null;
		if(isset($_POST['disable_user']))
		{
			$stmt = phoromatic_server::$db->prepare('UPDATE phoromatic_users SET AdminLevel = (AdminLevel * -1) WHERE UserName = :user_name');
			$stmt->bindValue(':user_name', $_POST['disable_user']);
			$result = $stmt->execute();
			$main .= '<h2>Disabled Account: ' . $_POST['disable_user'] . '</h2>';
		}
		else if(isset($_POST['register_username']) && isset($_POST['register_password']) && isset($_POST['register_password_confirm']) && isset($_POST['register_email']))
		{
			$new_account = create_new_phoromatic_account($_POST['register_username'], $_POST['register_password'], $_POST['register_password_confirm'], $_POST['register_email'], (isset($_POST['seed_accountid']) ? $_POST['seed_accountid'] : null));
		}

		$main .= '<h1>Phoromatic Server Administration</h1>';

		$main .= '<hr /><h2>Server Information</h2>';
		$main .= '<p><strong>HTTP Server Port:</strong> ' . getenv('PTS_WEB_PORT') . '<br /><strong>WebSocket Server Port:</strong> ' . getenv('PTS_WEBSOCKET_PORT') . '<br /><strong>Phoromatic Server Path:</strong> ' . phoromatic_server::phoromatic_path() . '<br /><strong>Configuration File:</strong>: ' . pts_config::get_config_file_location() . '</p>';

		$main .= '<hr /><h2>Statistics</h2>';
		$stmt = phoromatic_server::$db->prepare('SELECT COUNT(Title) AS SystemCount FROM phoromatic_systems WHERE State >= 0');
		$result = $stmt->execute();
		$row = $result->fetchArray();
		$stats['Total System Count'] = $row['SystemCount'];
		$stmt = phoromatic_server::$db->prepare('SELECT COUNT(Title) AS ScheduleCount FROM phoromatic_schedules WHERE State >= 1');
		$result = $stmt->execute();
		$row = $result->fetchArray();
		$stats['Total Schedule Count'] = $row['ScheduleCount'];
		$stmt = phoromatic_server::$db->prepare('SELECT COUNT(UploadID) AS ResultCount FROM phoromatic_results');
		$result = $stmt->execute();
		$row = $result->fetchArray();
		$stats['Total Result Count'] = $row['ResultCount'];
		$stmt = phoromatic_server::$db->prepare('SELECT COUNT(ActivityTime) AS ActivityCount FROM phoromatic_activity_stream');
		$stmt->bindValue(':today_date', date('Y-m-d') . '%');
		$result = $stmt->execute();
		$row = $result->fetchArray();
		$stats['Total Activity Count'] = $row['ActivityCount'];

		$main .= '<p>';
		foreach($stats as $what => $c)
			$main .= '<strong>' . $what . ':</strong> ' . $c . '<br />';


		$main .= '<hr /><h2>Account Topology</h2>';
		$stmt = phoromatic_server::$db->prepare('SELECT * FROM phoromatic_users ORDER BY AccountID,AdminLevel ASC');
		$result = $stmt->execute();

		$plevel = -1;
		$user_list = array();
		while($row = $result->fetchArray())
		{
			switch($row['AdminLevel'])
			{
				case 1:
					$level = 'Group Administrator';
					$offset = null;
					break;
				case 2:
					$level = 'Administrator';
					$offset = str_repeat('-', 10);
					break;
				case 3:
					$level = 'Power User';
					$offset = str_repeat('-', 20);
					break;
				case 10:
					$level = 'Viewer';
					$offset = str_repeat('-', 30);
					break;
				default:
					if($row['AdminLevel'] < 1)
						$level = 'Disabled';
					else
						$level = 'Unknown';

					$offset = null;
					break;
			}

			if($row['AdminLevel'] == 1)
			{
				if($plevel != -1)
					$main .= '</p>';
				$main .= '<p>';
			}

			$main .= $offset . ' <strong>' . $row['UserName'] . '</strong> (<em>' . $level . '</em>) <strong>Created On:</strong> ' . phoromatic_user_friendly_timedate($row['CreatedOn']) . ' <strong>Last Log-In:</strong> ' . ($row['LastLogin'] != null ? phoromatic_user_friendly_timedate($row['LastLogin']) : 'N/A') . ($row['AdminLevel'] == 1 ? ' [<strong>ACCOUNT ID:</strong> ' . $row['AccountID'] . ']' : null) . '<br />';
			$plevel = $row['AdminLevel'];
			$user_list[$row['UserName']] = $row['AdminLevel'];
		}
		if($plevel != -1)
			$main .= '</p>';

		$main .= '<hr /><h2>Disable Account</h2>';
		$main .= '<form action="' . $_SERVER['REQUEST_URI'] . '" name="disable_user" id="disable_user" method="post"><p><select name="disable_user">';
		foreach($user_list as $user_name => $user_level)
		{
			if($user_level > 0)
			{
				$main .= '<option value="' . $user_name . '">' . $user_name . '</option>';
			}
		}
		$main .= '</select></p><p><input name="submit" value="Disable User" type="submit" /></p></form>';
		$main .= '<hr /><h2>Create New Account Group</h2>';
		$main .= '<form name="register_form" id="register_form" action="?admin" method="post" onsubmit="return phoromatic_initial_registration(this);">
		<h3>Username</h3>
		<p><input type="text" name="register_username" /> <sup>1</sup></p>
		<h3>Password</h3>
		<p><input type="password" name="register_password" /> <sup>2</sup></p>
		<h3>Confirm Password</h3>
		<p><input type="password" name="register_password_confirm" /></p>
		<h3>Email</h3>
		<p><input type="text" name="register_email" /> <sup>3</sup></p>
		<h3>Account ID</h3>
		<p><input type="text" name="seed_accountid" /> <sup>4</sup></p>
		<p><input type="submit" value="Create Account" /></p>
		</form>
		<p style="font-size: 11px;"><sup>1</sup> Usernames shall be at least four characters long, not contain any spaces, and only be composed of normal ASCII characters.<br />
		<sup>2</sup> Passwords shall be at least six characters long.<br />
		<sup>3</sup> A valid email address is required for notifications, password reset, and other verification purposes.<br />
		<sup>4</sup> The account ID field is optional and is used to pre-seed the account identifier for advanced purposes. The field must be six characters. Leave this field blank if you are unsure.<br />
						</p>';


		//
		$server_log = explode(PHP_EOL, file_get_contents(getenv('PTS_PHOROMATIC_LOG_LOCATION')));
		foreach($server_log as $i => $line_item)
		{
			if(strpos($line_item, '[200]') !== false || strpos($line_item, '[302]') !== false)
			{
				unset($server_log[$i]);
			}
		}
		$server_log = implode(PHP_EOL, $server_log);

		$main .= '<hr /><h2>Phoromatic Server Log</h2>';
		$main .= '<p><textarea style="width: 80%; height: 400px;">' . $server_log  . '</textarea></p>';

		echo phoromatic_webui_header_logged_in();
		echo phoromatic_webui_main($main, phoromatic_webui_right_panel_logged_in());
		echo phoromatic_webui_footer();
	}
}

?>
