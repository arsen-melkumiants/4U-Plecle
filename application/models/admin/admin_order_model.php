<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_order_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_orders($status = false) {
		if ($status !== false) {
			if ($status == 0) {
				$this->db->where('status', 0);
				$this->db->where('start_date >', time() + 86400);
			} elseif ($status == 1) {
				$this->db->where('(status = 2 OR (status = 1 AND start_date > '.(time() + 86400).'))');
			} else {
				$this->db->where('(status > 2 OR (status IN (0,1) AND start_date < '.(time() + 86400).'))');
			}
		}

		return $this->db
			->order_by('id', 'desc')
			->get('orders');
	}

	function get_order_info($id) {
		return $this->db->where('id', $id)->get('orders')->row_array();
	}

	function get_payments($order_id) {
		if (!empty($order_id)) {
			$this->db->where('order_id', $order_id);
		}

		return $this->db
			->where_in('status', array(1,2))
			->order_by('id', 'desc')
			->get('payments');
	}

	function get_regions() {
		return $this->db->order_by('id', 'desc')->get('regions');
	}

	function get_region_info($id) {
		return $this->db->where('id', $id)->get('regions')->row_array();
	}

	function get_all_zips() {
		$all_regions = $this->db->get('regions')->result_array();
		if (empty($all_regions)) {
			return array();
		}
		foreach ($all_regions as $item) {
			$zips = explode(',', trim($item['zips'], ','));
			if (empty($zips) || (count($zips)) == 1 && empty($zips[0])) {
				continue;
			}
			foreach ($zips as $zip) {
				$result_array[$zip] = $item['name'];
			}
		}

		if (empty($result_array)) {
			return array();
		}
		return $result_array;
	}

	function show_user_zips($user_zips) {
		$zips = explode(',', trim($user_zips, ','));
		if (empty($zips) || (count($zips)) == 1 && empty($zips[0])) {
			return false;
		}

		foreach ($zips as $cur_zip) {
			if (isset($this->zip[$cur_zip])) {
				$result_array[$this->zip[$cur_zip]][] = $cur_zip;
			} else {
				$result_array['Неопределенные'][] = $cur_zip;
			}
		}

		if (empty($result_array)) {
			return false;
		}

		foreach ($result_array as $name => $zip) {
			$result_array[$name] = implode(',', $result_array[$name]).' - ('.$name.')';
		}
		$result_string = implode(', ', $result_array);
		return $result_string;
	}

	function get_all_reviews() {
		return $this->db
			->select('m.*, u.first_name as client_first_name, u.last_name as client_last_name, c.first_name as cleaner_first_name, c.last_name as cleaner_last_name, u.id as client_id')
			->from('marks as m')
			->join('orders as o', 'o.id = m.order_id')
			->join('users as u', 'u.id = o.client_id')
			->join('users as c', 'c.id = m.cleaner_id')
			->order_by('m.add_date', 'desc')
			->get();
	}

	function get_review_info($id = false) {
		return $this->db
			->select('m.*, u.id as client_id')
			->from('marks as m')
			->join('orders as o', 'o.id = m.order_id')
			->join('users as u', 'u.id = o.client_id')
			->where('m.id', $id)
			->get()
			->row_array();
	}

	function set_period_statistic($period = 'all', $field_name = 'start_date') {
		if ($period == 'daily') {
			$day = strtotime('today UTC');
			$this->db->where($field_name.' >', $day);
			$this->db->where($field_name.' <', $day + 86400);
		} elseif ($period == 'week') {
			$this->db->where($field_name.' >', strtotime('monday this week'));
			$this->db->where($field_name.' <', strtotime('monday next week'));
		} elseif ($period == 'month') {
			$this->db->where($field_name.' >', strtotime('first day of this month'));
			$this->db->where($field_name.' <', strtotime('first day of next month'));
		} elseif ($period == 'year') {
			$this->db->where($field_name.' >', strtotime(date('Y').'-01-01'));
			$this->db->where($field_name.' <', strtotime((date('Y') + 1).'-01-01'));
		} elseif (is_array($period)) {
			$this->db->where($field_name.' >', $period['from']);
			$this->db->where($field_name.' <', $period['to']);
		}
	}

	function get_order_stats($period = 'all') {
		$this->set_period_statistic($period);
		$order_counts = $this->db->select('COUNT(*) as count, last_mark as mark')
			->group_by('last_mark')
			->get('orders')
			->result_array();

		$result_array = array(
			'all'      => 0,
			'positive' => 0,
			'negative' => 0,
		);

		if (empty($order_counts)) {
			return $result_array;
		}

		$result_array['all'] = 0;
		foreach ($order_counts as $item) {
			$result_array['all'] += $item['count'];
			if (!empty($item['mark'])) {
				$result_array[$item['mark']] = $item['count'];
			}
		}

		return $result_array;
	}

	function get_turnover($period = 'all') {
		$this->set_period_statistic($period, 'add_date');
		$turnover_info = $this->db->select('SUM(total_price) AS total, SUM(total_price - total_cleaner_price) AS profit', false)
			->where('status', 1)
			->get('payments')
			->row_array();

		if (empty($turnover_info)) {
			return array(
				'total'  => 0,
				'profit' => 0,
			);
		}

		return $turnover_info;
	}

	function get_user_count() {
		$user_count = $this->db->select('COUNT(*) AS count, is_cleaner', false)
			->group_by('is_cleaner')
			->get('users')
			->result_array();

		$result_array = array(
			'clients'  => 0,
			'cleaners' => 0,
		);

		if (empty($user_count)) {
			return $result_array;
		}

		foreach ($user_count as $item) {
			if ($item['is_cleaner']) {
				$result_array['cleaners'] = $item['count'];
			} else {
				$result_array['clients'] = $item['count'];
			}
		}
		return $result_array;
	}

	function get_region_orders($period = 'all') {
		$this->set_period_statistic($period, 'start_date');
		return $this->db->select('COUNT(*) as count, zip')
			->group_by('zip')
			->get('orders');

	}
}
