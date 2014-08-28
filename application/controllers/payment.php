<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class payment extends CI_Controller {

	private $mrh_pass1 = 'evsY7kHJWTCWtTXTdk';

	private $mrh_pass2 = 'ayk75VeIlIBwFp2XrH';

	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'order_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
		));
		$this->data['main_menu']     = $this->menu_model->get_menu('main');
		$this->data['help_menu']     = $this->menu_model->get_menu('help');
		$this->data['services_menu'] = $this->menu_model->get_menu('services');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	public function index() {
		custom_404();
	}

	function result() {
		$mrh_pass2 = $this->mrh_pass2;

		$out_summ = $_REQUEST['OutSum'];
		$inv_id   = $_REQUEST['InvId'];
		$crc      = $_REQUEST['SignatureValue'];

		$crc = strtoupper($crc);

		$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass2"));

		// проверка корректности подписи
		// check signature
		if ($my_crc != $crc)
		{
			echo "bad sign\n";
			exit;
		}

		$payment_info = $this->db->where(array(
			'id'          => $inv_id,
			'total_price' => $out_summ,
			'status'      => 0
		))->get('payments')->row_array();

		if (empty($payment_info)) {
			echo "bad sign\n";
			exit;
		}
		$this->db->trans_begin();
		$this->db->where('id', $payment_info['order_id'])->update('orders', array('status' => 2));
		$this->db->where('id', $payment_info['id'])->update('payments', array('status' => 1));
		$this->db->trans_commit();
		$order_info = $this->db->where('id', $payment_info['order_id'])->get('orders')->row_array();
		$email_info = array(
			'order_id'   => $order_info['id'],
			'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
		);
		$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Оплата успешно произведена', 'paid_order', $email_info);
		if (!empty($order_info['cleaner_id'])) {
			$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Оплата успешно произведена', 'paid_order', $email_info);
		}

		// success
		echo "OK$inv_id\n";
		exit;
	}

	function success() {
		// your registration data
		$mrh_pass1 = $this->mrh_pass1;

		// HTTP parameters:
		$out_summ = $_REQUEST["OutSum"];
		$inv_id   = $_REQUEST["InvId"];
		$crc      = $_REQUEST["SignatureValue"];

		$crc = strtoupper($crc);  // force uppercase

		// build own CRC
		$my_crc = strtoupper(md5("$out_summ:$inv_id:$mrh_pass1"));

		if (strtoupper($my_crc) != strtoupper($crc)) {
			return redirect();
		}

		$payment_info = $this->db->where(array('id' =>  $inv_id, 'status' => 1))->get('payments')->row_array();
		if (empty($payment_info)) {
			return redirect();
		}

		$this->session->set_flashdata('success', 'Поздравляем! Оплата успешно совершена');
		return redirect();
	}

	function fail() {
		$this->session->set_flashdata('danger', 'Вы отказались от оплаты. Заказ #'.$_REQUEST["InvId"]);
		return redirect();
	}
}
