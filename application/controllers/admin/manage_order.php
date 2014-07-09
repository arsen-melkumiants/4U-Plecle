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
					} elseif (in_array($row['status'], array(0,1))) {
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

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
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
				'inputs'      => $this->special
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
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}

	public function delete($id = false, $type = false) {
		if (empty($id)) {
			custom_404();
		}

		if ($type == 'category') {
			$get_method = 'get_content_category_info';
			$table      = 'content_categories';
		} else {
			$get_method = 'get_content_info';
			$table      = 'content';
		}

		$content_info = $this->admin_content_model->$get_method($id);

		if (empty($content_info)) {
			custom_404();
		}
		set_header_info($content_info);

		admin_method('delete', $table, $content_info);
	}

	public function delete_category($id) {
		$this->delete($id, 'category');
	}

	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}

		$content_info = $this->admin_content_model->get_content_info($id);

		if (empty($content_info)) {
			custom_404();
		}
		set_header_info($content_info);
		admin_method('active', $this->DB_TABLE, $content_info);
	}

	function categories() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
				'p_width' => 50
			))
			->text('alias', array(
				'title' => 'Ссылка',
			))
			->edit(array('link' => $this->MAIN_URL.'edit_category/%d', 'modal' => true))
			->delete(array('link' => $this->MAIN_URL.'delete_category/%d', 'modal' => true))
			->btn(array(
				'link'   => $this->MAIN_URL.'add_category',
				'name'   => 'Добавить',
				'header' => true,
				'modal'  => true,
			))
			->create(function($CI) {
				return $CI->admin_content_model->get_content_categories(true);
			});

		load_admin_views();
	}

	public function edit_category($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$category_info = $this->admin_content_model->get_content_category_info($id);

		if (empty($category_info)) {
			custom_404();
		}
		set_header_info($category_info);

		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $category_info['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_category_form($category_info);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', 'content_categories', array('id' => $id));
		}
	}

	public function add_category() {
		if(!empty($_POST)){
			$alias = !empty($_POST['alias']) ? $_POST['alias'] : $_POST['name'];
			$_POST['alias'] = url_title(translitIt($alias), 'underscore', TRUE);
		}

		$this->data['center_block'] = $this->edit_category_form();

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', 'content_categories', array('except_fields' => array('add_date', 'author_id')));
		}
	}

	private function edit_category_form($category_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $category_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('alias', array(
				'value'       => $category_info['alias'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|'.(!$category_info['id'] ? 'is_unique[content_categories.alias]' : 'is_unique_without[content_categories.alias.'.$category_info['id'].']'),
				'label'       => 'Ссылка',
			))
			->btn(array('value' => empty($id) ? 'Добавить' : 'Изменить'))
			->create(array('action' => current_url()));
	}
}
