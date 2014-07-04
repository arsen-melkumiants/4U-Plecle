<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
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
			'special_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('main');
		$this->data['user_info'] = $this->ion_auth->user()->row_array();

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = lang('product_add_header');

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

		$this->data['center_block'] = '
			<h4 class="title">Активные сделки</h4>
			<table class="list">
			<tr>
			<td width="30%"><a href="#">#291</a></td>
			<td><a href="#">Уборка 26.05.2014 в 14:00</a></td>
			</tr>
			</table>
			';

		$this->load->view('header', $this->data);
		$this->load->view('orders/cleaner_top', $this->data);
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}


	function edit_product($id = false) {
		$id = $this->data['id'] = intval($id);
		$product_info = $this->shop_model->get_product_by_user($id, $this->data['user_info']['id']);
		if (empty($product_info)) {
			redirect('profile/products', 'refresh');
		}

		$this->data['title'] = $this->data['name'] = lang('product_edit_header').' "'.$product_info['name'].'"';
		$this->data['center_block'] = $this->edit_form($product_info);
		$this->data['center_block'] = $this->load->view('profile/edit', $this->data, true);

		if ($this->form_validation->run() == FALSE) {
			load_views();
		} else {
			$info = array(
				'name'             => $this->input->post('name'),
				'price'            => $this->input->post('price'),
				'cat_id'           => $this->input->post('cat_id'),
				'content'          => $this->input->post('content'),
				'author_id'        => $this->data['user_info']['id'],
				'last_update_date' => time(),
			);

			if ($product_info['type'] == 'media') {
				$info['amount']    = $this->input->post('amount');
				$info['unlimited'] = $this->input->post('unlimited');
			}

			foreach ($info as $key => $item) {
				if (isset($product_info[$key]) && $product_info[$key] != $item) {
					$info['status'] = 0;
					break;
				}
			}

			if ($product_info['is_locked']) {
				set_alert(lang('product_edit_message_lock'), false, 'warning');
			}

			if (isset($info['status']) && !$product_info['is_locked'])	{
				$this->db->where('id', $id)->update('shop_products', $info);
				if (!$product_info['created']) {
					$this->session->set_flashdata('success', lang('product_add_message_success'));
				}
			}

			if ($this->input->post('next_step')) {
				redirect('profile/product_gallery/'.$product_info['id'], 'refresh');
			}
			redirect(current_url(), 'refresh');
		}

	}

	private function edit_form($product_info = false) {
		$this->load->library('form');
		$this->form
			->text('name', array(
				'value'       => $product_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_name'),
			))
			->text('price', array(
				'value'       => $product_info['price'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|price',
				'symbol'      => '$',
				'icon_post'   => true,
				'label'       => lang('product_price'),
			))
			->select('cat_id', array(
				'value'       => $product_info['cat_id'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_category'),
				'options'     => $product_categories,
				'search'      => true,
			))
			->textarea('content', array(
				'value'       => $product_info['content'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => lang('product_content'),
			))
			->btn(array('value' => empty($product_info) ? lang('add') : lang('edit')))
			->create(array('action' => current_url(), 'error_inline' => 'true'));
	}

}
