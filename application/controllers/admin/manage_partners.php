<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manage_partners extends CI_Controller {

	public $MAIN_URL = '';

	public $IS_AJAX = false;

	public $DB_TABLE = 'partners';

	public $PAGE_INFO = array(
		'index'            => array(
			'header'       => 'Партнетры',
			'header_descr' => 'Список партнеров со ссылками на внешние сайты и ресурсы',
		),
		'add'              => array(
			'header'       => 'Добавления партнера',
			'header_descr' => 'Добавление информации о партнере',
		),
		'edit'             => array(
			'header'       => 'Редактирование "%name"',
			'header_descr' => 'Редактирование информации и изображения партнера',
		),
		'delete'           => array(
			'header'       => 'Удаление партнера "%name"',
			'header_descr' => false,
		),
	);

	function __construct() {
		parent::__construct();
		$this->config->set_item('sess_cookie_name', 'a_session');

		$this->load->library('ion_auth');
		if (!$this->ion_auth->is_admin()) {
			redirect(ADM_URL.'auth/login');
		}

		$this->MAIN_URL = ADM_URL.strtolower(__CLASS__).'/';
		admin_constructor();
	}

	public function index() {
		$this->load->library('table');
		$this->data['center_block'] = $this->table
			->text('name', array(
				'title'   => 'Имя',
			))
			->text('link', array(
				'title'   => 'Ссылка',
				'func'    => function ($row, $params, $that, $CI) {
					return '<a href="'.prep_url($row['link']).'">'.$row['link'].'</a>';
				}
		))
			->text('image', array(
				'title'   => 'Изображение',
				'func'    => function ($row, $params, $that, $CI) {
					if (!empty($row['image'])) {
						return '<img src="/uploads/partners/'.$row['image'].'" alt="'.$row['name'].'" height="50"/>';
					} else {
						return '<span class="label label-warning">Отсутствует</span>';
					}
				}
		))
			->edit(array('link' => $this->MAIN_URL.'edit/%d'))
			->active(array('link' => $this->MAIN_URL.'active/%d'))
			->delete(array('link' => $this->MAIN_URL.'delete/%d', 'modal' => 1))
			->btn(array(
				'link'   => $this->MAIN_URL.'add',
				'name'   => 'Добавить',
				'header' => true,
			))
			->create(function($CI) {
				return $CI->db->get($CI->DB_TABLE);
			});

		load_admin_views();
	}
	
	public function add() {
		$this->data['center_block'] = $this->edit_form();
		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('add', $this->DB_TABLE, array(
				'except_fields' => array('add_date', 'author_id'),
				'redirect_url'  => function($CI) {
					$id = $CI->db->insert_id();
					if (!empty($id)) {
						return $CI->MAIN_URL.'edit/'.$id;
					}
				},
			));
		}
	}

	public function edit($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$partner_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($partner_info)) {
			custom_404();
		}
		set_header_info($partner_info);

		$this->data['center_block'] = $this->edit_form($partner_info);
		$this->upload_image($id, true);

		if ($this->form_validation->run() == FALSE) {
			load_admin_views();
		} else {
			admin_method('edit', $this->DB_TABLE, array('id' => $id));
		}
	}

	private function edit_form($partner_info = false) {
		$this->load->library('form');
		return $this->form
			->text('name', array(
				'value'       => $partner_info['name'] ?: false,
				'valid_rules' => 'required|trim|xss_clean',
				'label'       => 'Имя',
			))
			->text('link', array(
				'value'       => $partner_info['link'] ?: false,
				'valid_rules' => 'required|trim|xss_clean|prep_url',
				'label'       => 'Ссылка',
			))
			->btn(array('value' => 'Изменить'))
			->create(array('action' => current_url()));
	}
	
	public function active($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$partner_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($partner_info)) {
			custom_404();
		}
		admin_method('active', $this->DB_TABLE, $partner_info);
	}
	
	public function delete($id = false) {
		if (empty($id)) {
			custom_404();
		}
		$partner_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($partner_info)) {
			custom_404();
		}
		set_header_info($partner_info);

		admin_method('delete', $this->DB_TABLE, $partner_info, function($info, $CI) {
			if (empty($info['image'])) {
				return false;
			}
			@unlink(FCPATH.'uploads/partners/'.$info['image']);
		});
	}

	public function upload_image($id = false, $is_called = false) {
		if (empty($id)) {
			custom_404();
		}
		$partner_info = $this->db->where('id', $id)->get($this->DB_TABLE)->row_array();

		if (empty($partner_info)) {
			custom_404();
		}

		$upload_folder = FCPATH.'uploads/partners/';
		@mkdir($upload_folder, 0777, true);
		$config['upload_path']   = $upload_folder;
		$config['file_name']     = !empty($_FILES['image']['size']) ? $_FILES['image']['size'] : time();
		$config['allowed_types'] = 'gif|jpg|jpeg|png';
		$config['max_size']      = '10000';

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('image')) {
			$this->data['image_full_path'] = !empty($partner_info['image']) ? '/uploads/partners/'.$partner_info['image'] : false;

			$this->load->library('form');
			$this->data['upload_form'] = $this->form
				->file('image', array('label' => 'Изображение'))
				->btn(array('value' => 'Загрузить', 'class' => 'btn-primary'))
				->create(array('upload' => true, 'action' => site_url($this->MAIN_URL.'upload_image/'.$id)));
			if (!empty($this->data['center_block'])) {
				$this->data['center_block'] .= $this->load->view(ADM_FOLDER.'upload_no_js', $this->data, true);
			}
			if (!empty($_FILES)) {
				$this->session->set_flashdata('danger', $this->upload->display_errors());
				redirect($this->MAIN_URL.'edit/'.$id, 'refresh');
			}
		} else {
			@unlink($upload_folder.$user_info['image']);
			$this->load->library('image_lib');
			$data = $this->upload->data();

			$this->image_lib->initialize(array(
				'source_image'   => $data['full_path'],
				'new_image'      => $data['full_path'],
				'quality'        => '100%',
				'height'         => '100',
				'width'          => '450',
				'maintain_ratio' => true,
			));
			$this->image_lib->resize();

			$info['image'] = basename($data['full_path']);

			$this->db->where('id', $id)->update('partners', $info);
			$this->session->set_flashdata('success', 'Фото успешно добавлено');
			redirect($this->MAIN_URL.'edit/'.$id, 'refresh');
		}
	}
}
