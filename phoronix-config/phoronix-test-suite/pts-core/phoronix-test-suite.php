<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2017, Phoronix Media
	Copyright (C) 2008 - 2017, Michael Larabel
	phoronix-test-suite.php: The main code for initalizing the Phoronix Test Suite

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

setlocale(LC_NUMERIC, 'C');
define('PTS_PATH', dirname(dirname(__FILE__)) . '/');

// PTS_MODE types
// CLIENT = Standard Phoronix Test Suite Client
// LIB = Only load select PTS files
// SILENT = Load all normal pts-core files, but don't run client code
if(!defined('PTS_MODE'))
{
	define('PTS_MODE', in_array(($m = getenv('PTS_MODE')), array('CLIENT', 'LIB', 'WEB_CLIENT', 'SILENT')) ? $m : 'CLIENT');
}

// Any PHP default memory limit should be fine for PTS, until you run image quality comparison tests that begins to consume memory
if(stripos(phpversion(), 'hhvm') === false)
{
	ini_set('memory_limit', '256M');
}

if(getenv('PTS_MODE') == 'CLIENT' && ini_get('open_basedir') != false)
{
	$passes = true;
	$open_basedir = ini_get('open_basedir');

	if($open_basedir != false)
	{
		$is_in_allowed_dir = false;
		foreach(explode(':', $open_basedir) as $allowed_dir)
		{
			if(strpos(PTS_PATH, $allowed_dir) === 0)
			{
				$is_in_allowed_dir = true;
				break;
			}
		}

		if($is_in_allowed_dir == false)
		{
			$passes = false;
		}
	}


	if($passes == false)
	{
		echo PHP_EOL . 'ERROR: The php.ini configuration open_basedir directive is preventing ' . PTS_PATH . ' from loading.' . PHP_EOL;
		return false;
	}
	else
	{
		echo PHP_EOL . 'NOTICE: The php.ini configuration is using the "open_basedir" directive, which may prevent some parts of the Phoronix Test Suite from working. See the Phoronix Test Suite documentation for more details and to disable this setting.' . PHP_EOL;
		sleep(1);
	}
}

require(PTS_PATH . 'pts-core/pts-core.php');

if(!PTS_IS_CLIENT)
{
	// pts-core is acting as a library, return now since no need to run client code
	return;
}

// Default to C locale
setlocale(LC_ALL, 'C');

// Needed for shutdown functions
// declare(ticks = 1);

$sent_command = strtolower(str_replace('-', '_', (isset($argv[1]) ? $argv[1] : null)));
$quick_start_options = array('dump_possible_options');
pts_define('QUICK_START', in_array($sent_command, $quick_start_options));

if(QUICK_START == false)
{
	pts_client::program_requirement_checks(true);
}
pts_client::init(); // Initalize the Phoronix Test Suite (pts-core) client
$pass_args = array();

if(is_file(PTS_PATH . 'pts-core/commands/' . $sent_command . '.php') == false)
{
	$replaced = false;

	if(pts_module::valid_run_command($sent_command))
	{
		$replaced = true;
	}
	else if(isset($argv[1]) && strpos($argv[1], '.openbenchmarking') !== false && is_readable($argv[1]))
	{
		$sent_command = 'openbenchmarking_launcher';
		$argv[2] = $argv[1];
		$argc = 3;
		$replaced = true;
	}
	else
	{
		$aliases = pts_storage_object::read_from_file(PTS_TEMP_STORAGE, 'command_alias_list');
		if($aliases == null)
		{
			$aliases = pts_documentation::client_commands_aliases();
		}

		if(isset($aliases[$sent_command]))
		{
			$sent_command = $aliases[$sent_command];
			$replaced = true;
		}
	}

	if($replaced == false)
	{
		// Show help command, since there are no valid commands
		$sent_command = 'help';
	}
}


pts_define('PTS_USER_LOCK', function_exists('posix_getpid') ? PTS_USER_PATH . 'run-lock-' . posix_getpid() : tempnam(PTS_USER_PATH, 'run-lock-'));

if(QUICK_START == false)
{
	// Cleanup old / expired runlocks XXX expire if this can eventually be removed
	foreach(pts_file_io::glob(PTS_USER_PATH . 'run-lock-*') as $possible_run_lock)
	{
		if(!pts_client::is_locked($possible_run_lock))
		{
			pts_file_io::unlink($possible_run_lock);
		}
	}
	if(pts_client::create_lock(PTS_USER_LOCK) == false)
	{
		//trigger_error('It appears that the Phoronix Test Suite is already running.' . PHP_EOL . 'For proper results, only run one instance at a time.', E_USER_WARNING);
	}

	register_shutdown_function(array('pts_client', 'process_shutdown_tasks'));
	//pcntl_signal(SIGTERM, array('pts_client', 'exit_client'));

	if(pts_client::read_env('PTS_IGNORE_MODULES') == false)
	{
		pts_client::module_framework_init(); // Initialize the PTS module system
	}
}

// Read passed arguments
for($i = 2; $i < $argc && isset($argv[$i]); $i++)
{
	$pass_args[] = $argv[$i];
}

if(QUICK_START == false)
{
	pts_client::user_agreement_check($sent_command);

	// OpenBenchmarking.org
	pts_openbenchmarking::refresh_repository_lists();
}

pts_client::execute_command($sent_command, $pass_args); // Run command

if(QUICK_START == false)
{
	pts_client::release_lock(PTS_USER_LOCK);
}

?>
