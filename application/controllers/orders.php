<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Orders extends CI_Controller {

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

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	public function index() {
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

		$this->data['created_orders']   = $this->order_model->order_table(0);
		$this->data['active_orders']    = $this->order_model->order_table(1);
		$this->data['completed_orders'] = $this->order_model->order_table(2);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->data['user_balance'] = $this->order_model->get_user_balance();

			$this->data['request_orders'] = $this->order_model->order_table(3);
			$this->data['invite_orders'] = $this->order_model->order_table(4);
			$this->load->view('orders/cleaner_top', $this->data);
		} else {
			$this->load->view('orders/client_top', $this->data);
		}

		$this->data['center_block'] = $this->load->view('orders/orders_list', $this->data, true);

		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	public function detail($order_id = false) {
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

		if ($this->data['order_info']['invite_read'] === '0') {
			$this->db->where('id', $this->data['order_info']['invite_id'])->update('order_invites', array('read' => 1));
		}

		$this->data['title'] = $this->data['header'] = 'Сделка #'.$order_id;

		$this->data['payment_history'] = $this->payment_table($order_id);
		$this->data['center_block'] = $this->load->view('orders/order_info', $this->data, true);

		if (in_array($this->data['order_info']['status'], array(1,2)) && $this->data['order_info']['start_date'] - 86400 < time()) {
			$this->messages($order_id, true);
		}

		$this->data['right_info']   = array(
			'title'      => 'Детали заявки',
			'info_array' => array(
				'Индекс'          => $this->data['order_info']['zip'],
				'Дата'            => date('d.m.Y', $this->data['order_info']['start_date']),
				'Время'           => date('H:i', $this->data['order_info']['start_date']),
				'Частота'         => $this->order_model->frequency[$this->data['order_info']['frequency']],
				'Рабочие часы'    => isset($this->order_model->duration[$this->data['order_info']['duration']]) ? $this->order_model->duration[$this->data['order_info']['duration']] : $this->data['order_info']['duration'].' часов',
				'Цена за час'     => floatval($this->data['order_info']['price_per_hour']).' рублей',
				'Моющие средства' => floatval($this->data['order_info']['detergent_price'] * $this->data['order_info']['need_detergents']).' рублей',
				'Итого'           => floatval($this->data['order_info']['total_price']).' рублей',
			),
		);

		$this->load->view('header', $this->data);
		if ($this->data['user_info']['is_cleaner']) {
			$this->data['client_info'] = $this->ion_auth->user($this->data['order_info']['client_id'])->row_array();
			$this->load->view('orders/cleaner_top', $this->data);

			$this->data['right_info']['info_array']['Цена за час'] = floatval($this->data['order_info']['cleaner_price']).' рублей';
			$this->data['right_info']['info_array']['Итого'] = floatval($this->data['order_info']['total_cleaner_price']).' рублей';
		} else {
			if (!empty($this->data['order_info']['cleaner_id'])) {
				$this->data['cleaner_info'] = $this->ion_auth->user($this->data['order_info']['cleaner_id'])->row_array();
			}
			$this->load->view('orders/client_top', $this->data);
		}
		$this->load->view('orders/order_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	public function contact($order_id = false) {
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

		$this->data['address'] = $this->data['order_info']['country'].
			','.$this->data['order_info']['city'].
			','.$this->data['order_info']['address'];
		echo $this->load->view('orders/contact', $this->data, true);
		exit;
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
				'func'  => function($row, $params, $that, $CI) {
					return floatval($CI->data['user_info']['is_cleaner'] ? $row['total_cleaner_price'] : $row['total_price']).' рублей';
				}
		))
			->create(function($CI) {
				return $CI->order_model->get_order_payments($CI->data['order_id']);
			}, array('no_header' => true, 'class' => 'list'));
	}

	public function pay($order_id = false) {
		return custom_404();
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

	public function positive_mark($order_id = false) {
		$this->mark($order_id, 'positive');
	}

	public function negative_mark($order_id = false) {
		$this->mark($order_id, 'negative');
	}

	private function mark($order_id = false, $type = 'positive') {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'client');
		if (empty($order_info)) {
			custom_404();
		}

		$mark_time = ($order_info['start_date'] + (3600 * $order_info['duration']) + 1800) < time();
		if ($order_info['status'] == 2 && $mark_time && !empty($order_info['cleaner_id'])) {
			$this->load->library('form');
			$this->data['center_block']  = '';
			$this->data['title']         = $this->data['header'] = 'Отзыв';

			$this->data['center_block'] .= '<div class="popup_info">';
			$this->data['center_block'] .= 'Спасибо за проведенную сделку, а теперь, оставьте пожалуйста отзыв о горничной.';
			$this->data['center_block'] .= '</div>';

			$sign = $type == 'positive' ? 1 : -1;

			for ($i = 1;$i <= 10;$i++) {
				$inputs[$i * $sign] = $i * $sign;
			}

			$this->data['center_block'] .= $this->form
				->textarea('review', array('no_editor' => true, 'rows' => 7, 'width' => 12, 'valid_rules' => 'trim', 'label' => 'Отзыв'))
				->separator('<div class="popup_info">И поставьте оценку, которую по-Вашему заслуживает горничная</div>')
				->radio('amount', array(
					'valid_rules' => 'required|trim',
					'inputs'      => $inputs,
					'btn_view'    => true,
					'placeholder' => 'Оценка',
				))
				->btn(array('name' => 'submit', 'value' => 'Завершить сделку', 'class' => 'btn-primary btn-block'))
				->create(array('action' => current_url(), 'btn_offset' => 0, 'error_inline' => true, 'class' => 'review_form'));

			if ($this->form_validation->run() == false || !in_array($this->input->post('amount'), $inputs)) {
				if ($this->input->is_ajax_request()) {
					echo $this->load->view('ajax', $this->data, true);
					exit;
				} else {
					custom_404();
				}
			} else {
				$cleaner_price = $order_info['max_sallary'] ? MAX_CLEANER_SALARY : CLEANER_SALARY;
				$update_array = array(
					'status'          => 3,
					'last_mark'       => $type,
					'detergent_price' => $order_info['detergent_price'],
				);

				if ($order_info['frequency'] == 'every_week') {
					$update_array['status'] = 1;
					$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 604800);
				} elseif ($order_info['frequency'] == 'every_2_weeks') {
					$update_array['status'] = 1;
					$update_array['start_date'] = $this->next_order_time($order_info['start_date'], 1209600);
				}

				if ($update_array['status'] === 1) {
					$update_array['price_per_hour']      = PRICE_PER_HOUR;
					$update_array['cleaner_price']       = $cleaner_price;
					$update_array['detergent_price']     = floatval($order_info['detergent_price']) ? DETERGENT_PRICE * $order_info['duration'] : 0;
					$update_array['total_price']         = PRICE_PER_HOUR * $order_info['duration'] + floatval($update_array['detergent_price']);
					$update_array['total_cleaner_price'] = $cleaner_price * $order_info['duration'] + floatval($update_array['detergent_price']);
				}
				$this->db->trans_begin();
				$this->db->where('id', $order_id)->update('orders', $update_array);
				$this->db->insert('marks', array(
					'order_id'   => $order_info['id'],
					'cleaner_id' => $order_info['cleaner_id'],
					'mark'       => $type,
					'amount'     => $this->input->post('amount'),
					'review'     => $this->input->post('review'),
					'add_date'   => time(),
					'status'     => 1,
				));
				$this->order_model->log_payment($order_info['cleaner_id'], 'order_payment', $order_info['id'], ($cleaner_price * $order_info['duration'] + floatval($update_array['detergent_price'])));
				$this->db->trans_commit();
				$this->session->set_flashdata('success', 'Сделка успешно завершена');
				$email_info = array(
					'order_id'   => $order_info['id'],
					'start_date' => date('d.m.Y в H:i', $order_info['start_date']),
				);
				$this->order_model->send_mail($this->ion_auth->user($order_info['client_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
				if (!empty($order_info['cleaner_id'])) {
					$this->order_model->send_mail($this->ion_auth->user($order_info['cleaner_id'])->row()->email, 'Сделка успешно завершена', 'success_order', $email_info);
				}
				$this->order_model->send_mail(SITE_EMAIL, 'Сделка завершена c негативным рейтингом', 'negative_order', $email_info);
				echo 'refresh';
				exit;
			}
		} else {
			$this->session->set_flashdata('danger', 'Оценка не может быть произведена');
		}
		redirect('orders/detail/'.$order_id, 'refresh');
	}

	private function next_order_time($start_date, $step) {
		$next_date = $start_date + $step;
		if ($next_date - 86400 < time()) {
			$next_date = $this->next_order_time($next_date, $step);
		}
		return $next_date;
	}

	public function cancel($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id);
		if (empty($order_info)) {
			custom_404();
		}

		if ($this->input->is_ajax_request()) {
			if (isset($_POST['delete'])) {
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
				} elseif (in_array($order_info['status'], array(2)) && ($order_info['start_date'] > time() + 3600) && ($order_info['start_date'] < time() + 86400) && $order_info['client_id'] == $this->data['user_info']['id']) {
					$update_array['cancel_date'] = time();
					$update_array['status']      = 5;
					if (defined('FINE_PRICE')) {
						$update_array['fine_price'] = FINE_PRICE;
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
				echo 'refresh';
			} else {
				$this->load->library('form');
				$this->data['center_block'] = '';
				if (in_array($order_info['status'], array(2)) && ($order_info['start_date'] < time() + 86400) && ($order_info['start_date'] > time() + 360)) {
					$this->data['title']         = $this->data['header'] = 'Внимание!';
					$this->data['center_block'] .= '<div class="popup_info">';
					$this->data['center_block'] .= 'До начало уборки осталось <span class="text-danger">менее 24 часов</span>.<br>';
					$this->data['center_block'] .= 'С вас будет списан штраф в <b>'.FINE_PRICE.' рублей</b>!<br>';
					$this->data['center_block'] .= 'Все равно хотите продолжить?';
					$this->data['center_block'] .= '</div>';
				} else {
					$this->data['title'] = $this->data['header'] = 'Отмена сделки';
				}
				$this->data['center_block'] .= $this->form
					->btn(array('name' => 'cancel', 'value' => 'Отмена', 'class' => 'btn-default', 'modal' => 'close'))
					->btn(array('name' => 'delete', 'value' => 'Да, отказаться от сделки', 'class' => 'btn-primary'))
					->create(array('action' => current_url(), 'btn_offset' => 2));
				echo $this->load->view('ajax', $this->data, true);
			}
		}

	}

	public function accept($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'no_cleaner');
		if (empty($order_info)) {
			custom_404();
		}

		if ($this->order_model->is_busy($order_info)) {
			$this->session->set_flashdata('danger', 'Вы не можете взять этот заказ, так как уже заняты в это время');
			redirect('orders/detail/'.$order_id, 'refresh');
		}

		$update_array['cleaner_id'] = $this->data['user_info']['id'];
		$this->db->where('id', $order_id)->update('orders', $update_array);
		if ($order_info['invite_status'] === '0') {
			$this->db->where('id', $order_info['invite_id'])->update('order_invites', array('status' => 1));
		}
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

	public function reject_invite($order_id = false) {
		$order_id = intval($order_id);
		if (empty($order_id)) {
			custom_404();
		}

		$order_info = $this->order_model->get_user_order($order_id, false, 'no_cleaner');
		if (empty($order_info)) {
			custom_404();
		}

		if ($order_info['invite_status'] === '0') {
			$this->db->where('id', $order_info['invite_id'])->update('order_invites', array('status' => 2));
			$this->db->query('UPDATE orders SET recommended = 0 WHERE NOT EXISTS (SELECT * FROM order_invites WHERE order_id = '.$order_id.' AND status = 0)');
			$this->session->set_flashdata('success', 'Предложение по работе отклонено, но у вас остается возможность заключить эту сделку, если вы передумали');
		}
		redirect('orders', 'refresh');
	}

	public function read_invites() {
		$this->order_model->get_unread_invite_count($this->data['user_info']['id'], true);
		exit;
	}

	public function messages($order_id = false, $is_called = false) {
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

		if ($is_called) {
			$this->order_model->read_messages($order_id);
		}

		$this->data['order_messages'] = $this->order_model->get_order_messages($order_id);

		$this->data['message_form'] = $this->form
			->textarea('text', array(
				'valid_rules' => 'required|xss_clean|max_length[250]',
				'width'       => 12,
				'no_editor'   => true,
				'rows'        => 3,
				'placeholder' => 'Введите сообщение...',
			))
			->btn(array(
				'value'       => 'Отправить сообщение',
				'class'       => 'btn-primary btn-block',
			))
			->create(array('error_inline' => true, 'btn_offset' => 3, 'btn_width' => 6));
		$this->data['center_block'] .= $this->load->view('orders/order_messages', $this->data, true);

		if ($this->form_validation->run() != false) {
			$this->db->insert('order_messages', array(
				'text'        => $this->input->post('text'),
				'order_id'    => $order_id,
				'sender_id'   => $this->data['user_info']['id'],
				'reciever_id' => $this->data['order_info']['client_id'] == $this->data['user_info']['id'] ? $this->data['order_info']['cleaner_id'] : $this->data['order_info']['client_id'],
				'add_date'    => time(),
			));

			$this->session->set_flashdata('success', 'Сообщение успешно отправлено');
			redirect('orders/detail/'.$order_id, 'refresh');
		}
	}

	public function withdraw() {
		if (!$this->data['user_info']['is_cleaner']) {
			custom_404();
		}

		$this->data['title'] = $this->data['header'] = 'Запрос на снятие денег';

		$this->form_validation->set_message('greater_than', 'Минимальная сумма для снятия 1000 рублей');
		$this->data['center_block'] = $this->form
			->text('number', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[70]',
				'label'       => 'Номер счета или кошелька',
				'width'       => 12,
			))
			->text('name', array(
				'valid_rules' => 'required|trim|xss_clean|max_length[70]',
				'label'       => 'Название платежной системы',
				'width'       => 12,
			))
			->text('amount', array(
				'valid_rules' => 'required|trim|xss_clean|price|greater_than[999]',
				'label'       => 'Сумма на снятие',
				'width'       => 12,
			))
			->btn(array('value' => 'Отправить'))
			->create(array('action' => current_url(), 'error_inline' => 'true'));

		if ($this->form_validation->run() != FALSE) {
			$user_balance = $this->order_model->get_user_balance();
			if ($this->input->post('amount') > $user_balance) {
				$this->session->set_flashdata('danger', 'К сожалению, у вас недостаточно средств на счету');
				custom_redirect('orders', 'refresh');
			}

			$this->db->insert('user_payment_requests', array(
				'type'       => 'withdraw',
				'name'       => $this->input->post('name'),
				'number'     => $this->input->post('number'),
				'amount'     => $this->input->post('amount'),
				'user_id'    => $this->data['user_info']['id'],
				'add_date'   => time(),
			));
			$this->session->set_flashdata('success', 'Спасибо, ваша заявка на вывод денег принята. В ближайшее время с вами свяжутся');
			custom_redirect('orders', 'refresh');
		}

		load_views();

	}
}
