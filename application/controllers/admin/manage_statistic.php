<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_statistic extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'shop_products';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Статистика по сделкам',
			'header_descr' => 'Статистика по оценкам сделок',
		),
		'orders'           => array(
			'header'       => 'Статистика по сделкам',
			'header_descr' => 'Статистика по оценкам сделок',
		),
		'turnover'         => array(
			'header'       => 'Финансовая статистика',
			'header_descr' => 'Статистика по обороту и доходу сайта',
		),
		'users'         => array(
			'header'       => 'Пользователи',
			'header_descr' => 'Количество клиентов и горничных',
		),
		'regions'         => array(
			'header'       => 'Статитстика по районам',
			'header_descr' => 'Общее количество сделок по индексам и районам',
		),
	);

	public function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->load->model(ADM_FOLDER.'admin_order_model');
		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();

		$this->zip = $this->admin_order_model->get_all_zips();
	}

	public function index() {
		$this->orders('all');
	}

	public function orders($period = 'all') {
		if ($period == 'period' && !empty($_GET['from']) && !empty($_GET['to'])) {
			$period = array(
				'from' => strtotime($_GET['from']),
				'to'   => strtotime($_GET['to']),
			);
		}
		$this->data['period'] = $period;
		$this->data['types']  = array(
			//'all'    => 'Все время',
			//'daily'  => 'День',
			'week'   => 'Неделя',
			'month'  => 'Месяц',
			'year'   => 'Год',
			'period' => 'Указанный период'
		);

		$order_stats = $this->admin_order_model->get_order_stats($period);
		$this->data['dd_list'] = array(
			'Все сделки'           => $order_stats['all'].' <small>(включая текущие и отмененные)</small>',
			'Положительные сделки' => $order_stats['positive'],
			'Отрицательные сделки' => $order_stats['negative'],
		);

		$this->MAIN_URL .= 'orders/';
		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'dd_page', $this->data, true);
		load_admin_views();
	}

	public function turnover($period = 'all') {
		if ($period == 'period' && !empty($_GET['from']) && !empty($_GET['to'])) {
			$period = array(
				'from' => strtotime($_GET['from']),
				'to'   => strtotime($_GET['to']),
			);
		}
		$this->data['period'] = $period;
		$this->data['types']  = array(
			//'all'    => 'Все время',
			//'daily'  => 'День',
			'week'   => 'Неделя',
			'month'  => 'Месяц',
			'year'   => 'Год',
			'period' => 'Указанный период'
		);

		$turnover = $this->admin_order_model->get_turnover($period);
		$this->data['dd_list'] = array(
			'Оборот сайта' => floatval($turnover['total']).' рублей',
			'Доход сайта'  => floatval($turnover['profit']).' рублей',
		);

		$this->MAIN_URL .= 'turnover/';
		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'dd_page', $this->data, true);
		load_admin_views();
	}

	public function users() {
		$user_count = $this->admin_order_model->get_user_count();
		$this->data['dd_list'] = array(
			'Всего клиентов'  => $user_count['clients'],
			'Всего горничных' => $user_count['cleaners'],
		);

		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'dd_page', $this->data, true);
		load_admin_views();
	}

	public function regions($period = 'all') {
		if ($period == 'period' && !empty($_GET['from']) && !empty($_GET['to'])) {
			$period = array(
				'from' => strtotime($_GET['from']),
				'to'   => strtotime($_GET['to']),
			);
		}
		$this->data['period'] = $period;
		$this->data['types']  = array(
			//'all'    => 'Все время',
			//'daily'  => 'День',
			'week'   => 'Неделя',
			'month'  => 'Месяц',
			'year'   => 'Год',
			'period' => 'Указанный период'
		);

		$this->MAIN_URL .= __FUNCTION__.'/';
		$this->data['center_block'] = $this->load->view(ADM_FOLDER.'dd_page', $this->data, true);
		$this->load->library('table');
		$this->data['center_block'] .= $this->table
			->text('zip', array(
				'title' => 'Индекс',
			))
			->text('zip', array(
				'title' => 'Район',
				'func'  => function($row, $params, $that, $CI) {
					return isset($CI->zip[$row['zip']]) ? $CI->zip[$row['zip']] : 'Не указан';
				}
		))
			->text('count', array(
				'title' => 'Количество сделок',
			))
			->create(function($CI) {
				return $CI->admin_order_model->get_region_orders($CI->data['period']);
			});

		load_admin_views();
	}
}
