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

	public $add_durations = array();

	function __construct() {
		parent::__construct();
		$this->load->database();

		$this->add_durations = $this->db->select('id, name, hours')->where('status', 1)->get('order_options')->result_array();
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
			$this->db->where('o.cleaner_id', 0);
			$this->db->where('(o.status = 2 AND o.start_date > '.time().')');
		} else {
			$this->db->where('o.'.$user_type.'_id', $user_id);
		}
		return $this->db
			->select('o.*, i.status as invite_status, i.id as invite_id, i.read as invite_read')
			->from('orders as o')
			->join('order_invites as i', 'o.id = i.order_id AND i.cleaner_id = '.$user_id, 'left')
			->where('o.id', $order_id)
			->order_by('o.id', 'desc')
			->get()
			->row_array();
	}

	function get_order_payments($order_id) {
		return $this->db
			->where('order_id', $order_id)
			->where('status', 1)
			->order_by('id', 'desc')
			->get('payments');
	}

	function get_all_orders($status = '0', $user_id = false, $user_type = false, $limit = false) {
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
			$this->db
				->where('o.cleaner_id', 0)
				->where_in('o.status', array(0,1,2))
				->where('o.start_date >', time() + 86400);
		} elseif ($status == 1) {
			$this->db
				->where('o.cleaner_id !=', 0)
				->where('(o.status = 2 OR (o.status = 1 AND o.start_date > '.(time() + 86400).'))');
		} elseif ($status == 3) {
			$this->db
				->where('o.cleaner_id', 0)
				->where('((o.status = 2 AND o.start_date > '.time().') OR (o.status  = 1 AND o.start_date > '.(time() + 86400).') )')
				->where('((o.recommended) = 0 OR (o.recommended = 1 AND o.add_date + '.(3600 * 3).' < '.time().'))');
		} elseif ($status == 4) {
			$this->db
				->where('o.cleaner_id', 0)
				->where('((o.status = 2 AND o.start_date > '.time().') OR (o.status  = 1 AND o.start_date > '.(time() + 86400).') )')
				->join('order_invites AS i', 'o.id = i.order_id AND i.status = 0 AND i.cleaner_id = '.$user_id, 'inner');
		} else {
			$this->db->where('(o.status > 2 OR (o.status IN (0,1) AND o.start_date < '.(time() + 86400).') OR (o.status = 2 AND o.start_date < '.time().' AND o.cleaner_id = 0))');
		}

		if (!in_array($status, array(3,4))) {
			$this->db->where('o.'.$user_type.'_id', $user_id);
		}


		if (!empty($limit)) {
			$this->db->limit($limit);
		}

		return $this->db
			->select('o.*, u.photo as photo, u.id as user_id, COUNT(m.id) as unread')
			->from('orders as o')
			->join('order_messages AS m', 'o.id = m.order_id AND m.reciever_id = '.$user_id.' AND m.read = 0', 'left')
			->join('users AS u', 'u.id = o.'.($user_type == 'client' ? 'cleaner' : 'client').'_id', 'left')
			->order_by('o.id', 'desc')
			->group_by('o.id')
			->get();
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

	function order_table($status = 0, $limit = 0) {
		$this->table_status = $status;
		$this->order_limit = $limit;

		$status_labels = array(
			'0' => 'Заявки на сделки',
			'1' => 'Активные сделки',
			'2' => 'Завершенные сделки',
			//'3' => 'Эти сделки должны Вас заинтересовать',
			//'4' => 'Вам предлагают',
		);

		$page_names = array(
			'0' => 'created_page',
			'1' => 'active_page',
			'2' => 'completed_page',
			'3' => 'request_page',
			'4' => 'invite_page',
		);

		$this->table
			->text('id', array(
				'title' => 'Номер',
				'func'  => function($row, $params, $that, $CI) {
					$row['is_cleaner'] = $CI->data['user_info']['is_cleaner'];
					return $CI->load->view('orders/line_info', $row, true);
				}
		))
			->text('comment', array(
				'title' => 'Номер',
				'width' => '37%',
				'func'  => function($row, $params, $that, $CI) {
					if (in_array($row['status'], array(0,1)) && $row['start_date'] < 86400 + time()) {
						return '<span class="label label-danger"><i class="icon_frown"></i>Сделка не состоялась</span>';
					} elseif (in_array($row['status'], array(4,5))) {
						return '<span class="label label-danger"><i class="icon_frown"></i>Сделка отменена</span>';
					} elseif (!$row['cleaner_id'] && $row['status'] == 2 && $row['start_date'] > time() && $CI->data['user_info']['is_cleaner']) {
						return '<span class="label label-primary"><i class="icon_info"></i>Подробнее</span>';
						//return '<a href="'.site_url('orders/accept/'.$row['id']).'" class="btn btn-primary">Взяться</a>';
					} elseif (!$row['cleaner_id'] && $row['status'] == 2 && $row['start_date'] < time()) {
						return '<span class="label label-danger"><i class="icon_frown"></i>Сделка отменена (отсутствует горничная)</span>';
					} elseif (in_array($row['status'], array(0,1))) {
						return '<span class="label label-warning"><i class="icon_time"></i>Ожидаем оплаты</span>';
					} elseif (!$row['cleaner_id']) {
						return '<span class="label label-warning"><i class="icon_time"></i>Ожидаем горничную</span>';
					} elseif ($row['status'] == 2 && $row['start_date'] + (3600 * $row['duration']) > time()) {
						return '<span class="label label-primary"><i class="icon_info"></i>Сделка в процессе</span>';
					} elseif ($row['status'] == 2 && $row['start_date'] + (3600 * $row['duration']) < time()) {
						return '<span class="label label-warning"><i class="icon_time"></i>Ожидаем оценку уборки</span>';
					} elseif ($row['status'] == 3 && $row['last_mark'] == 'positive') {
						return '<span class="label label-success"><i class="icon_ok"></i>Уборка успешно завершена</span>';
					} elseif ($row['status'] == 3 && $row['last_mark'] == 'negative') {
						return '<span class="label label-danger"><i class="icon_frown"></i>Плохое качество уборки</span>';
					}
					return '<span class="label label-primary"><i class="icon_info"></i>Подробнее</span>';
				}
		));


		$result_html = $this->table
			->create(function($CI) {
				return $CI->order_model->get_all_orders($CI->order_model->table_status, false, false, $CI->order_model->order_limit);
			}, array(
				'no_header' => true,
				'class'     => 'list orders',
				'page_name' => $page_names[$status],
				'limit'     => 5,
				'tr_func'   => function($row, $table_params, $that, $CI) {
					return 'onclick="show_order(\''.site_url('orders/detail/'.$row['id']).'\')"';
				}
		));
		if (!empty($result_html) && !empty($status_labels[$status])) {
			$result_html = '<h4 class="title">'.$status_labels[$status].'</h4>'.$result_html;
		}
		return $result_html;
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
		foreach ((array)$this->add_durations as $key => $item) {
			$duration_inputs['add_durations['.$item['id'].']'] = array(
				'name'  => $item['name'],
				'value' => $item['hours'],
			);
		}
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
				'width'       => 3,
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
			))
			->checkbox('add_durations[]', array(
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Дополнительные услуги',
				'inline'      => false, 'group_class' => 'col-sm-12', 'label_width' => 6,
				'inputs'      => $duration_inputs,
			));

		$zone_offset = isset($_POST['timezone']) ? date('Z') - $_POST['timezone'] : 0;
		$is_late = (!empty($_POST['start_date']) && time() + (86400 * 2) > strtotime($_POST['start_date']) + $zone_offset);
		if ($is_late) {
			$this->form->form_data[2]['params']['error'] = 'Поздняя дата, выберите пожалуйста более раннюю (минимум за два дня)';
		}

		$duration = $this->input->post('duration');
		if (!empty($duration) && !isset($this->order_model->duration[$duration])) {
			$this->form->form_data[1]['params']['error'] = 'Выбрано неверное время';
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

	function send_invites($order_info) {
		$this->load->model('special_model');
		$cleaners = $this->special_model->get_favorite_users($order_info['client_id'])->result_array();
		foreach ($cleaners as $item) {
			$insert_array[] = array(
				'order_id'   => $order_info['order_id'],
				'cleaner_id' => $item['user_id'],
				'add_date'   => time(),
				'status'     => 0,
			);
		}

		if (!empty($insert_array)) {
			$this->db->insert_batch('order_invites', $insert_array);
			$this->db->where('id', $order_info['order_id'])->update('orders', array('recommended' => 1));
			return true;
		}

		return false;
	}

	function get_unread_invite_count($user_id, $reset = false) {
		$unread_invites = $this->db
			->select('i.id')
			->from('orders AS o')
			->join('order_invites AS i', 'o.id = i.order_id AND i.status = 0 AND i.cleaner_id = '.$user_id.' AND i.read = 0', 'inner')
			->where('o.cleaner_id', 0)
			->where('((o.status = 2 AND o.start_date > '.time().') OR (o.status  = 1 AND o.start_date > '.(time() + 86400).') )')
			->order_by('i.id', 'desc')
			->get()
			->result_array();
		if ($reset && !empty($unread_invites)) {
			foreach ($unread_invites as $item) {
				$ids[] = $item['id'];
			}
			$this->db->where_in('id', $ids)->update('order_invites', array('read' => 1));
		}

		return count($unread_invites);
	}

	function get_order_messages($order_id) {
		$user_id = $this->data['user_info']['id'];
		$messages = $this->db
			->select('m.*, u.photo')
			->from('order_messages AS m')
			->join('users AS u', 'm.sender_id = u.id')
			->where('order_id', $order_id)
			->where('(sender_id = '.$user_id.' OR reciever_id = '.$user_id.')')
			->get()
			->result_array();
		if (empty($messages)) {
			return false;
		}

		foreach ($messages as $key => $item) {
			if ($user_id == $item['sender_id']) {
				$messages[$key]['owner'] = 1;
			} else {
				$messages[$key]['owner'] = 0;
			}
		}
		return $messages;
	}

	function read_messages($order_id) {
		$user_id = $this->data['user_info']['id'];
		$this->db
			->where(array(
				'order_id'    => $order_id,
				'reciever_id' => $user_id,
				'read'        => 0,
			))
			->update('order_messages', array('read' => 1));
	}

	function is_busy($order_info) {
		$start_date = $order_info['start_date'];
		$end_date   = $order_info['start_date'] + ($order_info['duration'] * 3600);

		$is_busy = false;

		$active_orders = $this->get_all_orders(1)->result_array();
		if (!empty($active_orders)) {
			foreach ($active_orders as $item) {
				$item['end_date'] = $item['start_date'] + ($item['duration'] * 3600);
				if ($item['end_date'] > $start_date && $item['start_date'] < $end_date) {
					$is_busy = true;
					break;
				}

				if (in_array($item['frequency'], array('every_week','every_2_weeks')) && $item['start_date'] <= $end_date) {
					while ($item['start_date'] <= $end_date) {
						$step = $item['frequency'] == 'every_week' ? 604800 : 1209600;
						$item['start_date'] += $step;
						$item['end_date']   += $step;
						if ($item['end_date'] > $start_date && $item['start_date'] < $end_date) {
							$is_busy = true;
							break 2;
						}
					}
				}
			}
		}

		if ($is_busy) {
			return true;
		}

		$user_events = $this->db->where('user_id', $this->data['user_info']['id'])->get('events')->result_array();
		if (!empty($user_events )) {
			foreach ($user_events as $item) {
				if ($item['end_date'] > $start_date && $item['start_date'] < $end_date) {
					$is_busy = true;
					break;
				}

				if ($item['repeatable'] && $item['start_date'] <= $end_date) {
					while ($item['start_date'] <= $end_date) {
						$step = 604800;
						$item['start_date'] += $step;
						$item['end_date']   += $step;
						if ($item['end_date'] > $start_date && $item['start_date'] < $end_date) {
							$is_busy = true;
							break 2;
						}
					}
				}
			}
		}

		return $is_busy;
	}
}
