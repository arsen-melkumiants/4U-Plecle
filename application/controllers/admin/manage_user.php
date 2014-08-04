<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_user extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'users';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Все пользователи',
			'header_descr' => 'Список пользователей',
		),
		'activated'        => array(
			'header'       => 'Активированные пользователи',
			'header_descr' => 'Список пользователей',
		),
		'inactivated'      => array(
			'header'       => 'Неактивированные пользователи',
			'header_descr' => 'Список пользователей',
		),
		'cleaners'         => array(
			'header'       => 'Работники',
			'header_descr' => 'Список работников',
		),
		'clients'      => array(
			'header'       => 'Клиенты',
			'header_descr' => 'Список клиентов',
		),
		'edit'             => array(
			'header'       => 'Редактирование пользователя "%first_name %last_name"',
			'header_descr' => 'Редактирование информации о пользователе',
		),
		'payment_accounts' => array(
			'header'       => 'Платежные счета пользователя "%first_name %last_name"',
			'header_descr' => 'Список всех платежных счетов пользователя',
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}
		$this->lang->load('auth');

		$this->load->model(ADM_FOLDER.'admin_user_model');
		$this->load->model(ADM_FOLDER.'admin_order_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();

		$this->zip = $this->admin_order_model->get_all_zips();
	}

	public function index($status = false, $type = false) {
		$this->data['status'] = $status;
		$this->data['type']   = $type;

		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('first_name', array(
				'title' => 'Имя',
				'func'  => function($row, $params, $that, $CI) {
					return $row['first_name'].'&nbsp;'.$row['last_name'];
				}
		))
			->text('email', array(
				'title' => 'Email',
			))
			->date('last_login', array(
				'title' => 'Дата последней авторизации',
			))
			->date('created_on', array(
				'title' => 'Дата регистрации'
			))
			->text('is_cleaner', array(
				'title' => 'Тип',
				'func'  => function($row, $params, $that, $CI) {
					if (!empty($row['is_cleaner'])) {
						return '<span class="label label-info">Работник</span>';
					} else {
						return '<span class="label label-info">Клиент</span>';
					}
				}
		))
			->text('zip', array(
				'title' => 'Индекс',
				'func'  => function($row, $params, $that, $CI) {
					return $CI->admin_order_model->show_user_zips($row['zip']);
				}
		))
			->text('active', array(
				'title' => 'Статус',
				'func'  => function($row, $params, $that, $CI) {
					if ($row['active'] == 0) {
						return '<span class="label label-danger">Неактивированный</span>';
					} elseif ($row['active'] == 1) {
						return '<span class="label label-success">Активированный</span>';
					}
				}
		))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->btn(array(
				'func' => function($row, $params, $html, $that, $CI) {
					if (!$row['status']) {
						$params['title'] = 'Активировать';
						$params['icon'] = 'ok';
					} else {
						$params['title'] = 'Деактивировать';
						$params['icon'] = 'ban-circle';
					}
					return '<a href="'.site_url($CI->MAIN_URL.'active/'.$row['id']).'" title="'.$params['title'].'"><i class="icon-'.$params['icon'].'"></i> </a>';
				}
		))
			->create(function($CI) {
				return $CI->admin_user_model->get_all_users($CI->data['status'], $CI->data['type']);
			});

		load_admin_views();
	}

	public function inactivated() {
		$this->index(0);
	}

	public function activated() {
		$this->index(1);
	}

	public function cleaners() {
		$this->index(false, 1);
	}

	public function clients() {
		$this->index(false, 0);
	}

	public function add() {
		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE);
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$user_info = $this->admin_user_model->get_user_info($id);

		if (empty($user_info )) {
			custom_404();
		}
		set_header_info($user_info);

		$this->data['center_block'] = $this->edit_form($user_info);
		$this->upload_photo($id, true);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($user_info = false) {
		$special = array(
			'english'                 => 'Я хорошо разговариваю по-английски',
			'country_work_permission' => 'У меня есть право работать на территории РФ',
			'passport'                => 'У меня есть паспорт, который я могу предоставить при собеседовании',
			'not_agency'              => 'Я понимаю, что Plecle.com не агентство',
			'experience'              => 'У меня есть минимум 6 месяц опыта работы горничной',
			'reference'               => 'У меня есть 3 работодательские характеристики',
			'bank_account'            => 'Я понимаю, что моя работа будет оплачена на банковский счет',
			'mobile_phone'            => 'У меня есть мобильный телефон и я могу принимать и получать сообщения',
		);

		$this->load->library('form');
		return $this->form
			->checkbox('special[]', array(
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Особые требования',
				'inline'      => false,
				'inputs'      => $special
			))
			->text('first_name', array('value' => $user_info['first_name'], 'valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => lang('create_user_fname_label')))
			->text('last_name', array('value' => $user_info['last_name'], 'valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => lang('create_user_lname_label')))
			->select('gender', array('value' => $user_info['gender'], 'options' => array('male' => 'Мужской', 'female' => 'Женский'), 'valid_rules' => 'required|trim|xss_clean',  'label' => lang('create_user_gender_label')))
			->text('phone', array('value' => $user_info['phone'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural', 'label' => lang('create_user_phone_label')))
			->text('address', array('value' => $user_info['address'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('create_user_address_label')))
			->text('city', array('value' => $user_info['city'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('create_user_city_label')))
			->text('country', array('value' => $user_info['country'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('create_user_country_label')))
			->text('zip', array('value' => trim($user_info['zip'], ','), 'valid_rules' => 'required|trim|xss_clean|max_length[100]', 'label' => lang('create_user_zip_label')))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$user_info = $this->admin_user_model->get_user_info($id);

		if (empty($user_info )) {
			custom_404();
		}
		set_header_info($user_info);

		if (!empty($user_info['id'])) {
			$active = isset($user_info['active']) ? $user_info['active'] : 1;
			$active = abs($active - 1);
			$this->db->where('id', $user_info['id'])->update('users', array('active' => $active));
			$this->session->set_flashdata('success', 'Данные успешно обновлены');
		}
		redirect($this->MAIN_URL, 'refresh');
	}

	public function upload_photo($id = false, $is_called = false) {
		if (empty($id)) {
			custom_404();
		}

		$user_info = $this->admin_user_model->get_user_info($id);

		if (empty($user_info )) {
			custom_404();
		}

		$this->data['image_full_path'] = !empty($user_info['photo']) ? '/uploads/avatars/'.$user_info['photo'] : false;

		$this->load->library('form');
		$this->data['upload_form'] = $this->form
			->file('photo', array('label' => 'Фото'))
			->hidden('x1')
			->hidden('y1')
			->hidden('re_width')
			->hidden('re_height')
			->hidden('height')
			->hidden('width')
			->btn(array('value' => 'Загрузить', 'class' => 'btn-primary'))
			->create(array('upload' => true, 'action' => site_url($this->MAIN_URL.'upload_photo/'.$id)));
		if (!empty($this->data['center_block'])) {
			$this->data['center_block'] .= $this->load->view(ADM_FOLDER.'upload_js', $this->data, true);
		}
		$upload_info = $this->admin_user_model->upload_user_photo($user_info);
		if ($upload_info === true) {
			$this->session->set_flashdata('success', 'Фото успешно добавлено');
		} elseif (!empty($upload_info)) {
			$this->session->set_flashdata('danger', $upload_info);
		}

		if (empty($is_called)) {
			redirect($this->MAIN_URL.'edit/'.$id, 'refresh');
		}
	}
}
