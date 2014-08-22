<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Reviews extends CI_Controller {

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
		if (!$this->data['user_info']['is_cleaner']) {
			custom_404();
		}
		$this->data['title'] = $this->data['header'] = 'Мои отзывы';

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

		$result_html = '<h4 class="title">'.$this->data['header'].'</h4>';
		$result_html .= $this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '20%',
				'func'  => function($row, $params) {
					if ($row['mark'] == 'positive') {
						return '<div class="positive"><div class="name">'.$row['first_name'].'</div><div>'.$row['review'].'</div></div>';
					} else {
						return '<div class="negative"><div class="name">'.$row['first_name'].'</div><div>'.$row['review'].'</div></div>';
					}
				}
			))
			->create(function($CI) {
				return $CI->order_model->get_all_reviews($CI->data['user_info']['id']);
			}, array('no_header' => true, 'class' => 'list reviews'));
		$this->data['center_block'] = $result_html;

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('profile/favorites_js', $this->data);
		$this->load->view('footer', $this->data);
	}
}
