<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_order extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'orders';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Общий список сделок',
			'header_descr' => 'Список сделок',
		),
		'active'           => array(
			'header'       => 'Активные сделки',
			'header_descr' => 'Список сделок',
		),
		'completed'        => array(
			'header'       => 'Завершенные сделки',
			'header_descr' => 'Список сделок',
		),
		'edit'             => array(
			'header'       => 'Редактирование сделки',
			'header_descr' => 'Просмотр и редактирование сделки',
		),
		'payments'         => array(
			'header'       => 'Список выплат',
			'header_descr' => 'История платежей',
		),
		'regions'          => array(
			'header'       => 'Список регионов',
			'header_descr' => '11',
		),
	);

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

	public $status = array(
		'0' => 'Ожидание оплаты',
		'1' => 'Ожидание повторной оплаты (если уборка не разовая)',
		'2' => 'Сделка оплачена',
		'3' => 'Уборка завершена',
		'4' => 'Сделка отменена (не была оплачена)',
		'5' => 'Сделка отменена (была уже оплачена)',
		'6' => 'Сделка деактивирована',
	);

	public $special = array(
		'need_ironing' => 'Нужна глажка',
		'have_pets'    => 'Есть домашнее животное',
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_order_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index($status = false) {
		$this->data['status'] = $status;
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '30%',
			))
			->text('status', array(
				'title' => 'Информация',
				'func'  => function($row, $params) {
					return 'Уборка '.date('d.m.Y в H:i', $row['start_date']);
				}
		))
			->text('comment', array(
				'title' => 'Статус',
				'width' => '30%',
				'func'  => function($row, $params, $that, $CI) {
					if ($row['status'] == 3 && $row['last_mark'] == 'positive') {
						return '<span class="text-success">Уборка успешно завершена</span>';
					} elseif (in_array($row['status'], array(0,1)) && $row['start_date'] > 86400 + time()) {
						return '<span class="text-warning">Ожидаем оплаты</span>';
					} elseif ($row['status'] == 3 && $row['last_mark'] == 'negative') {
						return '<span class="text-danger">Плохое качество уборки</span>';
					} elseif ($row['status'] == 4) {
						return '<span class="text-danger">Сделка отменена</span>';
					} elseif ($row['status'] == 5) {
						return '<span class="text-danger">Сделка отменена</span>';
					} elseif (in_array($row['status'], array(0,1)) && $row['start_date'] < 86400 + time()) {
						return '<span class="text-danger">Сделка не состоялась</span>';
					} elseif (!$row['cleaner_id'] && $row['status'] == 2 && $row['start_date'] > time() && $CI->data['user_info']['is_cleaner']) {
						return '<a href="'.site_url('orders/accept/'.$row['id']).'" class="btn btn-primary">Взяться</a>';
					} elseif (!$row['cleaner_id']) {
						return '<span class="text-warning">Ожидаем горничную</span>';
					}
					return false;
				}
		))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->create(function($CI) {
				return $CI->admin_order_model->get_all_orders($CI->data['status']);
			});

		load_admin_views();
	}

	public function active() {
		$this->index(1);
	}

	public function completed() {
		$this->index(2);
	}


	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$order_info = $this->admin_order_model->get_order_info($id);

		if (empty($order_info)) {
			custom_404();
		}
		set_header_info($order_info);

		$this->data['center_block'] = $this->edit_form($order_info);
		$this->data['center_block'] .= '<br><br><h3>История платежей</h3>';
		$this->data['center_block'] .= $this->payment_table($order_info['id']);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			$_POST['start_date']   = strtotime($this->input->post('start_date'));
			$_POST['have_pets']    = $this->input->post('have_pets');
			$_POST['need_ironing'] = $this->input->post('need_ironing');
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($order_info = false) {
		$clients = $this->db->select('id, CONCAT(first_name, \' \',last_name, \' - \', email) as name', false)->where('is_cleaner != 1')->get('users')->result_array();
		$cleaners = $this->db->select('id, CONCAT(first_name, \' \',last_name, \' - \', email) as name', false)->where('is_cleaner = 1')->get('users')->result_array();

		$this->load->library('form');
		return $this->form
			->select('client_id', array(
				'value'       => !empty($order_info['client_id']) ? $order_info['client_id'] : false,
				'valid_rules' => 'trim|xss_clean|required',
				'label'       => 'Клиент',
				'options'     => $clients,
				'search'      => true,
			))
			->select('client_id', array(
				'value'       => !empty($order_info['cleaner_id']) ? $order_info['cleaner_id'] : false,
				'valid_rules' => 'trim|xss_clean|required',
				'label'       => 'Работник',
				'options'     => $cleaners,
				'search'      => true,
			))
			->radio('frequency', array(
				'valid_rules' => 'required|trim',
				'label'       => 'Как часто нужна горничная?',
				'inline'      => false,
				'inputs'      => $this->frequency,
				'value'       => !empty($order_info['frequency']) ? $order_info['frequency'] : false,
			))
			->select('duration', array(
				'valid_rules' => 'required|trim',
				'label'       => 'На сколько времени нужна?',
				'options'     => $this->duration,
				'value'       => !empty($order_info['duration']) ? $order_info['duration'] : false,
			))
			->date('start_date', array(
				'valid_rules' => 'required|trim',
				'label'       => 'Время начала уборки',
				'type'        => 'd.m.Y H:i',
				'value'       => !empty($order_info['start_date']) ? $order_info['start_date'] : false,
			))
			->checkbox('special[]', array(
				'valid_rules' => 'trim|xss_clean',
				'label'       => 'Особые требования',
				'inline'      => false,
				'inputs'      => $this->special,
				'value'       => !empty($order_info) ? $order_info : false,
			))
			->text('country', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Страна',
				'value'       => !empty($order_info['country']) ? $order_info['country'] : false,
			))
			->text('city', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Город',
				'value'       => !empty($order_info['city']) ? $order_info['city'] : false,
			))
			->text('address', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]',
				'label'       => 'Адрес',
				'value'       => !empty($order_info['address']) ? $order_info['address'] : false,
			))
			->text('zip', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[100]|is_natural',
				'label'       => 'Индекс',
				'value'       => !empty($order_info['zip']) ? trim($order_info['zip'], ',') : false
			))
			->radio('status', array(
				'valid_rules' => 'required|trim|is_natural',
				'label'       => 'Статус',
				'inline'      => false,
				'inputs'      => $this->status,
				'value'       => $order_info['status'],
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function payments($order_id = false) {

		$this->data['center_block'] = $this->payment_table();

		load_admin_views();
	}

	private function payment_table($order_id = false) {
		$this->data['order_id'] = intval($order_id);
		$this->load->library('table');
		$this->table
			->text('id', array(
				'title' => 'Номер платежа',
			));
		if (empty($this->data['order_id'])) {
			$this->table
				->text('order_id', array(
					'title' => 'Номер сделки',
					'func'  => function($row, $params, $that, $CI) {
						return '<a href="'.site_url($CI->MAIN_URL.'edit/'.$row['order_id']).'">#'.$row['order_id'].'</a>';
					}
			));
		}
		return $this->table
			->text('price_per_hour', array(
				'title' => 'Цена за час уборки',
				'func'  => function($row, $params) {
					return $row['price_per_hour'].' рублей';
				}
		))
			->text('detergent_price', array(
				'title' => 'Цена моющих средств',
				'func'  => function($row, $params) {
					return $row['detergent_price'].' рублей';
				}
		))
			->text('total_price', array(
				'title' => 'Общая цена',
				'func'  => function($row, $params) {
					return $row['total_price'].' рублей';
				}
		))
			->date('add_date', array(
				'title' => 'Дата оплаты',
			))
			->create(function($CI) {
				return $CI->admin_order_model->get_payments($CI->data['order_id']);
			});

	}


}
