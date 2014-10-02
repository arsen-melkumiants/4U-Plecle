<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_payment extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Вывод денег горничных',
			'header_descr' => 'Заявки на вывод денег горничных',
		),
		'delete_request'   => array(
			'header'       => 'Удаление запроса №%id',
			'header_descr' => '',
		),
		'accept_request'   => array(
			'header'       => 'Подтверждение запроса №%id',
			'header_descr' => '',
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		//$this->load->model(ADM_FOLDER.'admin_product_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('id', array('title' => 'Номер заявки'))
			->date('login', array(
				'title' => 'Пользователь',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('4U/manage_user/edit/'.$row['user_id']).'">'.$row['login'].'</a>';
				}
		))
			->text('name', array('title' => 'Платежный способ'))
			->text('number', array('title' => 'Номер счета'))
			->text('amount', array(
				'title' => 'Сумма на снятие',
				'func'  => function($row, $params) {
					return floatval($row['amount']).' рублей';
				}
		))
			->date('add_date', array(
				'title' => 'Дата создания'
			))
			->delete(array('link' => $this->MAIN_URL.'delete_request/%d', 'modal' => 1))
			->btn(array('link' => $this->MAIN_URL.'accept_request/%d', 'modal' => 1, 'icon' => 'ok', 'title' => 'Подтвердить'))
			->create(function($CI) {
				return $CI->db
					->select('r.*, u.first_name, u.last_name')
					->from('user_payment_requests as r')
					->join('users as u', 'r.user_id = u.id')
					->where('r.type', 'withdraw')
					->where('r.status', 0)
					->order_by('r.id', 'desc')
					->get();
			});

		load_admin_views();
	}

	public function delete_request($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$request_info = $this->db->where(array('id' => $id,'status'  => 0))->get('user_payment_requests')->row_array();
		if (empty($request_info)) {
			custom_404();
		}
		set_header_info($request_info);

		if (isset($_POST['delete'])) {
			$this->db->where('id', $id)->update('user_payment_requests', array('status' => 2));
			$this->session->set_flashdata('danger', 'Удаление успешно выполено');
			echo 'refresh';
		} else {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'delete', 'value' => 'Удалить', 'class' => 'btn-danger'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}

	public function accept_request($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$request_info = $this->db->where(array('id' => $id,'status'  => 0))->get('user_payment_requests')->row_array();
		if (empty($request_info)) {
			custom_404();
		}
		set_header_info($request_info);

		$this->load->model('order_model');

		$request_info['amount'] = $this->input->post('amount') ?: $request_info['amount'];

		if (isset($_POST['accept'])) {
			$user_balance = $this->order_model->get_user_balance($request_info['user_id']);
			if ($user_balance < $request_info['amount']) {
				$this->session->set_flashdata('danger', 'Недостаточно средств на счету пользователя('.$user_balance.' рублей)');
				echo 'refresh';
				exit;
			}

			$this->db->trans_begin();

			$this->db->where('id', $id)->update('user_payment_requests', array('status' => 1, 'amount' => $request_info['amount']));
			$this->order_model->log_payment($request_info['user_id'], 'withdraw', $request_info['id'], -($request_info['amount']));
			$this->session->set_flashdata('success', 'Запрос успешно обработан');

			$this->db->trans_commit();
			echo 'refresh';
			exit;
		} else {
			$this->load->library('form');
			$this->data['center_block'] = $this->form
				->text('amount', array(
					'class'       => 'withdraw_amount',
					'value'       => $request_info['amount'] ?: false,
					'valid_rules' => 'required|trim|xss_clean|numeric',
					'symbol'      => 'руб',
					'label'       => 'Количество',
				))
				->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
				->btn(array('name' => 'accept', 'value' => $request_info['type'] == 'withdraw' ? 'Вывести' : 'Пополнить', 'class' => 'btn-success'))
				->create(array('action' => current_url(), 'btn_offset' => 4));
			echo $this->load->view(ADM_FOLDER.'ajax', '', true);
		}
	}

}
