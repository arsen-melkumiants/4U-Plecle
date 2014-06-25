<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Not_found extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->model(array(
			'menu_model',
			'shop_model',
		));
		$this->load->library(array(
			'session',
			'ion_auth',
		));
		$this->data['main_menu']  = $this->menu_model->get_menu('upper');
		$this->data['left_block'] = $this->shop_model->get_categories();
    }

	public function index() {
		$this->data['title'] = $this->data['header'] = lang('page_doesnt_exist');
		
		$this->output->set_status_header('404');
		$this->data['center_block'] = '<h1 class="text_404">404</h1>';
	
		load_views();
	}
}