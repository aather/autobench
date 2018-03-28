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

class pts_result_file
{
	protected $save_identifier = null;
	protected $result_objects = null;
	protected $extra_attributes = null;
	protected $is_multi_way_inverted = false;
	protected $file_location = false;

	private $title = null;
	private $description = null;
	private $notes = null;
	private $internal_tags = null;
	private $reference_id = null;
	private $preset_environment_variables = null;
	private $systems = null;
	private $is_tracker = -1;

	public function __construct($result_file, $read_only_result_objects = false)
	{
		$this->save_identifier = $result_file;
		$this->extra_attributes = array();
		$this->systems = array();
		$this->result_objects = array();

		if($result_file == null)
		{
			return;
		}
		else if(is_file($result_file))
		{
			$this->file_location = $result_file;
			$result_file = file_get_contents($result_file);
		}
		else if(!isset($result_file[1024]) && defined('PTS_SAVE_RESULTS_PATH') && is_file(PTS_SAVE_RESULTS_PATH . $result_file . '/composite.xml'))
		{
			$this->file_location = PTS_SAVE_RESULTS_PATH . $result_file . '/composite.xml';
			$result_file = file_get_contents($this->file_location);
		}

		$xml = simplexml_load_string($result_file, 'SimpleXMLElement', LIBXML_COMPACT | LIBXML_PARSEHUGE);
		if(isset($xml->Generated))
		{
			$this->title = self::clean_input($xml->Generated->Title);
			$this->description = self::clean_input($xml->Generated->Description);
			$this->notes = self::clean_input($xml->Generated->Notes);
			$this->internal_tags = self::clean_input($xml->Generated->InternalTags);
			$this->reference_id = self::clean_input($xml->Generated->ReferenceID);
			$this->preset_environment_variables = self::clean_input($xml->Generated->PreSetEnvironmentVariables);
		}

		if(isset($xml->System))
		{
			foreach($xml->System as $s)
			{
				$system = new pts_result_file_system(self::clean_input($s->Identifier->__toString()), self::clean_input($s->Hardware->__toString()), self::clean_input($s->Software->__toString()), json_decode(self::clean_input($s->JSON), true), self::clean_input($s->User->__toString()), self::clean_input($s->Notes->__toString()), self::clean_input($s->TimeStamp->__toString()), self::clean_input($s->ClientVersion->__toString()));
				$this->systems[] = $system;
			}
		}

		if(isset($xml->Result))
		{
			foreach($xml->Result as $result)
			{
				$test_profile = new pts_test_profile(($result->Identifier != null ? $result->Identifier->__toString() : null), null, !$read_only_result_objects);
				$test_profile->set_test_title($result->Title->__toString());
				$test_profile->set_version($result->AppVersion->__toString());
				$test_profile->set_result_scale($result->Scale->__toString());
				$test_profile->set_result_proportion($result->Proportion->__toString());
				$test_profile->set_display_format($result->DisplayFormat->__toString());

				$test_result = new pts_test_result($test_profile);
				$test_result->set_used_arguments_description($result->Description->__toString());
				$test_result->set_used_arguments($result->Arguments->__toString());

				$result_buffer = new pts_test_result_buffer();
				foreach($result->Data->Entry as $entry)
				{
					$result_buffer->add_test_result($entry->Identifier->__toString(), $entry->Value->__toString(), $entry->RawString->__toString(), (isset($entry->JSON) ? $entry->JSON->__toString() : null));
				}
				$test_result->set_test_result_buffer($result_buffer);
				$this->result_objects[$test_result->get_comparison_hash(true, false)] = $test_result;
			}
		}

		unset($xml);
	}
	public function get_file_location()
	{
		return $this->file_location;
	}
	public function validate()
	{
		$dom = new DOMDocument();
		$dom->loadXML($this->get_xml());
		return $dom->schemaValidate(PTS_OPENBENCHMARKING_PATH . 'schemas/result-file.xsd');
	}
	public function __toString()
	{
		return $this->get_identifier();
	}
	protected static function clean_input($value)
	{
		if(is_array($value))
		{
			return array_map(array($this, 'clean_input'), $value);
		}
		else
		{
			return strip_tags($value);
		}
	}
	public static function is_test_result_file($identifier)
	{
		return is_file(PTS_SAVE_RESULTS_PATH . $identifier . '/composite.xml');
	}
	public function default_result_folder_path()
	{
		return PTS_SAVE_RESULTS_PATH . $this->save_identifier . '/';
	}
	public function get_identifier()
	{
		return $this->save_identifier;
	}
	public function read_extra_attribute($key)
	{
		return isset($this->extra_attributes[$key]) ? $this->extra_attributes[$key] : false;
	}
	public function set_extra_attribute($key, $value)
	{
		$this->extra_attributes[$key] = $value;
	}
	public function add_system($system)
	{
		if(!in_array($system, $this->systems))
		{
			$this->systems[] = $system;
		}
	}
	public function get_systems()
	{
		return $this->systems;
	}
	public function get_system_hardware()
	{
		// XXX this is deprecated
		$hw = array();
		foreach($this->get_systems() as $s)
		{
			$hw[] = $s->get_hardware();
		}
		return $hw;
	}
	public function get_system_software()
	{
		// XXX this is deprecated
		$sw = array();
		foreach($this->get_systems() as $s)
		{
			$sw[] = $s->get_software();
		}
		return $sw;
	}
	public function get_system_identifiers()
	{
		// XXX this is deprecated
		$ids = array();
		foreach($this->get_systems() as $s)
		{
			$ids[] = $s->get_identifier();
		}
		return $ids;
	}
	public function get_system_count()
	{
		// XXX this is deprecated
		return count($this->get_systems());
	}
	public function set_title($new_title)
	{
		if($new_title != null)
		{
			$this->title = $new_title;
		}
	}
	public function get_title()
	{
		return $this->title;
	}
	public function set_description($new_description)
	{
		if($new_description != null)
		{
			$this->description = $new_description;
		}
	}
	public function get_description()
	{
		return $this->description;
	}
	public function set_notes($notes)
	{
		if($notes != null)
		{
			$this->notes = $notes;
		}
	}
	public function get_notes()
	{
		return $this->notes;
	}
	public function set_internal_tags($tags)
	{
		if($tags != null)
		{
			$this->internal_tags = $tags;
		}
	}
	public function get_internal_tags()
	{
		return $this->internal_tags;
	}
	public function set_reference_id($new_reference_id)
	{
		if($new_reference_id != null)
		{
			$this->reference_id = $new_reference_id;
		}
	}
	public function get_reference_id()
	{
		return $this->reference_id;
	}
	public function set_preset_environment_variables($env)
	{
		if($env != null)
		{
			$this->preset_environment_variables = $env;
		}
	}
	public function get_preset_environment_variables()
	{
		return $this->preset_environment_variables;
	}
	public function get_test_count()
	{
		return count($this->get_result_objects());
	}
	public function has_matching_test_and_run_identifier(&$test_result, $run_identifier_to_check)
	{
		$found_match = false;
		$hash_to_check = $test_result->get_comparison_hash();

		foreach($this->get_result_objects() as $result_object)
		{
			if($hash_to_check == $result_object->get_comparison_hash())
			{
				if(in_array($run_identifier_to_check, $result_object->test_result_buffer->get_identifiers()))
				{
					$found_match = true;
				}
				break;
			}
		}

		return $found_match;
	}
	public function get_contained_tests_hash($raw_output = true)
	{
		$result_object_hashes = $this->get_result_object_hashes();
		sort($result_object_hashes);
		return sha1(implode(',', $result_object_hashes), $raw_output);
	}
	public function get_result_object_hashes()
	{
		$object_hashes = array();

		foreach($this->get_result_objects() as $result_object)
		{
			$object_hashes[] = $result_object->get_comparison_hash();
		}

		return $object_hashes;
	}
	public function is_results_tracker()
	{
		// If there are more than five results and the only changes in the system identifier names are numeric changes, assume it's a tracker
		// i.e. different dates or different versions of a package being tested
		if($this->is_tracker === -1)
		{
			$identifiers = $this->get_system_identifiers();

			if(isset($identifiers[5]))
			{
				// dirty SHA1 hash check
				$is_sha1_hash = strlen($identifiers[0]) == 40 && strpos($identifiers[0], ' ') === false;
				$has_sha1_shorthash = false;

				foreach($identifiers as $i => &$identifier)
				{
					$has_sha1_shorthash = ($i == 0 || $has_sha1_shorthash) && isset($identifier[7]) && pts_strings::string_only_contains(substr($identifier, -8), pts_strings::CHAR_NUMERIC | pts_strings::CHAR_LETTER) && strpos($identifier, ' ') === false;
					$identifier = pts_strings::remove_from_string($identifier, pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DASH | pts_strings::CHAR_DECIMAL);
				}

				$this->is_tracker = count(array_unique($identifiers)) <= 1 || $is_sha1_hash || $has_sha1_shorthash;

				if($this->is_tracker)
				{
					$hw = $this->get_system_hardware();

					if(isset($hw[1]) && count($hw) == count(array_unique($hw)))
					{
						// it can't be a results tracker if the hardware is always different
						$this->is_tracker = false;
					}
				}

				if($this->is_tracker == false)
				{
					// See if only numbers are changing between runs
					foreach($identifiers as $i => &$identifier)
					{
						if(($x = strpos($identifier, ': ')) !== false)
						{
							$identifier = substr($identifier, ($x + 2));
						}
						if($i > 0 && pts_strings::remove_from_string($identifier, pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DECIMAL) != pts_strings::remove_from_string($identifiers[($i - 1)], pts_strings::CHAR_NUMERIC | pts_strings::CHAR_DECIMAL))
						{
							return false;
						}
					}
					$this->is_tracker = true;
				}
			}
			else
			{
				// Definitely not a tracker as not over 5 results
				$this->is_tracker = false;
			}
		}

		return $this->is_tracker;
	}
	public function is_multi_way_comparison($identifiers = false, $extra_attributes = null)
	{
		if(isset($extra_attributes['force_tracking_line_graph']))
		{
			// Phoromatic result tracker
			$is_multi_way = true;
			$this->is_multi_way_inverted = true;
		}
		else
		{
			$hw = null; // XXX: this isn't used anymore at least for now on system hardware
			if($identifiers == false)
			{
				$identifiers = $this->get_system_identifiers();
			}
			$is_multi_way = count($identifiers) < 2 ? false : pts_render::multi_way_identifier_check($identifiers, $hw, $this);
			$this->is_multi_way_inverted = $is_multi_way && $is_multi_way[1];
		}

		return $is_multi_way;
	}
	public function invert_multi_way_invert()
	{
		$this->is_multi_way_inverted = !$this->is_multi_way_inverted;
	}
	public function is_multi_way_inverted()
	{
		return $this->is_multi_way_inverted;
	}
	public function get_contained_test_profiles()
	{
		$test_profiles = array();

		foreach($this->get_result_objects() as $object)
		{
			$test_profiles[] = $object->test_profile;
		}

		return $test_profiles;
	}
	public function override_result_objects($result_objects)
	{
		$this->result_objects = $result_objects;
	}
	public function get_result_objects($select_indexes = -1, $read_only_objects = false)
	{
		if($select_indexes != -1 && $select_indexes !== null)
		{
			$objects = array();

			if($select_indexes == 'ONLY_CHANGED_RESULTS')
			{
				foreach($this->result_objects as &$result)
				{
					// Only show results where the variation was greater than or equal to 1%
					if(abs($result->largest_result_variation(0.01)) >= 0.01)
					{
						$objects[] = $result;
					}
				}
			}
			else
			{
				foreach(pts_arrays::to_array($select_indexes) as $index)
				{
					if(isset($this->result_objects[$index]))
					{
						$objects[] = $this->result_objects[$index];
					}
				}
			}

			return $objects;
		}

		return $this->result_objects;
	}
	public function to_json()
	{
		$file = $this->get_xml();
		$file = str_replace(array("\n", "\r", "\t"), '', $file);
		$file = trim(str_replace('"', "'", $file));
		$simple_xml = simplexml_load_string($file);
		return json_encode($simple_xml);
	}
	public function rename_run($from, $to)
	{
		if($from == null)
		{
			if(count($this->systems) == 1)
			{
				foreach($this->systems as &$s)
				{
					$s->set_identifier($to);
				}
			}
		}
		else
		{
			foreach($this->systems as &$s)
			{
				if($s->get_identifier() == $from)
				{
					$s->set_identifier($to);
				}
			}
		}

		foreach($this->result_objects as &$result)
		{
			$result->test_result_buffer->rename($from, $to);
		}
	}
	public function reorder_runs($new_order)
	{
		foreach($new_order as $identifier)
		{
			foreach($this->systems as $i => $s)
			{
				if($s->get_identifier() == $identifier)
				{
					$c = $s;
					unset($this->systems[$i]);
					$this->systems[] = $c;
					break;
				}
			}
		}

		foreach($this->result_objects as &$result)
		{
			$result->test_result_buffer->reorder($new_order);
		}
	}
	public function remove_run($remove)
	{
		$remove = pts_arrays::to_array($remove);
		foreach($this->systems as $i => &$s)
		{
			if(in_array($s->get_identifier(), $remove))
			{
				unset($this->systems[$i]);
			}
		}

		foreach($this->result_objects as &$result)
		{
			$result->test_result_buffer->remove($remove);
		}
	}
	public function add_to_result_file(&$result_file)
	{
		foreach($result_file->get_systems() as $s)
		{
			if(!in_array($s, $this->systems))
			{
				$this->systems[] = $s;
			}
		}

		foreach($result_file->get_result_objects() as $result)
		{
			$this->add_result($result);
		}
	}
	public function add_result(&$result_object)
	{
		$ch = $result_object->get_comparison_hash(true, false);
		if(isset($this->result_objects[$ch]) && isset($this->result_objects[$ch]->test_result_buffer))
		{
			foreach($result_object->test_result_buffer->get_buffer_items() as $bi)
			{
				if($bi->get_result_value() === null)
				{
					continue;
				}

				$this->result_objects[$ch]->test_result_buffer->add_buffer_item($bi);
			}
		}
		else
		{
			$this->result_objects[$ch] = $result_object;
		}
	}
	public function get_xml($to = null, $force_nice_formatting = false)
	{
		$xml_writer = new nye_XmlWriter(null, $force_nice_formatting);
		$xml_writer->addXmlNode('PhoronixTestSuite/Generated/Title', $this->get_title());
		$xml_writer->addXmlNode('PhoronixTestSuite/Generated/LastModified', date('Y-m-d H:i:s'));
		$xml_writer->addXmlNode('PhoronixTestSuite/Generated/TestClient', pts_core::program_title(true));
		$xml_writer->addXmlNode('PhoronixTestSuite/Generated/Description', $this->get_description());
		$xml_writer->addXmlNodeWNE('PhoronixTestSuite/Generated/Notes', $this->get_notes());
		$xml_writer->addXmlNodeWNE('PhoronixTestSuite/Generated/InternalTags', $this->get_internal_tags());
		$xml_writer->addXmlNodeWNE('PhoronixTestSuite/Generated/ReferenceID', $this->get_reference_id());
		$xml_writer->addXmlNodeWNE('PhoronixTestSuite/Generated/PreSetEnvironmentVariables', $this->get_preset_environment_variables());

		// Write the system hardware/software information
		foreach($this->get_systems() as $s)
		{
			$xml_writer->addXmlNode('PhoronixTestSuite/System/Identifier', $s->get_identifier());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/Hardware', $s->get_hardware());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/Software', $s->get_software());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/User', $s->get_username());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/TimeStamp', $s->get_timestamp());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/TestClientVersion', $s->get_client_version());
			$xml_writer->addXmlNode('PhoronixTestSuite/System/Notes', $s->get_notes());

