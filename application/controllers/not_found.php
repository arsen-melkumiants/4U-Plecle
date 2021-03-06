<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Not_found extends CI_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('main');

		set_alert($this->session->flashdata('success'), false, 'success');
		set_alert($this->session->flashdata('danger'), false, 'danger');
	}

	public function index() {
		$this->data['title'] = $this->data['header'] = lang('page_doesnt_exist');

		$this->output->set_status_header('404');
		$this->data['center_block'] = '<h1 class="text_404">404</h1>';


		$this->load->view('header', $this->data);
		$this->load->view('content_page', $this->data);
		$this->load->view('footer', $this->data);
	}
}
