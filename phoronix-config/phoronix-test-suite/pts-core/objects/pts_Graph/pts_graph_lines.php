<?php

/*
	Phoronix Test Suite
	URLs: http://www.phoronix.com, http://www.phoronix-test-suite.com/
	Copyright (C) 2008 - 2016, Phoronix Media
	Copyright (C) 2008 - 2016, Michael Larabel
	pts_LineGraph.php: The line graph object that extends pts_Graph.php.

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

class pts_graph_lines extends pts_graph_core
{
	private $max_count;
	public function __construct(&$result_object, &$result_file = null, $extra_attributes = null)
	{
		$max_count = 0;
		if(!isset($extra_attributes['force_tracking_line_graph']) || !$extra_attributes['force_tracking_line_graph'])
		{
			foreach($result_object->test_result_buffer->buffer_items as &$buffer_item)
			{
				/*
				$r = pts_strings::comma_explode($buffer_item->get_result_raw());
				$c = count($r);
				foreach($r as $val)
				{
					echo $val;
				}
				*/

				$values = pts_strings::comma_explode($buffer_item->get_result_value());
				$buffer_item->reset_result_value($values);
				$buffer_item->reset_raw_value(pts_strings::comma_explode($buffer_item->get_result_raw()));
				$max_count = max($max_count, count($values));
			}
		}

		//$extra_attributes['no_compact_results_var'] = true;
		parent::__construct($result_object, $result_file, $extra_attributes);
		$this->i['show_graph_key'] = true;
		$this->i['show_background_lines'] = true;
		$this->i['iveland_view'] = true;
		$this->i['min_identifier_size'] = 6.5;
		$this->i['plot_overview_text'] = isset($extra_attributes['no_overview_text']) == false;
		$this->i['display_select_identifiers'] = false;
		$this->i['hide_graph_identifiers'] = !isset($extra_attributes['force_tracking_line_graph']) || !$extra_attributes['force_tracking_line_graph'];

		// XXX removed on 20 January from here: $this->is_multi_way_comparison &&
		if(isset($extra_attributes['force_tracking_line_graph']) && $extra_attributes['force_tracking_line_graph'] && is_array($this->results))
		{
			// need to do compacting here
			$this->test_result->test_result_buffer = new pts_test_result_buffer();
			foreach($this->results as $system => $results)
			{
				$result_r = array();
				$raw_r = array();
				//$json_r = array();
				foreach($this->graph_identifiers as $d)
				{
					$result_r[$d] = null;
					$raw_r[$d] = null;
					//$json_r[$d] = null;
				}
				foreach($results as &$buffer_item)
				{
					$result_r[$buffer_item->get_result_identifier()] = $buffer_item->get_result_value();
					$raw_r[$buffer_item->get_result_identifier()] = $buffer_item->get_result_raw();
					$json_r[$buffer_item->get_result_identifier()] = $buffer_item->get_result_json();
				}
				// add array_values($json_r)
				$this->test_result->test_result_buffer->add_test_result($system, array_values($result_r), array_values($raw_r));
			}
			$max_count = count($this->graph_identifiers) + 1;
		}

		$this->max_count = $max_count;
	}
	protected function render_graph_pre_init()
	{
		// Do some common work to this object
		$this->i['identifier_width'] = $this->max_count > 0 ? (($this->i['graph_left_end'] - $this->i['left_start']) / $this->max_count) : 1;

		if(!$this->i['hide_graph_identifiers'])
		{
			$identifier_count = $this->test_result->test_result_buffer->get_count();
			$longest_string = pts_strings::find_longest_string($this->graph_identifiers);
			$this->i['identifier_size'] = $this->text_size_bounds($longest_string, $this->i['identifier_size'], $this->i['min_identifier_size'], $this->i['identifier_width'] - 4);

			if($this->i['identifier_size'] <= $this->i['min_identifier_size'])
			{
				list($text_width, $text_height) = pts_svg_dom::estimate_text_dimensions($longest_string, $this->i['min_identifier_size'] + 0.5);
				$this->i['bottom_offset'] += $text_width;
				$this->update_graph_dimensions($this->i['graph_width'], $this->i['graph_height'] + $text_width);

				if(($text_height + 4) > $this->i['identifier_width'] && $identifier_count > 3)
				{
					// Show the identifiers as frequently as they will fit
					$this->i['display_select_identifiers'] = ceil(($text_height + 4) / $this->i['identifier_width']);
				}
			}
		}

		$max_value = 0;
		foreach($this->test_result->test_result_buffer->buffer_items as &$buffer_item)
		{
			if(!is_array($buffer_item->get_result_value()))
			{
				$max_value = max($max_value,  $buffer_item->get_result_value());
			}
			else
			{
				$max_value = max($max_value,  max($buffer_item->get_result_value()));
			}
		}

		if($this->graph_y_title == 'Percent' && $max_value < 100 && $max_value > 80)
		{
			$this->i['graph_max_value'] = 120;
			$this->i['mark_count'] = 6;
		}
		else
		{
			$max_value *= 1.25; // leave room at top of graph
			$this->i['graph_max_value'] = round($max_value, $max_value < 10 ? 1 : 0);
			$this->i['graph_max_value'] = round(ceil($this->i['graph_max_value'] / $this->i['mark_count']), (0 - strlen($this->i['graph_max_value']) + 2)) * $this->i['mark_count'];
		}
	}
	protected function render_graph_identifiers()
	{
		if($this->i['hide_graph_identifiers'])
		{
			return;
		}

		$px_from_top_end = $this->i['graph_top_end'] + 5;

		if($this->i['identifier_width'] > 2)
		{
			$this->svg_dom->draw_svg_line($this->i['left_start'] + $this->i['identifier_width'], $this->i['graph_top_end'], $this->i['graph_left_end'], $this->i['graph_top_end'], self::$c['color']['notches'], 10, array('stroke-dasharray' => '1,' . ($this->i['identifier_width'] - 1)));
		}
		else if($this->i['display_select_identifiers'])
		{
			$this->svg_dom->draw_svg_line($this->i['left_start'] + ($this->i['identifier_width'] * $this->i['display_select_identifiers']), $this->i['graph_top_end'], $this->i['graph_left_end'], $this->i['graph_top_end'], self::$c['color']['notches'], 10, array('stroke-dasharray' => '1,' . (($this->i['identifier_width'] * $this->i['display_select_identifiers']) - 1)));
		}

		$g = $this->svg_dom->make_g(array('fill' => self::$c['color']['headers'], 'font-size' => ($this->i['identifier_size'] <= $this->i['min_identifier_size'] ? 9 : $this->i['identifier_size']), 'text-anchor' => ($this->i['identifier_size'] <= $this->i['min_identifier_size'] ? 'start' : 'middle')));
		foreach(array_keys($this->graph_identifiers) as $i)
		{
			if($this->i['display_select_identifiers'] && ($i % $this->i['display_select_identifiers']) != 0)
			{
				// $this->i['display_select_identifiers'] contains the value of how frequently to display identifiers
				continue;
			}

			$px_from_left = floor($this->i['left_start'] + ($this->i['identifier_width'] * ($i + (count($this->graph_identifiers) > 1 ? 1 : 0))));

			if($this->i['identifier_size'] <= $this->i['min_identifier_size'])
			{
				$this->svg_dom->add_text_element($this->graph_identifiers[$i], array('x' => $px_from_left, 'y' => ($px_from_top_end + 2), 'transform' => 'rotate(90 ' . $px_from_left . ' ' . ($px_from_top_end + 2) . ')'), $g);
			}
			else
			{
				$this->svg_dom->add_text_element($this->graph_identifiers[$i], array('x' => $px_from_left, 'y' => ($px_from_top_end + 10), 'fill' => self::$c['color']['headers']), $g);
			}
		}
	}
	protected function render_graph_key()
	{
		if(isset($this->i['force_simple_keys']) && $this->i['force_simple_keys'])
		{
			return parent::render_graph_key();
		}
		if(!$this->i['plot_overview_text'])
		{
			return;
		}
		if($this->i['key_line_height'] == 0)
		{
			return;
		}

		$square_length = 10;
		$precision = $this->i['graph_max_value'] > 999 ? 0 : 1;

		$num_rows = max(1, ceil($this->test_result->test_result_buffer->get_count() / $this->i['keys_per_line']));
		$num_cols = ceil($this->test_result->test_result_buffer->get_count() / $num_rows);

		$y_start = $this->i['top_heading_height'] + 24;
		//$y_start = $this->i['top_start'] - $this->graph_key_height() + $this->getStatisticsHeaderHeight();
		$y_end = $y_start + $this->i['key_line_height'] * ($num_rows );
		$x_start = $this->i['left_start'];
		$x_end = $x_start + $this->i['key_item_width'] * ($num_cols - 1);

		// draw the "Min Avg Max" text
		$stat_header_offset = $this->i['key_longest_string_width'] + $square_length + 10;
		$g_text = $this->svg_dom->make_g(array('font-size' => 6.5, 'fill' => self::$c['color']['notches']));
		for($x = $x_start + $stat_header_offset; $x <= $x_end + $stat_header_offset; $x += $this->i['key_item_width'])
		{
			$stat_words = array('Min', 'Avg', 'Max');
			$stat_word_width = $this->get_stat_word_width();
			$attributes = array('y' => $y_start - 14);

			foreach($stat_words as &$stat_word)
			{
				$attributes['x'] = $x;
				$this->svg_dom->add_text_element($stat_word, $attributes, $g_text);
				$x += $stat_word_width;
			}
		}

		// draw the keys and the min,avg,max values
		$g_rect = $this->svg_dom->make_g(array('stroke' => self::$c['color']['notches'], 'stroke-width' => 1));
		for($i = 0, $x = $x_start; $x <= $x_end; $x += $this->i['key_item_width'])
		{
			for ($y = $y_start; $y <= $y_end; $y += $this->i['key_line_height'], ++$i)
			{
				if(!isset($this->test_result->test_result_buffer->buffer_items[$i]))
				{
					break;
				}

				$identifier_title = $this->test_result->test_result_buffer->buffer_items[$i]->get_result_identifier();
				$this_color = $this->get_paint_color($identifier_title);

				// draw square
				$this->svg_dom->add_element('rect',
								array('x' => $x, 'y' => $y - $square_length, 'width' => $square_length,
								  'height' => $square_length, 'fill' => $this_color), $g_rect);

				// draw text
				$g_text = $this->svg_dom->make_g(array('font-size' => self::$c['size']['key'], 'fill' => $this_color));
				$this->svg_dom->add_text_element($identifier_title, array('x' => $x + $square_length + 4, 'y' => $y), $g_text);

				// draw min/avg/max
				$x_stat_loc = $x + $square_length + $this->i['key_longest_string_width'] + 10;
				$vals = $this->test_result->test_result_buffer->buffer_items[$i]->get_result_value();
				$attributes = array('y' => $y);

				$stat_word_width = $this->get_stat_word_width();
				$stat_array = $this->calc_min_avg_max($vals);

				$precise_stat_array = array();
				foreach($stat_array as $stat_value)
				{
					if(is_string($stat_value))
					{
						// TODO investigate this case...
						// sometimes $stat_value is string of comma separated values
						$precise_stat_array[] = $stat_value;
					}
					else
					{
						$precise_stat_array[] = pts_math::set_precision($stat_value, $precision);
					}
				}

				$attributes['x'] = $x_stat_loc;
				foreach($precise_stat_array as $stat_value)
				{
					$this->svg_dom->add_text_element(strval($stat_value), $attributes, $g_text);
					$attributes['x'] += $stat_word_width;
				}
			}
		}
	}
	private static function calc_min_avg_max(&$data_set)
	{
		if(empty($data_set))
		{
			return array(0, 0, 0);
		}

		$min_value = min($data_set);
		$max_value = max($data_set);
		$avg_value = array_sum($data_set) / count($data_set);

		return array($min_value, $avg_value, $max_value);
	}
	protected function renderGraphLines()
	{
		$prev_value = 0;

		foreach($this->test_result->test_result_buffer->buffer_items as &$buffer_item)
		{
			$paint_color = $this->get_paint_color($buffer_item->get_result_identifier());
			$result_array = $buffer_item->get_result_value();
			$raw_array = $buffer_item->get_result_raw();
			$point_counter = count($result_array);
			$regression_plots = array();
			$poly_points = array();
			$g = $this->svg_dom->make_g(array('stroke' => $paint_color, 'stroke-width' => 1, 'fill' => $paint_color));

			for($i = 0; $i < $point_counter; $i++)
			{
				$value = isset($result_array[$i]) ? $result_array[$i] : -1;

				if($value < 0)
				{
					// Draw whatever is needed of the line so far, since there is no result here
					$this->draw_graph_line_process($poly_points, $paint_color, $regression_plots, $point_counter, $g);
					continue;
				}

				$identifier = $buffer_item->get_result_identifier();
				$std_error = isset($raw_array[$i]) ? pts_math::standard_error(pts_strings::colon_explode($raw_array[$i])) : 0;
				$data_string = $identifier . ': ' . $value;

				$value_plot_top = $this->i['graph_top_end'] + 1 - ($this->i['graph_max_value'] == 0 ? 0 : round(($value / $this->i['graph_max_value']) * ($this->i['graph_top_end'] - $this->i['top_start'])));
				$px_from_left = round($this->i['left_start'] + ($this->i['identifier_width'] * ($i + (count($this->graph_identifiers) > 1 ? 1 : 0))));

				if($px_from_left > $this->i['graph_left_end'])
				{
					break;
				}

				if($value_plot_top >= $this->i['graph_top_end'])
				{
					$value_plot_top = $this->i['graph_top_end'] - 1;
				}

				$poly_points[] = array($px_from_left, $value_plot_top, $data_string, $std_error);

				if(isset($this->d['regression_marker_threshold']) && $this->d['regression_marker_threshold'] > 0 && $i > 0 && abs(1 - ($value / $prev_value)) > $this->d['regression_marker_threshold'])
				{
					$regression_plots[($i - 1)] = $prev_identifier . ': ' . $prev_value;
					$regression_plots[$i] = $identifier . ': ' . $value;
				}

				$prev_identifier = $identifier;
				$prev_value = $value;
			}

			$this->draw_graph_line_process($poly_points, $paint_color, $regression_plots, $point_counter, $g);
		}
	}
	protected function draw_graph_line_process(&$poly_points, &$paint_color, &$regression_plots, $point_counter, &$g)
	{
		$poly_points_count = count($poly_points);

		if($poly_points_count == 0)
		{
			// There's nothing to draw
			return;
		}

		$svg_poly = array();
		foreach($poly_points as $x_y)
		{
			$svg_poly[] = round($x_y[0]) . ',' . round($x_y[1]);
		}
		$svg_poly = implode(' ', $svg_poly);
		$this->svg_dom->add_element('polyline', array('points' => $svg_poly, 'fill' => 'none', 'stroke-width' => 2), $g);

		// plot error bars if needed
		foreach($poly_points as $i => $x_y_pair)
		{
			if($x_y_pair[0] < ($this->i['left_start'] + 2) || $x_y_pair[0] > ($this->i['graph_left_end'] - 2))
			{
				// Don't draw anything on the left or right hand edges
				continue;
			}

			$plotted_error_bar = false;
			if($x_y_pair[3] > 0 && $this->i['graph_max_value'] != 0)
			{
				$std_error_width = 4;
				$std_error_rel_size = round(($x_y_pair[3] / $this->i['graph_max_value']) * ($this->i['graph_top_end'] - $this->i['top_start']));

				if($std_error_rel_size > 3)
				{
					$this->svg_dom->add_element('line', array('x1' => $x_y_pair[0], 'y1' => ($x_y_pair[1] + $std_error_rel_size), 'x2' => $x_y_pair[0], 'y2' => ($x_y_pair[1] - $std_error_rel_size)), $g);
					$this->svg_dom->add_element('line', array('x1' => ($x_y_pair[0] - $std_error_width), 'y1' => ($x_y_pair[1] - $std_error_rel_size), 'x2' => ($x_y_pair[0] + $std_error_width), 'y2' => ($x_y_pair[1] - $std_error_rel_size)), $g);
					$this->svg_dom->add_element('line', array('x1' => ($x_y_pair[0] - $std_error_width), 'y1' => ($x_y_pair[1] + $std_error_rel_size), 'x2' => ($x_y_pair[0] + $std_error_width), 'y2' => ($x_y_pair[1] + $std_error_rel_size)), $g);
					$plotted_error_bar = true;
				}
			}

			if(isset($regression_plots[$i]) && $i > 0)
			{
				$this->svg_dom->draw_svg_line($x_y_pair[0], $x_y_pair[1] + 6, $x_y_pair[0], $x_y_pair[1] - 6, self::$c['color']['alert'], 4, array('xlink:title' => $regression_plots[$i]));
			}

			if($point_counter < 6 || $plotted_error_bar || $i == 0 || $i == ($poly_points_count  - 1))
			{
				$this->svg_dom->add_element('ellipse', array('cx' => $x_y_pair[0], 'cy' => $x_y_pair[1], 'rx' => 3, 'ry' => 3, 'xlink:title' => $x_y_pair[2]), $g);
			}
		}

		$poly_points = array();
	}
	protected function render_graph_result()
	{
		$this->renderGraphLines();
	}
	protected function graph_key_height()
	{
		if(isset($this->i['force_simple_keys']) && $this->i['force_simple_keys'])
		{
			return parent::graph_key_height();
		}
		if($this->test_result->test_result_buffer->get_count() < 2 && $this->i['show_graph_key'] == false)
		{
			return 0;
		}

		$this->i['key_line_height'] = 16;
		$this->i['key_longest_string_width'] = self::text_string_width($this->test_result->test_result_buffer->get_longest_identifier(), self::$c['size']['key']);

		$item_width_spacing = 32;
		$this->i['key_item_width'] = $this->i['key_longest_string_width'] + $this->get_stat_word_width() * 3 + $item_width_spacing;

		// if there are <=4 data sets, then use a single column, otherwise, try and multi-col it
		if($this->test_result->test_result_buffer->get_count() < 3)
		{
			$this->i['keys_per_line'] = 1;
		}
		else
		{
			$this->i['keys_per_line'] = max(1, floor(($this->i['graph_left_end'] - $this->i['left_start']) / $this->i['key_item_width']));
		}

		$statistics_header_height = $this->getStatisticsHeaderHeight();
		$extra_spacing = 16;
		return ceil($this->test_result->test_result_buffer->get_count() / $this->i['keys_per_line']) * $this->i['key_line_height']
			+ $statistics_header_height + $extra_spacing;
	}
	private function get_stat_word_width()
	{
		return ceil(2.6 * self::text_string_width($this->i['graph_max_value'] + 0.1, $this->i['min_identifier_size'] + 0.5));
	}
	private function getStatisticsHeaderHeight()
	{
		return $this->i['plot_overview_text'] ? 10 : 0;
	}
}

?>
