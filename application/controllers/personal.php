<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Personal extends CI_Controller {

	function __construct() {
		parent::__construct();
		$this->load->library(array(
			'ion_auth',
			'form',
			'form_validation',
		));
		$this->load->helper('url');

		// Load MongoDB library instead of native db driver if required
		$this->config->item('use_mongodb', 'ion_auth') ?
			$this->load->library('mongo_db') :
			$this->load->database();

		$this->form_validation->set_error_delimiters($this->config->item('error_start_delimiter', 'ion_auth'), $this->config->item('error_end_delimiter', 'ion_auth'));

		$this->lang->load('auth');
		$this->load->helper('language');

		$this->load->model(array(
			'menu_model',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('main');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	//redirect if needed, otherwise display the user list
	function index() {
		return false;
		if (!$this->ion_auth->logged_in()) {
			redirect(ADM_URL.'auth/login', 'refresh');
		} elseif (!$this->ion_auth->is_admin())	{
			return show_error(lang('admin_permission'));
		} else {
			redirect('', 'refresh');
		}
	}

	//log the user in
	function login() {
		if ($this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->data['title'] = $this->data['header'] = lang('login_heading');

		$this->form
			->text('identity', array(
				'label'       => 'Email',
				'valid_rules' => 'required|xss_clean',
				'width'       => 12,
			))
			->password('password', array(
				'label'       => lang('login_password_label'),
				'valid_rules' => 'required|xss_clean',
				'width'       => 12,
			))
			->btn(array(
				'value'      => lang('login_submit_btn'),
				'class'      => 'btn-primary btn-block',
			))
			->link(array('name' => lang('login_forgot_password'), 'class' => 'btn btn-link btn-block', 'href' => site_url('personal/forgot_password')))
			;

		if ($this->form_validation->run() == true) {
			$remember = (bool) $this->input->post('remember');
			if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
				$this->session->set_flashdata('success', $this->ion_auth->messages());
				if ($this->input->is_ajax_request()) {
					echo 'refresh';exit;
				}
				redirect('', 'refresh');
			} else {
				//$this->session->set_flashdata('danger', $this->ion_auth->errors());
				$this->form->form_data[0]['params']['error'] = $this->ion_auth->errors();
				$this->data['center_block'] = $this->form->create(array(
					'action'       => current_url(),
					'error_inline' => 'true',
					'btn_offset'   => 0,
					'class'        => !$this->input->is_ajax_request() ? 'col-md-4 col-md-offset-4' : false,
				));
				load_views();
			}
		} else {
			$this->data['center_block'] = $this->form->create(array(
				'action'       => current_url(),
				'error_inline' => 'true',
				'btn_offset'   => 0,
				'class'        => !$this->input->is_ajax_request() ? 'col-md-4 col-md-offset-4' : false,
			));
			load_views();
		}
	}

	//log the user out
	function logout()
	{
		$logout = $this->ion_auth->logout();
		$this->session->set_flashdata('success', $this->ion_auth->messages());
		redirect('', 'refresh');
	}

	//activate the user
	function activate($id, $code=false)
	{
		if ($code !== false) {
			$activation = $this->ion_auth->activate($id, $code);
		} else if ($this->ion_auth->is_admin())	{
			$activation = $this->ion_auth->activate($id);
		}

		if ($activation) {
			//redirect them to the auth page
			$this->session->set_flashdata('success', $this->ion_auth->messages());
			redirect("", 'refresh');
		} else {
			//redirect them to the forgot password page
			$this->session->set_flashdata('danger', $this->ion_auth->errors());
			redirect('personal/forgot_password', 'refresh');
		}
	}

	function registration()	{
		$this->data['title'] = $this->data['header'] = lang('create_user_heading');

		if ($this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->data['options'] = array(
			'english'                 => 'Я хорошо разговариваю по-английски',
			'country_work_permission' => 'У меня есть право работать на территории РФ',
			'passport'                => 'У меня есть паспорт, который я могу предоставить при собеседовании',
			'not_agency'              => 'Я понимаю, что Plecle.com не агентство',
			'experience'              => 'У меня есть минимум 6 месяц опыта работы горничной',
			'reference'               => 'У меня есть 3 работодательские характеристики',
			'bank_account'            => 'Я понимаю, что моя работа будет оплачена на банковский счет',
			'mobile_phone'            => 'У меня есть мобильный телефон и я могу принимать и получать сообщения',
		);
		foreach ($this->data['options'] as $key => $item) {
			if (!empty($_POST['options'][$key])) {
				$this->data['result_options'][$key] = 1;
			} else {
				$this->data['result_options'][$key] = 0;
			}
		}

		$this->data['user_info_form'] = $this->form
			->text('first_name', array('valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => lang('create_user_fname_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('last_name', array('valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => lang('create_user_lname_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('phone', array('valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',  'label' => lang('create_user_phone_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('email', array('valid_rules' => 'required|trim|xss_clean|max_length[150]|valid_email|is_unique[users.email]',  'label' => lang('create_user_email_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->select('gender', array('options' => array('male' => 'Мужской', 'female' => 'Женский'), 'valid_rules' => 'required|trim|xss_clean',  'label' => lang('create_user_gender_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->func(function($params, $CI) {
				$CI->data['params'] = $params;
				return $CI->load->view('date_form', $CI->data, true);
			}, array('label' => lang('create_user_birth_label')))
				->password('password', array('valid_rules' => 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']',  'label' => $this->lang->line('create_user_password_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
				->password('password_confirm', array('valid_rules' => 'required|matches[password]',  'label' => lang('create_user_password_confirm_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
				->create(array('error_inline' => true, 'no_form_tag' => true));

		$this->data['address_form'] = $this->form
			->text('country', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_country_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('city', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_city_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('address', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_address_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->text('zip', array('valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_zip_label'), 'width' => 12, 'group_class' => 'col-sm-6'))
			->create(array('error_inline' => true, 'no_form_tag' => true));

		$this->data['confirm'] = $this->form
			->checkbox('confirm', array('valid_rules' => 'required', 'inputs' => array('confirm' => 'Я согласен с <a href="'.site_url('rules').'">правилами</a> сайта')))
			->create(array('error_inline' => true, 'no_form_tag' => true));

		if ($this->form_validation->run() == true) {
			$username = $this->input->post('first_name').'_'.$this->input->post('last_name');
			$email    = strtolower($this->input->post('email'));
			$password = $this->input->post('password');

			$birth = strtotime(
				intval($this->input->post('year')).'-'.
				intval($this->input->post('month')).'-'.
				intval($this->input->post('day'))
			);

			$additional_data = array(
				'first_name' => $this->input->post('first_name'),
				'last_name'  => $this->input->post('last_name'),
				'gender'     => $this->input->post('gender'),
				'birth'      => $birth,
				'country'    => $this->input->post('country'),
				'city'       => $this->input->post('city'),
				'address'    => $this->input->post('address'),
				'zip'        => $this->input->post('zip'),
				'phone'      => $this->input->post('phone'),
				'extra'      => json_encode($this->data['result_options']),
				'is_cleaner' => 1,
			);
		}

		if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {
			$this->session->set_flashdata('success', $this->ion_auth->messages());
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect("", 'refresh');
		} else {
			$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

			$this->load->view('header', $this->data);
			$this->load->view('reg_cleaners', $this->data);
			$this->load->view('footer', $this->data);
		}
	}

	function edit_profile()	{
		$this->data['title'] = $this->data['header'] = lang('edit_user_heading');

		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->data['left_block'] = $this->load->view('profile/menu', $this->data, true);

		$user_info     = $this->ion_auth->user()->row_array();
		$id            = $user_info['id'];
		$groups        = $this->ion_auth->groups()->result_array();
		$currentGroups = $this->ion_auth->get_users_groups($id)->result();

		$amount_list = array_flip(array('0','130','200','300','400','500','600','700','800','900','1000','1500','2000','2500','3000'));
		foreach ($amount_list as $key => $value) {
			$amount_list[$key] = $key.' $';
		}

		if (isset($_POST['payment_amount']) && !isset($amount_list[$_POST['payment_amount']])) {
			$_POST['payment_amount'] = '';
		}

		$this->form
			->text('username', array('value' => $user_info['username'], 'valid_rules' => 'required|trim|xss_clean|max_length[150]',  'label' => $this->lang->line('create_user_fname_label')))
			->text('email', array('value' => $user_info['email'], 'valid_rules' => 'required|trim|xss_clean|max_length[150]|valid_email',  'label' => lang('create_user_email_label')))
			->text('company', array('value' => $user_info['company'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_company_label')))
			->text('address', array('value' => $user_info['address'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_address_label')))
			->text('city', array('value' => $user_info['city'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_city_label')))
			->text('state', array('value' => $user_info['state'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_state_label')))
			->text('country', array('value' => $user_info['country'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]',  'label' => lang('create_user_country_label')))
			->text('zip', array('value' => $user_info['zip'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',  'label' => lang('create_user_zip_label')))
			->text('phone', array('value' => $user_info['phone'], 'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',  'label' => lang('create_user_phone_label')))
			->text('url', array('value' => $user_info['url'], 'valid_rules' => 'trim|xss_clean|max_length[100]',  'label' => lang('create_user_url_label')))
			->separator()
			->password('password', array('label' => $this->lang->line('edit_user_password_label')))
			->password('password_confirm', array('label' => $this->lang->line('edit_user_password_confirm_label')));

		if ($user_info['is_seller']) {
			$this->form
				->separator('<h4>'.lang('finance_payment_info').'</h4>')
				->select('payment_name', array(
					'value'       => $user_info['payment_name'],
					'valid_rules' => 'required|trim|xss_clean',
					'label'       => lang('finance_account_name'),
					'options'     => array('Webmoney' => 'Webmoney', 'Paxum' => 'Paxum'),
				))
				->text('payment_number', array('value' => $user_info['payment_number'], 'valid_rules' => 'required|trim|xss_clean|max_length[70]', 'label' => lang('finance_account_number')))
				->select('payment_amount', array(
					'value'       => $user_info['payment_amount'],
					'valid_rules' => 'required|trim|xss_clean',
					'label'       => lang('product_amount'),
					'options'     => $amount_list,
				))
				;
		}

		if (isset($_POST) && !empty($_POST))
		{
			// do we have a valid request?
			if ($this->_valid_csrf_nonce() === FALSE || $id != $this->input->post('id')) {
				show_error($this->lang->line('error_csrf'));
			}

			$data = array(
				'username'       => $this->input->post('username'),
				'email'          => $this->input->post('email'),
				'company'        => $this->input->post('company'),
				'address'        => $this->input->post('address'),
				'city'           => $this->input->post('city'),
				'state'          => $this->input->post('state'),
				'country'        => $this->input->post('country'),
				'zip'            => $this->input->post('zip'),
				'phone'          => $this->input->post('phone'),
				'url'            => $this->input->post('url'),
			);

			//Update the groups user belongs to
			$groupData = $this->input->post('groups');
			if (isset($groupData) && !empty($groupData)) {
				$this->ion_auth->remove_from_group('', $id);
				foreach ($groupData as $grp) {
					$this->ion_auth->add_to_group($grp, $id);
				}
			}

			//update the password if it was posted
			if ($this->input->post('password'))	{
				$this->form_validation->set_rules('password', $this->lang->line('edit_user_validation_password_label'), 'required|min_length[' . $this->config->item('min_password_length', 'ion_auth') . ']|max_length[' . $this->config->item('max_password_length', 'ion_auth') . ']|matches[password_confirm]');
				$this->form_validation->set_rules('password_confirm', $this->lang->line('edit_user_validation_password_confirm_label'), 'required');
				$this->form_validation->run();
				$this->form->form_data[10]['params']['error'] = form_error('password');
				$this->form->form_data[11]['params']['error'] = form_error('password_confirm');

				$data['password'] = $this->input->post('password');
			}

			if ($this->form_validation->run() === TRUE) {
				$this->ion_auth->update($user_info['id'], $data);
				$this->session->set_flashdata('success', lang('profile_changed_success'));
				redirect(current_url(), 'refresh');
			}
		}

		//display the edit user form
		$this->data['csrf'] = $this->_get_csrf_nonce();
		$this->form
			->hidden(key($this->data['csrf']), $this->data['csrf'][key($this->data['csrf'])])
			->hidden('id', $id);


		//set the flash data error message if there is one
		$this->data['message'] = (validation_errors() ? validation_errors() : ($this->ion_auth->errors() ? $this->ion_auth->errors() : $this->session->flashdata('message')));

		//pass the user to the view
		$this->data['user'] = $user_info;
		$this->data['groups'] = $groups;
		$this->data['currentGroups'] = $currentGroups;


		$this->data['center_block'] = $this->form
			->btn(array('value' => lang('edit_user_submit_btn')))
			->create(array('action' => current_url(), 'error_inline' => 'true'));
		load_views();
	}

	function forgot_password() {
		$this->data['header'] = $this->data['title'] = lang('forgot_password_heading');

		if ($this->config->item('identity', 'ion_auth') == 'username') {
			$label = 'forgot_password_username_identity_label';
			$email_rule = '';
		} else {
			$label = 'forgot_password_email_identity_label';
			$email_rule = '|valid_email';
		}
		$label = $this->lang->line($label);
		$this->form
			->text('email', array(
				'valid_rules' => 'required|trim|xss_clean'.$email_rule,
				'label'       => $label,
				'width'       => 12,
			))
			->btn(array(
				'value'      => lang('forgot_password_submit_btn'),
				'class'      => 'btn-primary btn-block',
			));

		if ($this->form_validation->run() == false) {
			$this->form->form_data[0]['params']['error'] .= $this->session->flashdata('message');
			$this->data['center_block'] = $this->form->create(array(
				'action'       => current_url(),
				'error_inline' => true,
				'btn_offset'   => 0,
				'class'        => !$this->input->is_ajax_request() ? 'col-md-4 col-md-offset-4' : false,
			));
			load_views();
		} else {
			$identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
			if(empty($identity)) {
				$this->ion_auth->set_message('forgot_password_email_not_found');
				$this->session->set_flashdata('danger', $this->ion_auth->messages());
				redirect('personal/forgot_password', 'refresh');
			}

			//run the forgotten password method to email an activation code to the user
			$forgotten = $this->ion_auth->forgotten_password($identity->{$this->config->item('identity', 'ion_auth')});

			if ($forgotten) {
				$this->session->set_flashdata('success', $this->ion_auth->messages());
				redirect('', 'refresh'); //we should display a confirmation page here instead of the login page
			} else {
				$this->session->set_flashdata('danger', $this->ion_auth->errors());
				redirect('personal/forgot_password', 'refresh');
			}
		}
	}

	function _get_csrf_nonce() {
		$this->load->helper('string');
		$key   = random_string('alnum', 8);
		$value = random_string('alnum', 20);
		$this->session->set_flashdata('csrfkey', $key);
		$this->session->set_flashdata('csrfvalue', $value);

		return array($key => $value);
	}

	function _valid_csrf_nonce() {
		if ($this->input->post($this->session->flashdata('csrfkey')) !== FALSE &&
			$this->input->post($this->session->flashdata('csrfkey')) == $this->session->flashdata('csrfvalue'))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}

	function _render_page($view, $data = null, $render = false)	{
		$this->viewdata = (empty($data)) ? $this->data: $data;
		$view_html = $this->load->view($view, $this->viewdata, $render);
		if (!$render) return $view_html;
	}

	function cleaner_profile($user_id = false) {
		//$this->data['title'] = $this->data['header'] = 'Профиль работника';
		$user_id = intval($user_id);
		if (empty($user_id)) {
			custom_404();
		}

		$this->data['cleaner_info'] = $this->ion_auth->user($user_id)->row_array();


		$this->data['center_block'] = $this->load->view('orders/cleaner_profile', $this->data, true);
		$this->load->view('ajax', $this->data);
	}


	function make_order() {
		$this->data['user_info'] = $this->ion_auth->user()->row_array();
		if ($this->ion_auth->logged_in() && $this->data['user_info']['is_cleaner']) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('', 'refresh');
		}

		$this->load->model('order_model');
		$this->data['cleaners'] = $this->order_model->get_all_cleaners($this->input->post('zip'));

		if (!isset($_POST['duration'])) {
			$this->data['temp_post']['zip'] = $this->input->post('zip');
			$_POST = array();
		}
		$detergent_price = $this->input->post('detergents') ? DETERGENT_PRICE : 0;


		$this->data['title'] = $this->data['header'] = 'Создание заявки';
		$this->data['center_block'] = $this->order_model->order_form();

		if ($this->form_validation->run() == FALSE) {

			$this->data['right_info'] = array(
				'title'       => 'Ваш профиль',
				'info_array'  => array(
					'Индекс'          => !empty($this->data['temp_post']['zip']) ? $this->data['temp_post']['zip'] : $this->input->post('zip'),
					'Дата'            => date('d.m.Y'),
					'Время'           => date('H:i'),
					'Частота'         => isset($this->order_model->frequency[$this->input->post('frequency')]) ? $this->order_model->frequency[$this->input->post('frequency')] : false,
					'Рабочие часы'    => isset($this->order_model->duration[$this->input->post('duration')]) ? $this->order_model->duration[$this->input->post('duration')] : false,
					'Цена за час'     => PRICE_PER_HOUR,
					'Моющие средства' => $detergent_price,
					'<b>Итого</b>'    => '<b>'.(PRICE_PER_HOUR * $this->input->post('duration') + $detergent_price).'</b>'
				),
			);
			$this->load->view('header', $this->data);
			$this->load->view('orders/cleaner_list', $this->data);
			$this->load->view('orders/order_page', $this->data);
			$this->load->view('footer', $this->data);
		} else {
			if (!$this->ion_auth->logged_in()) {
				$username = $this->input->post('first_name').'_'.$this->input->post('last_name');
				$email    = strtolower($this->input->post('email'));
				$password = $this->input->post('password');

				$additional_data = array(
					'first_name' => $this->input->post('first_name'),
					'last_name'  => $this->input->post('last_name'),
					'country'    => $this->input->post('country'),
					'city'       => $this->input->post('city'),
					'address'    => $this->input->post('address'),
					'zip'        => $this->input->post('zip'),
					'phone'      => $this->input->post('phone'),
					'is_cleaner' => 0,
				);
				$user_id = $this->ion_auth->register($username, $password, $email, $additional_data);
				$auto_reg = true;
			} else {
				$user_id = $this->ion_auth->user()->row()->id;
			}

			$info = array(
				'client_id'       => $user_id,
				'price_per_hour'  => PRICE_PER_HOUR,
				'detergent_price' => $detergent_price,
				'total_price'     => PRICE_PER_HOUR * $this->input->post('duration') + $detergent_price,
				'frequency'       => $this->input->post('frequency'),
				'duration'        => $this->input->post('duration'),
				'need_ironing'    => intval($this->input->post('need_ironing')),
				'have_pets'       => intval($this->input->post('have_pets')),
				'need_detergents' => $this->input->post('need_detergents'),
				'comment'         => $this->input->post('comment'),
				'country'         => $this->input->post('country'),
				'city'            => $this->input->post('city'),
				'address'         => $this->input->post('address'),
				'zip'             => $this->input->post('zip'),
				'start_date'      => strtotime($this->input->post('start_date')),
				'add_date'        => time(),
				'last_mark'       => '',
				'status'          => 0,
			);
			$this->db->trans_commit();

			$this->db->insert('orders', $info);
			$order_id = $this->db->insert_id();

			$email_info = array(
				'order_id'  => $order_id,
				'auto_reg'  => $auto_reg,
				'email'     => $user_data['order_info']['email'],
			);
			$this->session->set_flashdata('success', 'Ваша завяка успешно создана');
			redirect();
		}
	}
}
