<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Statistics extends CI_Controller {

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
				'Индекс'    => trim($this->data['user_info']['zip'], ','),
			),
		);

		$this->data['center_block'] = $this->load->view('orders/statistics', $this->data, true);
		/*$this->stats = array(
			'month_payment' => 
		);*/

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

}
