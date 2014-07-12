<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_user_model extends CI_Model {

	function __construct() {
		parent::__construct();
		$this->load->database();
	}

	function get_all_users($active = false) {
		if ($active !== false) {
			$this->db->where('active', $active);
		}
		return $this->db->select('*, active as status')->get('users');
	}

	function get_user_info($id) {
		return $this->db->where('id', $id)->get('users')->row_array();
	}

	public function upload_user_photo($user_info){
		if(empty($_FILES['photo']['name'])){
			return false;
		}

		$upload_folder = FCPATH.'uploads/avatars/';
		@mkdir($upload_folder, 0777, true);
		$config['upload_path']   = $upload_folder;
		$config['file_name']     = $_FILES['photo']['size'];
		$config['allowed_types'] = 'gif|jpg|png';
		$config['max_size']      = '10000';

		$this->load->library('upload', $config);

		if (!$this->upload->do_upload('photo')) {
			return $this->upload->display_errors();
		} else {
			@unlink($upload_folder.$user_info['photo']);
			$this->load->library('image_lib');

			$data = $this->upload->data();
			$this->resize_image($data, 150);
			$info['photo'] = basename($data['full_path']);

			$this->db->where('id', $user_info['id'])->update('users', $info);
			return true;
		}
	}


	function resize_image($data, $new_size = false, $dir = '') {
		if (empty($new_size)) {
			return false;
		}

		$origin_width = $data['image_width'];
		$origin_height = $data['image_height'];

		if (!empty($_POST['width']) && $_POST['width'] != 'NaN') {
			if($origin_width <= $origin_height) {
				$prop = $_POST['re_width'] / $_POST['width'];
				$new_width = round($origin_width / $prop);
				$new_height = $new_width;
			} else {
				$prop = $_POST['re_height'] / $_POST['height'];
				$new_height = round($origin_height / $prop);
				$new_width = $new_height;
			}
			$config['x_axis'] = $_POST['x1'] * ($origin_width / $_POST['re_width']);
			$config['y_axis'] = $_POST['y1'] * ($origin_height / $_POST['re_height']);
		} else {
			if($origin_width < $origin_height) {
				$new_width  = $origin_width;
				$new_height = $origin_width;
				$config['y_axis'] = ($origin_height - $new_width) / 2;
			} else {
				$new_height = $origin_height;
				$new_width  = $origin_height;
				$config['x_axis'] = ($origin_width - $new_height) / 2;
			}
		}

		$config['image_library']  = 'gd2';
		$config['source_image']   = $data['full_path'];
		$config['new_image']      = $data['full_path'];
		$config['quality']        = '100%';
		$config['width']          = $new_width;
		$config['height']         = $new_height;
		$config['maintain_ratio'] = false;

		$this->image_lib->initialize($config);
		$this->image_lib->crop();

		$config['image_library']  = 'gd2';
		$config['source_image']   = $data['full_path'];
		$config['new_image']      = $data['full_path'];
		$config['quality']        = '100%';
		$config['width']          = $new_size;
		$config['height']         = $new_size;
		$config['maintain_ratio'] = false;
		
		$this->image_lib->initialize($config);
		$this->image_lib->resize();

		return false;
	}
}
