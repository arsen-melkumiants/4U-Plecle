<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Favorites extends CI_Controller {

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
		if ($this->data['user_info']['is_cleaner']) {
			custom_404();
		}
		$this->data['title'] = $this->data['header'] = 'Избранные горничные';

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
					return '<a data-toggle="modal" data-target="#ajaxModal" href="'.site_url('personal/cleaner_profile/'.$row['user_id']).'">'.
						'<img src="'.(!empty($row['photo']) ? '/uploads/avatars/'.$row['photo'] : '/img/no_photo.jpg').'" width="100" class="img-circle">'.
						'</div>';
				}
			))
			->text('first_name', array(
				'title' => 'Информация',
				'width' => '30%',
				'func'  => function($row, $params) {
					return '<h4>'.$row['first_name'].'</h4>';
				}
			))
			->text('info', array(
				'width' => '40%',
				'title' => 'Заметка',
				'func'  => function($row, $params) {
					return '<div class="text_edit" placeholder="Можно добавить заметку">'.$row['info'].'</div>
						<button data-id="'.$row['id'].'" data-loading-text="Сохранение..." class="btn btn-default btn-xs text_save">Сохранить</button>
						<small class="text-danger">До 250 символов</small>';
				}
			))
			->btn(array(
				'link' => 'favorites/delete/%d',
				'icon' => 'remove',
			))
			->create(function($CI) {
				return $CI->special_model->get_favorite_users($CI->data['user_info']['id']);
			}, array('no_header' => true, 'class' => 'list cleaners'));
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

	function edit($id = false) {
		$id = intval($id);
		$favorite_info = $this->db->where(array('id' => $id, 'owner_id' => $this->data['user_info']['id']))->get('favorites')->row_array();
		if (empty($favorite_info)) {
			custom_404();
		}

		$this->load->library('form_validation');
		$this->form_validation->set_rules('info', 'Заметка', 'max_length[250]|trim');
		if ($this->form_validation->run() == true) {
			$this->db->where('id', $id)->update('favorites', array('info' => $this->input->post('info')));
		}
	}

	function delete($id) {
		$id = intval($id);
		$favorite_info = $this->db->where(array('id' => $id, 'owner_id' => $this->data['user_info']['id']))->get('favorites')->row_array();
		if (empty($favorite_info)) {
			custom_404();
		}

		$this->db->where('id', $id)->delete('favorites');
		$this->session->set_flashdata('success', 'Удаление успешно выполнено');
		redirect('favorites', 'refresh');
	}

}
