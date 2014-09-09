<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Order_model extends CI_Model {

	public $mrh_login = 'plecle';

	public $mrh_pass1 = 'evsY7kHJWTCWtTXTdk';

	public $mrh_pass2 = 'ayk75VeIlIBwFp2XrH';

	public $frequency = array(
		'once'          => 'Только один раз',
		'every_week'    => 'Еженедельно',
		'every_2_weeks' => 'Каждые две недели',
	);

	public $duration = array(
		'1'   => '1 час',
		'1.5' => '1.5 часа',
		'2'   => '2 часа',
		'2.5' => '2.5 часа',
		'3'   => '3 часа',
		'3.5' => '3.5 часа',
		'4'   => '4 часа',
		'4.5' => '4.5 часа',
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
			$this->db->where('cleaner_id', 0);
			$this->db->where_in('status', array(0,1,2));
			$this->db->where('start_date >', time() + 86400);
		} elseif ($status == 1) {
			$this->db->where('cleaner_id !=', 0);
			$this->db->where('(status = 2 OR (status = 1 AND start_date > '.(time() + 86400).'))');
		} elseif ($status == 3) {
			$this->db->where('cleaner_id', 0);
			$this->db->where('((status = 2 AND start_date > '.time().') OR (status IN (1) AND start_date > '.(time() + 86400).') )');
		} else {
			$this->db->where('(status > 2 OR (status IN (0,1) AND start_date < '.(time() + 86400).') OR (status = 2 AND start_date < '.time().' AND cleaner_id = 0))');
		}

		if ($status != 3) {
			$this->db->where($user_type.'_id', $user_id);
		}

		return $this->db
			->order_by('id', 'desc')
			->get('orders');
	}

	function get_all_cleaners($zip = false) {
		$sql = 'SELECT u.*, SUM(IFNULL(m.amount,0)) as rating
			FROM users AS u
			LEFT JOIN marks AS m ON u.id = m.cleaner_id
			WHERE u.active = 1
			AND u.is_cleaner = 1';
		if (!empty($zip)) {
			if (!is_array($zip)) {
				$zip = array($zip);
			}
			$sql_like = array();
			foreach($zip as $key => $item) {
				if (empty($item)) {
					continue;
				}
				$sql_like[] = 'u.zip LIKE \'%,'.$item.',%\'';
			}
			$sql .= ' AND ('.implode(' OR ', $sql_like).')';
		}
		$sql .= ' GROUP BY u.id ORDER BY rating desc';
		return $this->db
			->query($sql)
			->result_array();
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

		$is_late = (!empty($_POST['start_date']) && time() + (86400 * 2) > strtotime($_POST['start_date']));
		if ($is_late) {
			$this->form->form_data[2]['params']['error'] = 'Поздняя дата, выберите пожалуйста более раннюю (минимум за два дня)';
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

	function make_payment_url($order_info = false) {
		if (empty($order_info) || !is_array($order_info)) {
			return false;
		}

		$pay_time = ($order_info['start_date'] - 86400) > time();
		if ($pay_time && ($order_info['status'] == 0 || $order_info['status'] == 1)) {
			$payment_info = $this->db->where(array('order_id' => $order_info['id'], 'status' => 0))->get('payments')->row_array();
			if (empty($payment_info)) {
				$this->db->insert('payments', array(
					'order_id'            => $order_info['id'],
					'price_per_hour'      => $order_info['price_per_hour'],
					'cleaner_price'       => $order_info['cleaner_price'],
					'detergent_price'     => $order_info['detergent_price'],
					'total_price'         => $order_info['total_price'],
					'total_cleaner_price' => $order_info['total_cleaner_price'],
					'add_date'            => time(),
					'status'              => 0,
				));
				$payment_id = $this->db->insert_id();
			} else {
				$this->db->where('id', $payment_info['id'])->update('payments', array(
					'order_id'            => $order_info['id'],
					'price_per_hour'      => $order_info['price_per_hour'],
					'cleaner_price'       => $order_info['cleaner_price'],
					'detergent_price'     => $order_info['detergent_price'],
					'total_price'         => $order_info['total_price'],
					'total_cleaner_price' => $order_info['total_cleaner_price'],
					'add_date'            => time(),
					'status'              => 0,
				));
				$payment_id = $payment_info['id'];
			}
			// your registration data
			$mrh_login = $this->mrh_login;
			$mrh_pass1 = $this->mrh_pass1;
			// order properties
			$inv_id    = $payment_id;
			$inv_desc  = 'Оплата сделки №'.$order_info['id'];
			$out_summ  = $order_info['total_price'];

			// build CRC value
			$crc  = md5("$mrh_login:$out_summ:$inv_id:$mrh_pass1");
			$culture  = 'ru';
			$encoding = 'utf-8';

			// build URL
			$url_params = array(
				'MrchLogin='     .$mrh_login,
				'OutSum='        .$out_summ,
				'InvId='         .$inv_id,
				'Desc='          .$inv_desc,
				'SignatureValue='.$crc,
				'Culture='       .$culture,
				'Encoding='      .$encoding,
			);
			$pay_url = 'https://auth.robokassa.ru/Merchant/Index.aspx?'.implode('&', $url_params);
			//$pay_url = site_url('orders/pay/'.$order_info['id']);
			return $pay_url;
		} elseif ($order_info['status'] == 2) {
			//$this->session->set_flashdata('danger', 'Оплата уже совершена');
		} else {
			//$this->session->set_flashdata('danger', 'Оплата не может быть произведена');
		}
		return false;
	}

	function get_total_payments($type = false, $user_type = false) {
		if ($type == 'month') {
			$this->db->where('p.add_date >= '.strtotime(date('Y-m-1 00:00')));
		} elseif ($type == 'year') {
			$this->db->where('p.add_date >= '.strtotime(date('Y-1-1 00:00')));
		}

		if ($user_type == 'client') {
			$this->db->where('o.client_id', $this->data['user_info']['id']);
			$this->db->select('SUM(p.total_price) as total_price, o.fine_price');
		} elseif ($user_type == 'cleaner') {
			$this->db->where('o.cleaner_id', $this->data['user_info']['id']);
			$this->db->select('SUM(p.total_cleaner_price) as total_price, o.fine_price');
		}

		$payment_info = $this->db
			->from('payments AS p')
			->join('orders AS o', 'o.id = p.order_id')
			->where('p.status', 1)
			->group_by('o.id')
			->get()
			->result_array()
			;
		if(empty($payment_info)) {
			return 0;
		}

		$total_sum = 0;
		foreach ($payment_info as $item) {
			$total_sum += floatval($item['total_price']) - floatval($item['fine_price']);
		}
		return $total_sum;
	}

	function get_reviews_statistic($user_type = false, $user_id = false) {
		if (empty($user_id)) {
			$user_id = $this->data['user_info']['id'];
		}

		if ($user_type == 'client') {
			$this->db->where('o.client_id', $user_id);
		} elseif ($user_type == 'cleaner') {
			$this->db->where('m.cleaner_id', $user_id);
		}


		$result_array = array(
			'total'   => 0,
			'success' => 0,
			'fail'    => 0,
			'rating'  => 0,
		);

		$marks_info = $this->db->select('m.*')
			->from('marks AS m')
			->join('orders AS o', 'o.id = m.order_id')
			->where('m.status', 1)
			->get()
			->result_array();
		if (empty($marks_info)) {
			return $result_array;
		}

		foreach ($marks_info as $item) {
			$result_array['total']++;
			$result_array['rating'] += $item['amount'];
			if ($item['mark'] == 'positive') {
				$result_array['success']++;
			} else {
				$result_array['fail']++;
			}
		}
		return $result_array;
	}

	function get_completed_orders($user_type = false, $user_id = false) {
		if (empty($user_id)) {
			$user_id = $this->data['user_info']['id'];
		}

		if ($user_type == 'client') {
			$this->db->where('client_id', $user_id);
		} elseif ($user_type == 'cleaner') {
			$this->db->where('cleaner_id', $user_id);
		}

		$this->db->where('(status > 2 OR (status IN (0,1) AND start_date < '.(time() + 86400).') OR (status = 2 AND start_date < '.time().' AND cleaner_id = 0))');

		$result_array = array(
			'total'   => 0,
			'success' => 0,
			'fail'    => 0,
		);

		$orders_info = $this->db->get('orders')->result_array();
		if (empty($orders_info)) {
			return $result_array;
		}

		foreach ($orders_info as $row) {
			if (in_array($row['status'], array(0,1)) && $row['start_date'] < 86400 + time()) {
				$result_array['fail']++;
			} elseif (in_array($row['status'], array(4,5))) {
				$result_array['fail']++;
			} elseif (!$row['cleaner_id'] && $row['status'] == 2 && $row['start_date'] < time()) {
				$result_array['fail']++;
			} elseif ($row['status'] == 3 && $row['last_mark'] == 'positive') {
				$result_array['success']++;
			} elseif ($row['status'] == 3 && $row['last_mark'] == 'negative') {
				$result_array['fail']++;
			} elseif ($row['status'] == 3) {
				$result_array['success']++;
			}
			$result_array['total']++;
		}
		return $result_array;
	}

	function get_all_reviews($user_id = false) {
		return $this->db->select('m.*, u.first_name, u.last_name, u.photo')
			->from('marks AS m')
			->join('orders AS o', 'o.id = m.order_id', 'left')
			->join('users AS u', 'u.id = o.client_id')
			->where('m.status', 1)
			->where('m.cleaner_id', $user_id)
			->order_by('m.add_date', 'desc')
			->get();
	}

	function get_cleaner_reviews($user_id = false, $limit = false) {
		if (!empty($limit)) {
			$this->db->limit($limit);
		}
		return $this->get_all_reviews($user_id)->result_array();
	}
}
