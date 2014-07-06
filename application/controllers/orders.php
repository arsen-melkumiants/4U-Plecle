<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
			'table',
		));
		$this->load->helper('url');

		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('personal/login', 'refresh');
		}

		$this->lang->load('auth');
		$this->load->helper('language');

		$this->load->model(array(
			'menu_model',
			'order_model',
			'special_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('main');
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = 'Список сделок';

		$this->data['right_info'] = array(
			'title'         => 'Ваш профиль',
			'info_array'    => array(
				'Имя'       => $this->data['user_info']['first_name'],
				'Фамилия'   => $this->data['user_info']['last_name'],
				'Мобильный' => $this->data['user_info']['phone'],
				'Email'     => $this->data['user_info']['email'],
				'Страна'    => $this->data['user_info']['country'],
				'Город'     => $this->data['user_info']['city'],
				'Адрес'     => $this->data['user_info']['address'],
				'Индекс'    => $this->data['user_info']['zip'],
			),
		);

		$this->data['center_block'] = $this->order_table(0);
		$this->data['center_block'] .= $this->order_table(1);
		$this->data['center_block'] .= $this->order_table(2);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	function detail($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$this->data['order_info'] = $this->order_model->get_user_order($order_id);
		if (empty($this->data['order_info'])) {
			custom_404();
		}

		$this->data['center_block'] = $this->load->view('orders/order_info', $this->data, true);
		$this->data['right_info']   = array(
			'title'      => 'Ваш профиль',
			'info_array' => array(
				'Индекс'          => $this->data['order_info']['zip'],
				'Дата'            => date('d.m.Y', $this->data['order_info']['start_date']),
				'Время'           => date('h:i', $this->data['order_info']['start_date']),
				'Частота'         => $this->order_model->frequency[$this->data['order_info']['frequency']],
				'Рабочие часы'    => $this->order_model->duration[$this->data['order_info']['duration']],
				'Цена за час'     => floatval($this->data['order_info']['price_per_hour']).' руб',
				'Моющие средства' => floatval($this->data['order_info']['detergent_price'] * $this->data['order_info']['need_detergents']).' руб',
				'Итого'           => floatval($this->data['order_info']['total_price']).' руб',
			),
		);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	private function order_table($status = 0) {
		$this->data['status'] = $status;

		$status_labels = array(
			'0' => 'Заявки на сделки',
			'1' => 'Активные сделки',
			'2' => 'Завершенные сделки'
		);

		$result_html = $this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '30%',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('orders/detail/'.$row['id']).'">#'.$row['id'].'</a>';
				}
		))
			->text('status', array(
				'title' => 'Информация',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('orders/detail/'.$row['id']).'">Уборка '.date('d.m.Y в h:i', $row['start_date']).'</a>';
				}
		))
			->create(function($CI) {
				return $CI->order_model->get_all_orders($CI->data['user_info']['id'], $CI->data['status']);
			}, array('no_header' => true, 'class' => 'list'));
		if (!empty($result_html)) {
			$result_html = '<h4 class="title">'.$status_labels[$status].'</h4>'.$result_html;
		}
		return $result_html;
	}

}
