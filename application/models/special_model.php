<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Special_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
		$this->load->library('parser');
	}

	function get_spec_content($var = false, $data = false) {
		if (empty($var)) {
			return false;
		}

		$info = $this->db->select('content_'.$this->config->item('lang_abbr').' as content')->where('var', $var)->get('special_content')->row_array();
		if (empty($info)) {
			return false;
		}
		return $this->parser->parse_string($info['content'], (array)$data, true);
	}

	function get_favorite_users($user_id = false) {
		return $this->db->select('f.*, u.first_name, u.last_name, u.photo')
			->from('favorites AS f')
			->join('users AS u', 'u.id = f.user_id')
			->where('f.owner_id', $user_id)
			->group_by('u.id')
			->get()
			;
	}
}
