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

}