			if(!defined('USER_PTS_CORE_VERSION') || USER_PTS_CORE_VERSION > 3722)
			{
				// Ensure that a supported result file schema is being written...
				// USER_PTS_CORE_VERSION is set by OpenBenchmarking.org so if the requested client is old, don't write this data to send back to their version
				$xml_writer->addXmlNodeWNE('PhoronixTestSuite/System/JSON', ($s->get_json() ? json_encode($s->get_json()) : null));
			}
		}

		// Write the results
		foreach($this->get_result_objects() as $result_object)
		{
			$buffer_items = $result_object->test_result_buffer->get_buffer_items();

			if(count($buffer_items) == 0)
			{
				continue;
			}

			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Identifier', $result_object->test_profile->get_identifier());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Title', $result_object->test_profile->get_title());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/AppVersion', $result_object->test_profile->get_app_version());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Arguments', $result_object->get_arguments());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Description', $result_object->get_arguments_description());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Scale', $result_object->test_profile->get_result_scale());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/Proportion', $result_object->test_profile->get_result_proportion());
			$xml_writer->addXmlNode('PhoronixTestSuite/Result/DisplayFormat', $result_object->test_profile->get_display_format());

			foreach($buffer_items as $i => &$buffer_item)
			{
				$xml_writer->addXmlNode('PhoronixTestSuite/Result/Data/Entry/Identifier', $buffer_item->get_result_identifier());
				$xml_writer->addXmlNode('PhoronixTestSuite/Result/Data/Entry/Value', $buffer_item->get_result_value());
				$xml_writer->addXmlNode('PhoronixTestSuite/Result/Data/Entry/RawString', $buffer_item->get_result_raw());

				if(!defined('USER_PTS_CORE_VERSION') || USER_PTS_CORE_VERSION > 3722)
				{
					// Ensure that a supported result file schema is being written...
					// USER_PTS_CORE_VERSION is set by OpenBenchmarking.org so if the requested client is old, don't write this data to send back to their version
					$xml_writer->addXmlNodeWNE('PhoronixTestSuite/Result/Data/Entry/JSON', ($buffer_item->get_result_json() ? json_encode($buffer_item->get_result_json()) : null));
				}
			}
		}

		return $to == null ? $xml_writer->getXML() : $xml_writer->saveXMLFile($to);
	}
	public function merge($result_merges_to_combine, $pass_attributes = 0)
	{
		if(!is_array($result_merges_to_combine) || empty($result_merges_to_combine))
		{
			return false;
		}

		foreach($result_merges_to_combine as $i => &$merge_select)
		{
			if(!($merge_select instanceof $merge_select))
			{
				$merge_select = new pts_result_merge_select($merge_select);
			}

			if(!is_file($merge_select->get_result_file()))
			{
				if(defined('PTS_SAVE_RESULTS_PATH') && is_file(PTS_SAVE_RESULTS_PATH . $merge_select->get_result_file() . '/composite.xml'))
				{
					$merge_select->set_result_file(PTS_SAVE_RESULTS_PATH . $merge_select->get_result_file() . '/composite.xml');
				}
				else
				{
					unset($result_merges_to_combine[$i]);
				}
			}
		}

		if(empty($result_merges_to_combine))
		{
			return false;
		}

		foreach($result_merges_to_combine as &$merge_select)
		{
			$result_file = new pts_result_file($merge_select->get_result_file(), true);

			if($merge_select->get_rename_identifier())
			{
				$result_file->rename_run(null, $merge_select->get_rename_identifier());
			}

			if($this->get_title() == null && $result_file->get_title() != null)
			{
				$this->set_title($result_file->get_title());
			}

			if($this->get_description() == null && $result_file->get_description() != null)
			{
				$this->set_description($result_file->get_description());
			}

			$this->add_to_result_file($result_file);
			unset($result_file);
		}
	}
}

?>
