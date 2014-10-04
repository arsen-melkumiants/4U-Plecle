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

	public function autocomplete_orders() {
		$this->load->model('order_model');
		$this->load->library('ion_auth');

		$orders = $this->db->where(array(
			'status'        => 2,
			'cleaner_id !=' => 0,
		))->get('orders')->result_array();

		print_r($orders);
		$type = 'positive';
		$sign = 10;
		foreach ($orders as $order_info) {
			$mark_time = ($order_info['start_date'] + (3600 * $order_info['duration']) + 86400) < time();
			if (!$mark_time) {
				continue;
			}

			$cleaner_price = $order_info['max_sallary'] ? MAX_CLEANER_SALARY : CLEANER_SALARY;
			$update_array = array(
				'status'          => 3,
				'last_mark'       => $type,
				'detergent_price' => $order_info['detergent_price'],
			);

			if ($order_info['frequency'] == 'every_week') {
				$update_array['status'] = 1;
				$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 604800);
			} elseif ($order_info['frequency'] == 'every_2_weeks') {
				$update_array['status'] = 1;
				$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 1209600);
			}

			if ($update_array['status'] === 1) {
				$update_array['price_per_hour']      = PRICE_PER_HOUR;
				$update_array['cleaner_price']       = $cleaner_price;
				$update_array['detergent_price']     = floatval($order_info['detergent_price']) ? DETERGENT_PRICE * $order_info['duration'] : 0;
				$update_array['total_price']         = PRICE_PER_HOUR * $order_info['duration'] + floatval($update_array['detergent_price']);
				$update_array['total_cleaner_price'] = $cleaner_price * $order_info['duration'] + floatval($update_array['detergent_price']);
			}

			$this->db->trans_begin();

			$this->db->where('id', $order_info['id'])->update('orders', $update_array);
			$this->db->insert('marks', array(
				'order_id'   => $order_info['id'],
				'cleaner_id' => $order_info['cleaner_id'],
				'mark'       => $type,
				'amount'     => 10,
				'review'     => '',
				'add_date'   => time(),
				'status'     => 1,
			));
			$this->order_model->log_payment($order_info['cleaner_id'], 'order_payment', $order_info['id'], ($cleaner_price * $order_info['duration'] + floatval($update_array['detergent_price'])));

			$this->db->trans_commit();

			$email_info = array(
				'order_id'   => $order_info['id'],
				'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
			);
			$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
			if (!empty($order_info['cleaner_id'])) {
				$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
			}
		}
	}
	
	private function next_order_time($start_date, $step) {
		$next_date = $start_date + $step;
		if ($next_date - 86400 < time()) {
			$next_date = $this->next_order_time($next_date, $step);
		}
		return $next_date;
	}
















}
