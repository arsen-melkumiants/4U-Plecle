<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cli_tools extends CI_Controller {

	public function __construct(){
		parent::__construct();
		if (!$this->input->is_cli_request()) {
			custom_404();
		}

		$this->load->database();
	}

	public function index() {
		return false;
	}

	public function process_requests() {
		$result = $this->db
			->query('SELECT r.*
			FROM order_requests AS r
			JOIN users AS u ON u.zip LIKE CONCAT(\'%,\', r.zip ,\',%\')
			WHERE u.is_cleaner =  1
			AND u.active =  1
			AND r.status =  0')
			->result_array();
		if (empty($result)) {
			return false;
		}

		$this->load->model('order_model');

		foreach ($result as $item) {
			$this->order_model->send_mail($item['email'], 'Горничные для Вас', 'confirm_request', $item);
			$this->db->where('id', $item['id'])->update('order_requests', array('status' => 1, 'send_date' => time()));
		}
	}
}
