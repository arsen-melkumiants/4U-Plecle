<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Calendar extends CI_Controller {

	public function __construct() {
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

		if (!$this->data['user_info']['is_cleaner']) {
			if ($this->input->is_ajax_request()) {
				echo 'refresh';exit;
			}
			redirect('personal/login', 'refresh');
		}

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	public function index() {
		$this->data['title'] = $this->data['header'] = 'Календарь событий';

		$this->data['center_block'] = $this->load->view('profile/calendar_js', $this->data, true);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	public function add_event() {
		$this->data['header'] = 'Создание события';

		$this->data['center_block'] = $this->edit_form();

		if ($this->form_validation->run() == FALSE) {
			if ($this->input->is_ajax_request()) {
				load_views();
			} else {
				redirect('calendar/events', 'refresh');
			}
		} else {
			$data = array(
				'user_id'    => $this->data['user_info']['id'],
				'start_date' => strtotime($this->input->post('start_date')),
				'end_date'   => strtotime($this->input->post('end_date')),
				'repeatable' => $this->input->post('repeatable'),
			);

			$this->db->insert('events', $data);
			$this->session->set_flashdata('success', 'Событие создано');
			custom_redirect('calendar/events');
		}
	}

	public function edit_event($event_id = false) {
		$event_id = intval($event_id);
		if (empty($event_id)) {
			custom_404();
		}

		$event_info = $this->db->where(array(
			'id'      => $event_id,
			'user_id' => $this->data['user_info']['id'],
		))->get('events')->row_array();
		if (empty($event_info)) {
			custom_404();
		}

		$this->data['header'] = 'Редактирование события';

		$this->data['center_block'] = $this->edit_form($event_info);

		if ($this->form_validation->run() == FALSE) {
			load_views();
		} else {
			$data = array(
				'start_date' => strtotime($this->input->post('start_date')),
				'end_date'   => strtotime($this->input->post('end_date')),
			);

			if (isset($_POST['repeatable'])) {
				$data['repeatable'] = $this->input->post('repeatable');
			}

			$this->db->where('id', $event_id)->update('events', $data);
			$this->session->set_flashdata('success', 'Событие редактировано');
			custom_redirect('calendar/events');
		}
	}

	public function delete_event($event_id = false) {
		$event_id = intval($event_id);
		if (empty($event_id)) {
			custom_404();
		}

		$event_info = $this->db->where(array(
			'id'      => $event_id,
			'user_id' => $this->data['user_info']['id'],
		))->get('events')->row_array();
		if (empty($event_info)) {
			custom_404();
		}

		$this->db->where('id', $event_id)->delete('events');
		//$this->session->set_flashdata('success', 'Событие удалено');
		custom_redirect('calendar/events');
	}

	private function edit_form($event_info = false) {
		$this->load->library('form');
		return $this->form
			->date('start_date', array(
				'value'       => !empty($event_info['start_date']) ? $event_info['start_date'] : (!empty($_GET['start_date']) ? $_GET['start_date'] : false),
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Дата начала',
				'type'        => 'd.m.Y H:i',
			))
			->date('end_date', array(
				'value'       => $event_info['end_date'] ?: (!empty($_GET['end_date']) ? $_GET['end_date'] : false),
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Дата завершения',
				'type'        => 'd.m.Y H:i',
			))
			->radio('repeatable', array(
				'value'       => $event_info['repeatable'] ?: false,
				'inputs'      => array('Нет', 'Да'),
				'label'       => 'Повторение',
			))
			->btn(array('value' => !empty($event_info) ? 'Изменить' : 'Добавить'))
			->create(array('action' => current_url()));
	}

	public function events() {
		$all_orders = $this->db->where('cleaner_id', $this->data['user_info']['id'])->get('orders')->result_array();
		if (empty($all_orders)) {
			exit;
		}

		foreach ($all_orders as $key => $item) {
			$result_array[$key] = array(
				'start'    => date('Y-m-d H:i', $item['start_date']),
				'end'      => date('Y-m-d H:i', $item['start_date'] + ($item['duration'] * 3600)),
				'color'    => '#ffba00',
				'editable' => false,
				'url'      => site_url('orders/detail/'.$item['id']),
			);

			if (in_array($item['status'], array(0,1)) && $item['start_date'] < 86400 + time()) {
				//Сделка не состоялась
				$result_array[$key]['color'] = '#a2aea8';
			} elseif (in_array($item['status'], array(4,5))) {
				//Сделка отменена
				$result_array[$key]['color'] = '#a2aea8';
			} elseif (!$item['cleaner_id'] && $item['status'] == 2 && $item['start_date'] > time() && $this->data['user_info']['is_cleaner']) {
				//Подробнее
				$result_array[$key]['color'] = '#ffba00';
			} elseif (!$item['cleaner_id'] && $item['status'] == 2 && $item['start_date'] < time()) {
				//Сделка отменена (отсутствует горничная)
				$result_array[$key]['color'] = '#a2aea8';
			} elseif (in_array($item['status'], array(0,1))) {
				//Ожидаем оплаты
				$result_array[$key]['color'] = '#ffba00';
			} elseif (!$item['cleaner_id']) {
				//Ожидаем горничную
				$result_array[$key]['color'] = '#ffba00';
			} elseif ($item['status'] == 2 && $item['start_date'] + (3600 * $item['duration']) > time()) {
				//Сделка в процессе
				$result_array[$key]['color'] = '#00ff7e';
			} elseif ($item['status'] == 2 && $item['start_date'] + (3600 * $item['duration']) < time()) {
				//Ожидаем оценку уборки
				$result_array[$key]['color'] = '#a2aea8';
			} elseif ($item['status'] == 3 && $item['last_mark'] == 'positive') {
				//Уборка успешно завершена
				$result_array[$key]['color'] = '#a2aea8';
			} elseif ($item['status'] == 3 && $item['last_mark'] == 'negative') {
				//Плохое качество уборки
				$result_array[$key]['color'] = '#a2aea8';
			}
		}

		$all_events = $this->db->where('user_id', $this->data['user_info']['id'])->get('events')->result_array();
		if (!empty($all_events)) {
			foreach ($all_events as $item) {
				$result_array[] = array(
					'id'          => $item['id'],
					'title'       => 'Занято',
					'start'       => date('Y-m-d H:i', $item['start_date']),
					'end'         => date('Y-m-d H:i', $item['end_date']),
					'color'       => '#e53838',
					'editable'    => true,
					'delete'      => site_url('calendar/delete_event/'.$item['id']),
					'repeatable'  => $item['repeatable'],
				);
			}
		}

		echo json_encode($result_array);
		exit;
	}

}