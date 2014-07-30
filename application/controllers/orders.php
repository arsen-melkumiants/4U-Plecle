<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

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

	//redirect if needed, otherwise display the user list
	function index() {
		$this->data['title'] = $this->data['header'] = 'Список сделок';

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

		$this->data['center_block'] = $this->order_table(0);
		$this->data['center_block'] .= $this->order_table(1);
		$this->data['center_block'] .= $this->order_table(2);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->data['center_block'] .= $this->order_table(3);
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	function detail($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$this->data['order_info'] = $this->order_model->get_user_order($order_id);
		if (empty($this->data['order_info'])) {
			$this->data['order_info'] = $this->order_model->get_user_order($order_id, false, 'no_cleaner');
			if (empty($this->data['order_info'])) {
				custom_404();
			}
		}

		$this->data['title'] = $this->data['header'] = 'Сделка #'.$order_id;

		$this->data['payment_history'] = $this->payment_table($order_id);
		$this->data['center_block'] = $this->load->view('orders/order_info', $this->data, true);
		$this->data['right_info']   = array(
			'title'      => 'Детали заявки',
			'info_array' => array(
				'Индекс'          => $this->data['order_info']['zip'],
				'Дата'            => date('d.m.Y', $this->data['order_info']['start_date']),
				'Время'           => date('H:i', $this->data['order_info']['start_date']),
				'Частота'         => $this->order_model->frequency[$this->data['order_info']['frequency']],
				'Рабочие часы'    => $this->order_model->duration[$this->data['order_info']['duration']],
				'Цена за час'     => floatval($this->data['order_info']['price_per_hour']).' рублей',
				'Моющие средства' => floatval($this->data['order_info']['detergent_price'] * $this->data['order_info']['need_detergents']).' рублей',
				'Итого'           => floatval($this->data['order_info']['total_price']).' рублей',
			),
		);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->data['client_info'] = $this->ion_auth->user($this->data['order_info']['client_id'])->row_array();
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			if (!empty($this->data['order_info']['cleaner_id'])) {
				$this->data['cleaner_info'] = $this->ion_auth->user($this->data['order_info']['cleaner_id'])->row_array();
			}
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	private function order_table($status = 0) {
		$this->data['status'] = $status;

		$status_labels = array(
			'0' => 'Заявки на сделки',
			'1' => 'Активные сделки',
			'2' => 'Завершенные сделки',
			'3' => 'Эти сделки должны Вас заинтересовать',
		);

		$this->table
			->text('id', array(
				'title' => 'Номер',
				'width' => '30%',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('orders/detail/'.$row['id']).'">#'.$row['id'].'</a>';
				}
		))
			->text('status', array(
				'title' => 'Информация',
				'func'  => function($row, $params) {
					return '<a href="'.site_url('orders/detail/'.$row['id']).'">Уборка '.date('d.m.Y в H:i', $row['start_date']).'</a>';
				}
		))
			->text('comment', array(
				'title' => 'Номер',
				'width' => '30%',
				'func'  => function($row, $params, $that, $CI) {
					if (in_array($row['status'], array(0,1)) && $row['start_date'] < 86400 + time()) {
						return '<span class="text-danger">Сделка не состоялась</span>';
					} elseif (!$row['cleaner_id'] && $row['status'] == 2 && $row['start_date'] > time() && $CI->data['user_info']['is_cleaner']) {
						return '<a href="'.site_url('orders/accept/'.$row['id']).'" class="btn btn-primary">Взяться</a>';
					} elseif (in_array($row['status'], array(0,1))) {
						return '<span class="text-warning">Ожидаем оплаты</span>';
					} elseif (!$row['cleaner_id']) {
						return '<span class="text-warning">Ожидаем горничную</span>';
					} elseif ($row['status'] == 2 && $row['start_date'] + (3600 * $row['duration']) < time()) {
						return '<span class="text-warning">Ожидаем оценку уборки</span>';
					} elseif ($row['status'] == 3 && $row['last_mark'] == 'positive') {
						return '<span class="text-success">Уборка успешно завершена</span>';
					} elseif ($row['status'] == 3 && $row['last_mark'] == 'negative') {
						return '<span class="text-danger">Плохое качество уборки</span>';
					} elseif ($row['status'] == 4) {
						return '<span class="text-danger">Сделка отменена</span>';
					} elseif ($row['status'] == 5) {
						return '<span class="text-danger">Сделка отменена</span>';
					}
					return false;
				}
		));


		$result_html = $this->table
			->create(function($CI) {
				return $CI->order_model->get_all_orders($CI->data['status']);
			}, array('no_header' => true, 'class' => 'list'));
		if (!empty($result_html)) {
			$result_html = '<h4 class="title">'.$status_labels[$status].'</h4>'.$result_html;
		}
		return $result_html;
	}

	private function payment_table($order_id = false) {
		$this->data['order_id'] = $order_id;
		return $this->table
			->date('add_date', array(
				'title' => 'Номер',
				'type' => 'd.m.Y H:i',
				'width' => '50%',
			))
			->text('total_price', array(
				'title' => 'Цена',
				'func'  => function($row, $params) {
					return floatval($row['total_price']).' рублей';
				}
		))
			->create(function($CI) {
				return $CI->order_model->get_order_payments($CI->data['order_id']);
			}, array('no_header' => true, 'class' => 'list'));
	}

	function pay($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'client');
		if (empty($order_info)) {
			custom_404();
		}

		$pay_time = ($order_info['start_date'] - 86400) > time();
		if ($pay_time && ($order_info['status'] == 0 || $order_info['status'] == 1)) {
			$this->db->trans_begin();
			$this->db->where('id', $order_id)->update('orders', array('status' => 2));
			$this->db->insert('payments', array(
				'order_id'        => $order_info['id'],
				'price_per_hour'  => $order_info['price_per_hour'],
				'detergent_price' => $order_info['detergent_price'],
				'total_price'     => $order_info['total_price'],
				'add_date'        => time(),
				'status'          => 1,
			));
			$this->db->trans_commit();
			$email_info = array(
				'order_id'   => $order_info['id'],
				'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
			);
			$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Оплата успешно произведена', 'paid_order', $email_info);
			if (!empty($order_info['cleaner_id'])) {
				$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Оплата успешно произведена', 'paid_order', $email_info);
			}
			$this->session->set_flashdata('success', 'Оплата успешно произведена');
		} elseif ($order_info['status'] == 2) {
			$this->session->set_flashdata('danger', 'Оплата уже совершена');
		} else {
			$this->session->set_flashdata('danger', 'Оплата не может быть произведена');
		}
		redirect('orders/detail/'.$order_id, 'refresh');
	}

	function positive_mark($order_id = false) {
		$this->mark($order_id, 'positive');
	}

	function negative_mark($order_id = false) {
		$this->mark($order_id, 'negative');
	}

	private function mark($order_id = false, $sign = 'positive') {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'client');
		if (empty($order_info)) {
			custom_404();
		}

		$mark_time = ($order_info['start_date'] + (3600 * $order_info['duration']) + 1800) < time();
		if ($order_info['status'] == 2 && $mark_time) {
			$update_array = array('status' => 3);
			if ($order_info['frequency'] == 'every_week') {
				$update_array['status'] = 1;
				$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 604800);
			} elseif ($order_info['frequency'] == 'every_2_weeks') {
				$update_array['status'] = 1;
				$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 1209600);
			}

			if ($update_array['status'] === 1) {
				$update_array['price_per_hour']  = PRICE_PER_HOUR;
				$update_array['detergent_price'] = floatval($order_info['detergent_price']) ? DETERGENT_PRICE * $order_info['duration'] : 0;
				$update_array['total_price']     = PRICE_PER_HOUR * $order_info['duration'] + floatval($update_array['detergent_price']);
			}
			$this->db->trans_begin();
			$this->db->where('id', $order_id)->update('orders', $update_array);
			$this->db->insert('marks', array(
				'order_id' => $order_info['id'],
				'mark'     => $sign,
				'add_date' => time(),
				'status'   => 1,
			));
			$this->db->trans_commit();
			$this->session->set_flashdata('success', 'Сделка успешно выполнена');
			$email_info = array(
				'order_id'   => $order_info['id'],
				'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
			);
			$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Сделка успешно выполнена', 'success_order', $email_info);
			if (!empty($order_info['cleaner_id'])) {
				$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Сделка успешно выполнена', 'success_order', $email_info);
			}
		} else {
			$this->session->set_flashdata('danger', 'Оценка не может быть произведена');
		}
		redirect('orders/detail/'.$order_id, 'refresh');
	}

	private function next_order_time($start_date, $step) {
		$next_date = $start_date + $step;
		if ($next_date + 86400 < time()) {
			$next_date = $this->next_order_time($next_date, $step);
		}
		return $next_date;
	}

	function cancel($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id);
		if (empty($order_info)) {
			custom_404();
		}

		$cancel_time = ($order_info['start_date'] > time() + 86400);
		if (in_array($order_info['status'], array(0,1,2)) && $cancel_time) {
			$update_array['cancel_date'] = time();
			if ($order_info['status'] == 2) {
				$update_array['status'] = 5;
			} else {
				$update_array['status'] = 4;
			}
			$this->db->where('id', $order_id)->update('orders', $update_array);
			$this->session->set_flashdata('success', 'Сделка успешно отменена');
			$email_info = array(
				'order_id'   => $order_info['id'],
				'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
				'paid'       => $order_info['status'] == 2,
			);
			$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Сделка отменена', 'cancel_order', $email_info);
			if (!empty($order_info['cleaner_id'])) {
				$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Сделка отменена', 'cancel_order', $email_info);
			}
		} else {
			$this->session->set_flashdata('danger', 'Сделка не может быть отменена');
		}
		redirect('orders/detail/'.$order_id, 'refresh');
	}

	function accept($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'no_cleaner');
		if (empty($order_info)) {
			custom_404();
		}

		$update_array['cleaner_id'] = $this->data['user_info']['id'];
		$this->db->where('id', $order_id)->update('orders', $update_array);
		$this->session->set_flashdata('success', 'Сделка успешно заключена');
		$email_info = array(
			'order_id'   => $order_info['id'],
			'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
		);
		$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Заявка успешно принята', 'accept_order', $email_info);
		if (!empty($order_info['cleaner_id'])) {
			$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Заявка успешно принята', 'accept_order', $email_info);
		}
		redirect('orders/detail/'.$order_id, 'refresh');
	}
}
