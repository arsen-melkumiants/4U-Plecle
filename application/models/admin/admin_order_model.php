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
			->where('status', 1)
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

}
