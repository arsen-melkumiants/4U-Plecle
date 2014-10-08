<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cli_tools extends CI_Controller {

	private $sms_server = 'http://gateway.api.sc/rest/';

	private $sms_login  = 'plecle';

	private $sms_pswd   = 'Qazqazqaz';

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

		foreach ($result as $item) {
			$this->send_mail($item['email'], 'Горничные для Вас', 'confirm_request', $item);
			$this->db->where('id', $item['id'])->update('order_requests', array('status' => 1, 'send_date' => time()));
		}
	}

	public function autocomplete_orders() {
		$this->load->library('ion_auth');

		$orders = $this->db->where(array(
			'status'        => 2,
			'cleaner_id !=' => 0,
		))->get('orders')->result_array();

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
			$this->log_payment($order_info['cleaner_id'], 'order_payment', $order_info['id'], ($cleaner_price * $order_info['duration'] + floatval($update_array['detergent_price'])));

			$this->db->trans_commit();

			$email_info = array(
				'order_id'   => $order_info['id'],
				'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
			);
			$this->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
			if (!empty($order_info['cleaner_id'])) {
				$this->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
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


	private function send_mail($email, $subject, $mail_view, $email_info){
		$this->load->library('email', array('mailtype'  => 'html'));
		$this->email->from(SITE_EMAIL, SITE_NAME);
		$this->email->to($email);
		$this->email->cc(SITE_EMAIL);
		$this->email->subject($subject);
		$this->email->message($this->load->view('email/'.$mail_view, $email_info ,true));
		$this->email->send();
	}


	private function log_payment($user_id, $type_name, $type_id = 0, $amount, $currency = 1) {
		if (empty($user_id) || empty($type_name) || empty($amount) || empty($currency)) {
			return false;
		}

		$payment_info = array(
			'user_id'   => $user_id,
			'type_name' => $type_name,
			'type_id'   => $type_id,
			'amount'    => $amount,
			'date'      => time(),
		);
		$this->db->insert('user_payment_logs', $payment_info);

		if ($type_name == 'fill_up') {
			$this->send_mail($this->data['user_info']['email'], 'mail_account_reffiled', 'account_reffiled', $payment_info);
		} elseif ($type_name == 'lift_up') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_lift_up_product', 'services_lift_up_product', $payment_info);
		} elseif ($type_name == 'mark') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_mark_product', 'services_mark_product', $payment_info);
		} elseif ($type_name == 'make_vip') {
			$this->send_mail($this->data['user_info']['email'], 'mail_services_vip_product', 'services_vip_product', $payment_info);
		} elseif ($type_name == 'income_product') {
			$this->send_mail($this->data['user_info']['email'], 'mail_product_purchased', 'product_purchased', $payment_info);
		}

		return $this->db->insert_id();
	}


	public function test_sms() {
		$server = 'http://gateway.api.sc/rest/';
		require APPPATH.'third_party/stream_telecom/StreamClass.php';
		$stream = new STREAM();

		// данные пользователя
		$login = 'plecle';
		$password = 'Qazqazqaz';

		// запрос на получение идентификатора сессии
		$session = $stream->GetSessionId($server,$login,$password);
		print_r($session);
		echo "\n";


		echo "\nBalance:\n";
		$balance = $stream->GetBalance($server,$session);
		print_r($balance);
		echo "\n";


		echo "\nGetMessageIn:\n";
		$minDateUTC = '2013-01-01T00:00:00';				//начало периода для запроса входящих сообщений (указывается по UTC)
		$maxDateUTC = '2013-01-03T00:00:00';				//конец периода для запроса входящих сообщений (указывается по UTC)
		$incoming = $stream->GetIncomingSms($server,$session,$minDateUTC,$maxDateUTC);
		print_r($incoming);
		echo "\n";


		// отправка sms-сообщения нескольким получателям
		echo "\nSend Bulk:\n";
		$data = 'Проверка';									//текст сообщения
		$sourceAddress = '1111';	//имя отправителя сообщения (отличное от testsms, имя отправителя Вы можете запросить в личном кабинете)
		$destinationAddresses = 'Номер абонента1, Номер абонента2';
		$send_bulk = $stream->SendBulk($server,$session,$sourceAddress,$destinationAddresses,$data);
		print_r($send_bulk);
		echo "\n";
	}

	public function send_sms() {
		$cleaners = $this->db
			->select('id, phone, zip')
			->where('is_cleaner', 1)
			->get('users')
			->result_array();
		if (empty($cleaners)) {
			exit;
		}

		foreach ($cleaners as $key => $item) {
			$cleaners[$key]['zip'] = explode(',', trim($item['zip'], ','));
		}

		$result_array = array();

		$orders = $this->db
			->select('id, zip, address, start_date, duration, total_cleaner_price')
			->where('cleaner_id', 0)
			->where('((status = 2 AND start_date > '.time().') OR (status  = 1 AND start_date > '.(time() + 86400).') )')
			->where('((recommended) = 0 OR (recommended = 1 AND add_date + '.(3600 * 3).' < '.time().'))')
			->get('orders')
			->result_array();
		if (!empty($orders)) {
			foreach ($orders as $item) {
				$result_array[$item['id']]['address']             = $item['address'];
				$result_array[$item['id']]['start_date']          = $item['start_date'];
				$result_array[$item['id']]['duration']            = $item['duration'];
				$result_array[$item['id']]['total_cleaner_price'] = $item['total_cleaner_price'];
				foreach ($cleaners as $user) {
					if (!in_array($item['zip'], $user['zip'])) {
						continue;
					}

					$result_array[$item['id']]['users'][$user['id']] = $user['phone'];
				}
			}
		}

		$orders = $this->db
			->select('i.*, u.phone, o.address, o.start_date, o.duration, o.total_cleaner_price')
			->from('order_invites AS i')
			->join('orders AS o', 'o.id = i.order_id')
			->join('users AS u', 'u.id = i.cleaner_id')
			->where('o.cleaner_id', 0)
			->where('((o.status = 2 AND o.start_date > '.time().') OR (o.status  = 1 AND o.start_date > '.(time() + 86400).') )')
			->where('o.recommended = 1')
			->where('i.status = 0')
			->get()
			->result_array();
		if (!empty($orders)) {
			foreach ($orders as $item) {
				$result_array[$item['order_id']]['address']                    = $item['address'];
				$result_array[$item['order_id']]['start_date']                 = $item['start_date'];
				$result_array[$item['order_id']]['duration']                   = $item['duration'];
				$result_array[$item['order_id']]['total_cleaner_price']        = $item['total_cleaner_price'];
				$result_array[$item['order_id']]['users'][$item['cleaner_id']] = $item['phone'];
			}
		}

		if (empty($result_array)) {
			exit;
		}

		$query = $this->db
			->where_in('order_id', array_keys($result_array))
			->get('order_sms_log');

		$sms_logs = array();
		foreach ($query->result_array() as $row) {
			$sms_logs[$row['order_id']]['users'][$row['user_id']] = $row['phone'];
		}

		require APPPATH.'third_party/stream_telecom/StreamClass.php';
		$stream = new STREAM();
		$session = $stream->GetSessionId($this->sms_server,$this->sms_login,$this->sms_pswd);
		$sourceAddress = 'Plecle.com';	//имя отправителя сообщения (отличное от testsms, имя отправителя Вы можете запросить в личном кабинете)

		foreach ($result_array as $order_id => $item) {
			if (isset($sms_logs[$order_id])) {
				$item['users'] = array_diff_key($item['users'], $sms_logs[$order_id]['users']);
			}

			if (empty($item['users'])) {
				continue;
			}

			$data = 'Заказ #'.$order_id.', '.$item['address'].', '.date('d.m.Y в H:i', $item['start_date']).', '.$item['duration'].' час(а), '.floatval($item['total_cleaner_price']).' руб'; //текст сообщения
			$data = translitIt($data, true);
			$destinationAddresses = implode(', ', array_keys($item['users']));
			//$send_bulk = $stream->SendBulk($this->sms_server, $session, $sourceAddress, $destinationAddresses, $data, 1440);

			$insert_sql = array();
			foreach ($item['users'] as $user_id => $phone) {
				$insert_sql[] = array(
					'order_id' => $order_id,
					'user_id'  => $user_id,
					'phone'    => $phone,
					'add_date' => time(),
				);
			}
			$this->db->insert_batch('order_sms_log', $insert_sql);
		}
	}
}
