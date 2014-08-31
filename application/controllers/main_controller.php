<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Main_controller extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
		));
		$this->data['main_menu']     = $this->menu_model->get_menu('main');
		$this->data['help_menu']     = $this->menu_model->get_menu('help');
		$this->data['services_menu'] = $this->menu_model->get_menu('services');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	public function index() {
		$this->data['title'] = SITE_NAME;
		$this->data['center_block'] = '';
		if (empty($this->ion_auth->user()->row()->is_cleaner)) {
			$this->data['center_block'] .= $this->load->view('main_form', $this->data, true);
		}
		$this->data['partners'] = $this->db->where(array('status' => 1, 'image !=' => ''))->get('partners')->result_array();
		$this->data['center_block'] .= $this->load->view('promo_page', $this->data, true);
		$this->data['regions'] = $this->db->get('regions')->result_array();

		$this->load->view('header', $this->data);
		$this->load->view('main_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	function menu_content($name = false) {
		if (empty($name)) {
			custom_404();
		}

		$menu_info = $this->db->select('*, name_'.$this->config->item('lang_abbr').' as name')->where('alias', $name)->get('menu_items')->row_array();
		if (empty($menu_info)) {
			return $this->content($name);
		}

		$this->data['title'] = $this->data['header'] = $menu_info['name'];

		if ($menu_info['type'] == 'content') {
			$content_info = $this->db->select('*, name_'.$this->config->item('lang_abbr').' as name, content_'.$this->config->item('lang_abbr').' as content')->where('id', $menu_info['item_id'])->get('content')->row_array();
			if (empty($content_info)) {
				custom_404();
			}
			$this->data['title'] = $this->data['header'] = $content_info['name'];
			$this->data['center_block'] = '<div>'.$content_info['content'].'</div>';
		} else {
			custom_404();
		}

		$this->load->view('header', $this->data);
		$this->load->view('content_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	function content($name = false) {
		if (empty($name)) {
			custom_404();
		}

		$content_info = $this->db->select('*, name_'.$this->config->item('lang_abbr').' as name, content_'.$this->config->item('lang_abbr').' as content')->where('alias', $name)->get('content')->row_array();
		if (empty($content_info)) {
			custom_404();
		}

		$this->data['title'] = $this->data['header'] = $content_info['name'];
		$this->data['center_block'] = '<div>'.$content_info['content'].'</div>';

		$this->load->view('header', $this->data);
		$this->load->view('content_page', $this->data);
		$this->load->view('footer', $this->data);
	}

	function region($id = false) {
		$id = intval($id);
		if (empty($id)) {
			custom_404();
		}

		$region_info = $this->db->where('id', $id)->get('regions')->row_array();
		if (empty($region_info)) {
			custom_404();
		}

		$this->data['title'] = $this->data['header'] = 'Район "'.$region_info['name'].'"';
	}
}
