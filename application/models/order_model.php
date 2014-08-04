<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {

	public $frequency = array(
		'once'          => 'Только один раз',
		'every_week'    => 'Еженедельно',
		'every_2_weeks' => 'Каждые две недели',
	);

	public $duration = array(
		'1' => '1 час',
		'2' => '2 часа',
		'3' => '3 часа',
		'4' => '4 часа',
	);

	public $special = array(
		'need_ironing' => 'Нужна глажка',
		'have_pets'    => 'Есть домашнее животное',
	);

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_user_order($order_id, $user_id = false, $user_type = false) {
		if (empty($user_type)) {
			if (!empty($this->data['user_info']['is_cleaner'])) {
				$user_type = 'cleaner';
			} else {
				$user_type = 'client';
			}
		}
		if (empty($user_id) && !empty($this->data['user_info'])) {
			$user_id = $this->data['user_info']['id'];
		}

		if ($user_type == 'no_cleaner') {
			if (empty($this->data['user_info']['is_cleaner'])) {
				return false;
			}
			$this->db->where('cleaner_id', 0);
			$this->db->where('(status = 2 AND start_date > '.time().')');
		} else {
			$this->db->where($user_type.'_id', $user_id);
		}
		return $this->db
			->where('id', $order_id)
			->order_by('id', 'desc')
			->get('orders')
			->row_array();
	}

	function get_order_payments($order_id) {
		return $this->db
			->where('order_id', $order_id)
			->where('status', 1)
			->order_by('id', 'desc')
			->get('payments');
	}

	function get_all_orders($status = '0', $user_id = false, $user_type = false) {
		if (empty($user_type)) {
			if (!empty($this->data['user_info']['is_cleaner'])) {
				$user_type = 'cleaner';
			} else {
				$user_type = 'client';
			}
		}
		if (empty($user_id) && !empty($this->data['user_info'])) {
			$user_id = $this->data['user_info']['id'];
		}

		if ($status == 0) {
			$this->db->where('status', 0);
			$this->db->where('start_date >', time() + 86400);
		} elseif ($status == 1) {
			$this->db->where('(status = 2 OR (status = 1 AND start_date > '.(time() + 86400).'))');
		} elseif ($status == 3) {
			$this->db->where('cleaner_id', 0);
			$this->db->where('((status = 2 AND start_date > '.time().') OR (status IN (1) AND start_date > '.(time() + 86400).'))');
		} else {
			$this->db->where('(status > 2 OR (status IN (0,1) AND start_date < '.(time() + 86400).'))');
		}

		if ($status != 3) {
			$this->db->where($user_type.'_id', $user_id);
		}

		return $this->db
			->order_by('id', 'desc')
			->get('orders');
	}

	function get_all_cleaners($zip = false) {
		if (!empty($zip)) {
			$this->db->like('zip', ','.$zip.',');
		}
		return $this->db
			->where('active', 1)
			->where('is_cleaner', 1)
			->get('users')
			->result_array();
	}

	function get_deals_count($cleaner_id) {
		return $this->db
			->select('m.id')
			->from('orders as o')
			->join('marks as m', 'o.id = m.order_id')
			->where('o.cleaner_id', $cleaner_id)
			->get()
			->num_rows();
	}

	function order_form() {
		if (!$this->ion_auth->logged_in()) {
			//------------------------------------------
			$this->form
				->text('email', array(
					'valid_rules' => 'required|trim|xss_clean|max_length[150]|valid_email',
					'label'       => lang('create_user_email_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->password('password', array(
					'valid_rules' => 'required|min_length['.$this->config->item('min_password_length', 'ion_auth').']|max_length['.$this->config->item('max_password_length', 'ion_auth').']',
					'label'       => $this->lang->line('create_user_password_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				));

			$is_login = false;
			if ($this->input->cookie('of_tab') == 'login_form') {
				$is_login = true;

				if ($this->form_validation->run() == true) {
					if (!$this->ion_auth->login($this->input->post('email'), $this->input->post('password'))) {
						$this->form->form_data[0]['params']['error'] = $this->ion_auth->errors();
						$data['login_form'] = $this->form->create(array('error_inline' => true, 'no_form_tag' => true));
					} else {
						$data['is_login'] = true;
					}
				} else {
					$data['login_form'] = $this->form->create(array('error_inline' => true, 'no_form_tag' => true));
				}
			} else {
				$data['login_form'] = $this->form->create(array('error_inline' => true, 'no_form_tag' => true));
			}

			//------------------------------------------
			$data['registration_form'] = $this->form
				->text('first_name', array(
					'valid_rules' => (!$is_login ? 'required|' : '').'trim|xss_clean|max_length[150]',
					'label'       => lang('create_user_fname_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->text('last_name', array(
					'valid_rules' => (!$is_login ? 'required|' : '').'trim|xss_clean|max_length[150]',
					'label'       => lang('create_user_lname_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->text('phone', array(
					'valid_rules' => (!$is_login ? 'required|' : '').'trim|xss_clean|max_length[100]|is_natural',
					'label'       => lang('create_user_phone_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->text('email', array(
					'valid_rules' => (!$is_login ? 'required|' : '').'trim|xss_clean|max_length[150]|valid_email|is_unique[users.email]',
					'label'       => lang('create_user_email_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->password('password', array(
					'valid_rules' => (!$is_login ? 'required|' : '').'min_length['.$this->config->item('min_password_length', 'ion_auth').']|max_length['.$this->config->item('max_password_length', 'ion_auth').']',
					'label'       => $this->lang->line('create_user_password_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->password('password_confirm', array(
					'valid_rules' => (!$is_login ? 'required|matches[password]' : ''),
					'label'       => lang('create_user_password_confirm_label'),
					'width'       => 12, 'group_class' => 'col-sm-6'
				))
				->checkbox('confirm', array(
					'valid_rules' => (!$is_login ? 'required' : ''),
					'inputs'      => array('confirm' => 'Я согласен с <a href="'.site_url('rules').'">правилами</a> сайта'),
					'width'       => 12, 'group_class' => 'col-sm-12'
				))
				->create(array('error_inline' => true, 'no_form_tag' => true));
		}

		//------------------------------------------
		$user_info = $this->ion_auth->user()->row_array();
		$data['address_form'] = $this->form
			->text('country', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => lang('create_user_country_label'),
				'width'       => 12, 'group_class' => 'col-sm-6',
				'value'       => !empty($user_info['country']) ? $user_info['country'] : 'Россия',
			))
			->text('city', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => lang('create_user_city_label'),
				'width'       => 12, 'group_class' => 'col-sm-6',
				'value'       => !empty($user_info['city']) ? $user_info['city'] : false,
			))
			->text('address', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => lang('create_user_address_label'),
				'width'       => 12, 'group_class' => 'col-sm-6',
				'value'       => !empty($user_info['address']) ? $user_info['address'] : false,
			))
			->text('zip', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',
				'label'       => lang('create_user_zip_label'),
				'width'       => 12, 'group_class' => 'col-sm-6',
				'value'       => !empty($this->data['temp_post']['zip']) ? $this->data['temp_post']['zip'] : (!empty($user_info['zip']) ? trim($user_info['zip'], ',') : false)
			))
			->create(array('error_inline' => true, 'no_form_tag' => true));

		//------------------------------------------
		$this->form
			->radio('frequency', array(
				'valid_rules' => 'required|trim',
				'label'       => 'Как часто нужна горничная?',
				'inline'      => false, 'group_class' => 'col-sm-12', 'label_width' => 6,
				'inputs'      => $this->frequency
			))
			->select('duration', array(
				'valid_rules' => 'required|trim',
				'label'       => 'На сколько времени нужна?',
				'group_class' => 'col-sm-12', 'label_width' => 6,
				'options'     => $this->duration
			))
			->date('start_date', array(
				'valid_rules' => 'required|trim',
				'label'       => 'Время начала уборки',
				'icon'        => false,
				'group_class' => 'col-sm-12', 'label_width' => 6,
				'type'        => 'd.m.Y H:i',
			))
			->checkbox('special[]', array(
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Особые требования',
				'inline'      => false, 'group_class' => 'col-sm-12', 'label_width' => 6,
				'inputs'      => $this->special
			));

		$is_late = (!empty($_POST['start_date']) && time() > strtotime($_POST['start_date']) + 86400 * 2);
		if ($is_late) {
			$this->form->form_data[2]['params']['error'] = 'Поздняя дата, выберите пожалуйста более раннюю';
		}
		$data['order_form'] = $this->form->create(array('error_inline' => true, 'no_form_tag' => true));

		//------------------------------------------
		$data['commnet_form'] = $this->form
			->textarea('comment', array(
				'valid_rules' => 'trim|xss_clean',
				'no_editor'   => true, 'rows' => 5, 'class' => 'col-sm-12', 'group_class' => 'row', 'full_width' => true,
			))
			->checkbox('need_detergents', array(
				'valid_rules' => 'trim|xss_clean',
				'inputs'      => array('need_detergents' => 'Нужно моющее средство'),
				'width'       => 12, 'group_class' => 'row'
			))
			->create(array('error_inline' => true, 'no_form_tag' => true));

		return $this->load->view('orders/order_form', $data, true);
	}

	function request_form() {
		$user_info = $this->ion_auth->user()->row_array();
		$html_form = '<h4 class="title">Форма запроса горничной</h4>';
		$html_form .= $this->form
			->text('email', array(
				'value'       => !empty($user_info['email']) ? $user_info['email'] : false,
				'valid_rules' => 'required|trim|xss_clean|max_length[150]|valid_email',
				'label'       => lang('create_user_email_label'),
				'width'       => 12, 'group_class' => 'col-sm-6',
			))
			->text('zip', array(
				'value'       => !empty($this->data['temp_post']['zip']) ? $this->data['temp_post']['zip'] : (!empty($user_info['zip']) ? trim($user_info['zip'], ',') : false),
				'valid_rules' => 'required|trim|xss_clean|max_length[50]',
				'label'       => lang('create_user_zip_label'),
				'width'       => 12, 'group_class' => 'col-sm-6'
			))
			->btn(array(
				'value' => 'Отправить запрос',
				'class' => 'btn btn-block btn-primary',
			))
			->create(array('error_inline' => true, 'btn_width' => 6));
		return $html_form;
	}

	function send_mail($email, $subject, $mail_view, $email_info){
		$this->load->library('email', array('mailtype'  => 'html'));
		$this->email->from(SITE_EMAIL, SITE_NAME);
		$this->email->to($email);
		$this->email->cc(SITE_EMAIL);
		$this->email->subject($subject);
		$this->email->message($this->load->view('email/'.$mail_view, $email_info ,true));
		$this->email->send();
	}
}
