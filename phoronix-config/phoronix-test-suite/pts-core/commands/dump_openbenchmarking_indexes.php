<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2011 - 2013, Phoronix Media
	Copyright (C) 2011 - 2013, Michael Larabel

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

class dump_openbenchmarking_indexes implements pts_option_interface
{
	public static function run($r)
	{
		echo PHP_EOL . 'OpenBenchmarking.org Repositories:' . PHP_EOL . PHP_EOL;

		foreach(pts_openbenchmarking::linked_repositories() as $repo)
		{
			if($repo == 'local')
			{
				// Skip local since it's a fake repository
				continue;
			}

			$repo_index = pts_openbenchmarking::read_repository_index($repo);
			$generated_time = date('F d H:i', $repo_index['main']['generated']);

			$tab = '    ';
			foreach(array('tests', 'suites') as $t)
			{
				echo PHP_EOL . str_repeat('=', 40) . PHP_EOL . strtoupper($repo . ' ' . $t) . PHP_EOL . 'Generated: ' . $generated_time . PHP_EOL . str_repeat('=', 40) . PHP_EOL . PHP_EOL;
				foreach($repo_index[$t] as $identifier => $test)
				{
					echo 'Identifier: ' . $identifier . PHP_EOL;
					foreach($test as $i => $j)
					{
						echo sprintf('%-22ls', $i) . ': ';

						if(is_array($j))
						{
							echo implode(', ', $j);
						}
						else
						{
							echo $j;
						}
						echo PHP_EOL;
					}
					echo PHP_EOL;
				}
			}
		}
	}
}

?>
