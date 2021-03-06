<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Form {

	public $form_data         = array();

	public $btn_data          = array();

	public $grid_type         = 'col-md';

	public $ajax_mode         = false;

	public $load_editor       = false;

	public $load_selectpicker = false;

	public function __construct($grid_type = false) {
		$CI =& get_instance();
		$this->grid_type = $grid_type ? $grid_type : $this->grid_type;
		$this->ajax_mode = $CI->input->is_ajax_request();
	}

	public function __call($method, $arguments) {
		if (!method_exists( $this, $method)) {
			throw new Exception('Undefined method Form_creator::' . $method . '() called');
		}

		return call_user_func_array(array($this, $method), $arguments);
	}

	public function attributes($list = false, $data = false) {
		if (empty($list) || empty($data)) {
			return false;
		}
		$attrs = '';
		foreach ($list as $name) {
			if (!isset($data[$name])) {
				continue;
			}
			$attrs .= ' '.$name.'="'.trim($data[$name]).'"';
		}
		return $attrs;
	}

	public function func($func = false, $params = false) {
		if (empty($func)) {
			return $this;
		}

		$this->form_data[] = array(
			'form'   => $func,
			'params' => $params,
		);

		return $this;
	}

	public function input($name = false, $params = false, $type = "text") {
		if (empty($name)) {
			return $this;
		}

		$params['name']  = $name;
		$params['type']  = $type;
		$params['value'] = isset($params['value']) ? $params['value'] : '';
		$params['label'] = !empty($params['label']) ? $params['label'] : '';

		$CI =& get_instance();
		$CI->load->library('form_validation');
		if (!empty($params['valid_rules'])) {
			$field_name = !empty($params['label']) ? $params['label'] : (!empty($params['placeholder']) ? $params['placeholder'] : ucfirst($name));
			$CI->form_validation->set_rules($name, $field_name, $params['valid_rules']);
			$CI->form_validation->run();
			if ($type != 'radio' && $type != 'checkbox') {
				$params['value'] = set_value($name, $params['value']);
			}
			$params['error'] = !empty($params['error']) ? $params['error'] : form_error($name);
		}

		$params['class'] = !empty($params['class']) ? 'form-control '.$params['class'] : 'form-control';

		$addon = '';
		if (!empty($params['icon'])) {
			$addon = '<i class="icon-'.$params['icon'].'"></i>';
		}elseif (!empty($params['symbol'])) {
			$addon = $params['symbol'];
		}

		if (empty($params['width'])) {
			$params['width'] = !empty($params['label']) ? ($this->ajax_mode ? 6 : 4) : 12;
		}else{
			$params['width'] = ($this->ajax_mode && $params['width'] <= 10 ? $params['width'] + 2 : $params['width']);
		}

		$params['label_width'] = !empty($params['label_width']) ? $params['label_width'] : 3;

		$label = !empty($params['label']) ? '<label class="'.$this->grid_type.'-'.($params['width'] == 12 ? 12 : $params['label_width']).' control-label">'.$params['label'].'</label>' : '';

		if(empty($params['error_width'])) {
			$params['error_width'] = !empty($params['label']) ? 12 - 3 - $params['width'] : 12 - $params['width'];
			$params['error_width'] = $params['error_width'] > 2 ? $params['error_width'] : 12;
		}

		$params['width'] = $this->grid_type.'-'.$params['width'];

		//offset
		$params['width'] .= !empty($params['offset']) ? ' '.$this->grid_type.'-offset-'.$params['offset'] : '';

		//radio-buttons
		$input = '';
		if (($type == 'radio' || $type == 'checkbox') && isset($params['inputs']) && is_array($params['inputs'])) {
			$input .= !empty($params['btn_view']) ? '<div class="btn-group" data-toggle="buttons">' : '';
			foreach ($params['inputs'] as $value => $info) {
				if (is_array($info)) {
					$input_name = $info['name'];
				} else {
					$input_name = $info;
				}

				if ($type == 'checkbox') {
					$name = $value;
					$value = is_array($info) && !empty($info['value']) ? $info['value'] : 1;

					$input_checked = !empty($params['value'][$name]) && !isset($_POST[$name]) ? 'checked="checked"' : false;

					if (strpos($name, '[') !== false) {
						list($var_name, $check_key) = explode('[', $name);
						$check_key = str_replace(']', '' , $check_key);
						if (!empty($_POST[$var_name][$check_key])) {
							$input_checked = 'checked="checked"';
						}
					} else {
						if ($CI->input->post($name)) {
							$input_checked = 'checked="checked"';
						}
					}

				} else {
					$input_checked = set_radio($name, $value);
				}

				if (empty($input_checked)) {
					$input_checked = $value == $params['value'] ? ' checked="checked"' : false;
				}

				if (!empty($params['btn_view'])) {
					$input .= '<label class="btn btn-primary'.($input_checked ? ' active' : '').'">'.PHP_EOL;
				} else {
					$input .= '<label class="'.$type.(isset($params['inline']) && !$params['inline'] ? '' : '-inline').'">'.PHP_EOL;
				}
				$readonly = !empty($params['disabled']) ? ' disabled="disabled"' : '';
				$input .= '<input type="'.$type.'" name="'.$name.'" value="'.$value.'"'.$input_checked.$readonly.'> '.$input_name.PHP_EOL;
				$input .= '</label>'.PHP_EOL;
			}
			$input .= !empty($params['btn_view']) ? '</div>' : '';
		} elseif ($type == 'select') {
			if (isset($params['options']) && is_array($params['options'])) {
				$attrs_list = array('class','name', 'data-live-search', 'disabled');
				$input .= '<select '.$this->attributes($attrs_list, $params).'>';
				foreach ($params['options'] as $value => $info) {
					if (is_array($info)) {
						$select_name = $info['name'];
						$value = $info['id'];
					} else {
						$select_name = $info;
					}
					$selected = isset($params['value']) && $params['value'] == $value ? ' selected="selected"' : '';
					$input .= '<option value="'.$value.'"'.$selected.'>'.$select_name.'</option>'.PHP_EOL;
				}
				$input .= '</select>';
			}
		} elseif ($type == 'textarea') {
			$attrs_list = array('class','name','readonly','rows','placeholder');
			$input .= '<textarea'.$this->attributes($attrs_list, $params).'>'.$params['value'].'</textarea>';
		} else {
			if ($type == 'checkbox') {
				$params['class'] = 'checkbox';
			}
			$attrs_list = array('type','class','name','value','placeholder','readonly','disabled');
			$input .= '<input'.$this->attributes($attrs_list, $params).'/>';
		}

		if (!empty($addon)) {
			$addon = '<span class="input-group-addon">'.$addon.'</span>';
			if (isset($params['icon_post']) && $params['icon_post'] == 'right') {
				$input = $input.$addon;
			}else{
				$input = $addon.$input;
			}
			$input = '<div class="input-group '.$params['width'].'">'.$input.'</div>'.PHP_EOL;
		}else{
			$input = '<div class="'.$params['width'].'">'.$input.'</div>'.PHP_EOL;
		}

		$this->form_data[] = array(
			'form' => $label.$input,
			'params' => $params
		);
		return $this;
	}

	public function text($name = false, $params = false) {
		$this->input($name, $params, 'text');
		return $this;
	}

	public function checkbox($name = false, $params = false) {
		$this->input($name, $params, 'checkbox');
		return $this;
	}

	public function file($name = false, $params = false) {
		$this->input($name, $params, 'file');
		return $this;
	}

	public function password($name = false, $params = false) {
		$this->input($name, $params, 'password');
		return $this;
	}

	public function date($name = false, $params = false) {
		$params['width'] = !empty($params['width']) ? $params['width'] : 3;
		$params['icon'] = isset($params['icon']) ? $params['icon'] : 'calendar';
		$params['icon_pos'] = !empty($params['icon_pos']) ? $params['icon_pos'] : 'right';
		$params['class'] = isset($params['class']) ? $params['class'] : 'date_time';
		$params['type'] = !empty($params['type']) ? $params['type'] : 'Y-m-d';
		$params['value'] = !empty($params['value']) ? date($params['type'], $params['value']) : '';

		$this->input($name, $params, 'text');
		return $this;
	}

	public function btn($params = false) {
		if (!is_array($params)) {
			return $this;
		}
		$name = !empty($params['name']) ? $params['name'] : 'submit';
		$params['type'] = 'submit';
		$params['class'] = !empty($params['class']) ? 'btn '.$params['class'] : 'btn btn-primary';
		$params['value'] = isset($params['value']) ? $params['value'] : ucfirst($name);
		$params['modal'] = !empty($params['modal']) ? ($params['modal'] == 'close' ? ' data-dismiss="modal"' : ' data-toggle="modal" data-target="#ajaxModal"') : '' ;

		$attrs_list = array('class', 'name', 'modal', 'value', 'type', 'style');
		$btn = '<button'.$this->attributes($attrs_list, $params).'>'.$params['value'].'</button>'.PHP_EOL;
		$this->btn_data[] = array(
			'form' => $btn,
			'params' => $params
		);
		return $this;
	}

	public function link($params = false) {
		$params['name'] = !empty($params['name']) ? $params['name'] : 'link';
		$params['class'] = !empty($params['class']) ? $params['class'] : 'btn btn-primary';
		$params['href'] = isset($params['href']) ? $params['href'] : '#';
		$params['modal'] = !empty($params['modal']) ? ($params['modal'] == 'close' ? ' data-dismiss="modal"' : ' data-toggle="modal" data-target="#ajaxModal"') : '' ;

		$attrs_list = array('class','href','modal','style');
		$btn = '<a'.$this->attributes($attrs_list, $params).'>'.$params['name'].'</a>'.PHP_EOL;
		$this->btn_data[] = array(
			'form' => $btn,
			'params' => $params
		);
		return $this;
	}

	public function separator($text = '&nbsp;', $params = false) {
		$this->form_data[] = array(
			'form'   => '<div class="'.$this->grid_type.'-12">'.$text.'</div>',
			'params' => $params,
		);
		return $this;
	}

	public function hidden($name = false, $value = false, $params = false) {
		if (empty($name)) {
			return $this;
		}
		$params['value'] = $value;
		$this->input($name, $params, 'hidden');
		return $this;
	}

	public function radio($name = false, $params = false) {
		if (empty($name) || empty($params['inputs'])) {
			return $this;
		}
		$this->input($name, $params, 'radio');
		return $this;
	}

	public function textarea($name = false, $params = false) {
		if (empty($params['no_editor'])) {
			$params['class'] = !empty($params['class']) ? $params['class'].' ckeditor' : 'ckeditor';
			if (!$this->load_editor) {
				$this->load_editor = true;
			}
		}

		if (empty($params['full_width'])) {
			$params['width'] = !empty($params['width']) ? $params['width'] : 9;
		}
		$this->input($name, $params, 'textarea');
		return $this;
	}

	public function select($name = false, $params = false) {
		if (!empty($params['search'])) {
			$params['data-live-search'] = 'true';
			$params['class'] = (!empty($params['class']) ? $params['class'].' ' : '').'selectpicker';
			if (!$this->load_selectpicker) {
				$this->load_selectpicker = true;
			}
		}
		$this->input($name, $params, 'select');
		return $this;
	}

	public function create($params = false) {
		$html = '';
		$CI =& get_instance();

		//js editor for textarea
		if (file_exists(FCPATH.'dist/ckeditor/ckeditor.js') && $this->load_editor && $this->load_editor !== 'exists') {
			$this->load_editor = 'exists';
			$CI->output->set_output('<script>window.onload = function(){CKEDITOR.config.allowedContent = true;};</script>');
			after_load('js', '/dist/ckeditor/ckeditor.js');
		}

		//selector
		if (file_exists(FCPATH.'dist/bs-select/bootstrap-select.js') && $this->load_selectpicker && $this->load_selectpicker !== 'exists') {
			$this->load_selectpicker = 'exists';
			after_load('js',  '/dist/bs-select/bootstrap-select.js');
			after_load('css', '/dist/bs-select/bootstrap-select.min.css');
		}

		$params['method'] = !empty($params['method']) ? $params['method'] : 'post';
		$params['class'] = !empty($params['class']) ? ' class="'.$params['class'].'" ' : '';

		$params['action'] = !empty($params['action']) ? $params['action'] : '';
		$get_vars = !empty($_GET) ? '?'.http_build_query($_GET) : '';
		$params['action'] = $params['action'].$get_vars;
		$params['upload'] = !empty($params['upload']) ? ' enctype="multipart/form-data"' : false;

		if (empty($params['no_form_tag'])) {
			$html .= '<form class="form-horizontal" method="'.$params['method'].'" action="'.$params['action'].'"'.$params['upload'].'>'.PHP_EOL;
		}
		$html .= '<div'.$params['class'].'>'.PHP_EOL;
		$html .= !empty($params['title']) ? '<h3>'.$params['title'].'</h3>'.PHP_EOL : '';
		$html .= !empty($params['info']) ? '<p>'.$params['info'].'</p>'.PHP_EOL : '';
		foreach ($this->form_data as $item) {
			//Set global alert
			if (!empty($item['params']['error'])) {
				if (empty($params['error_inline']) && function_exists('set_alert')) {
					set_alert(form_error($item['params']['name']), false, 'danger');
				} else {
					$item['params']['error_html'] = '<div class="error text-danger '.$this->grid_type.'-'.$item['params']['error_width'].'">'.$item['params']['error'].'</div>';
				}
			}
			$group_class = !empty($item['params']['group_class']) ? ' '.$item['params']['group_class'] : '';
			if (is_callable($item['form'])) {
				$html .= $item['form']($item['params'], $CI);
			} elseif (isset($item['params']['type']) && $item['params']['type'] == 'hidden') {
				$html .= $item['form'].PHP_EOL;
			} else {
				$item['params']['id'] = !empty($item['params']['id']) ? ' id="'.$item['params']['id'].'"' : '';
				$html .= '<div class="form-group'.(!empty($item['params']['error']) ? ' has-error' : '').$group_class.'"'.$item['params']['id'].'>'.PHP_EOL.
					$item['form'].(!empty($params['error_inline']) && !empty($item['params']['error']) ? $item['params']['error_html'] : '').PHP_EOL.
					'</div>'.PHP_EOL;
			}
		}

		if (!empty($this->btn_data)) {
			$item['params']['id'] = !empty($item['params']['id']) ? ' id="'.$item['params']['id'].'"' : '';
			$html .= '<div class="form-group'.'"'.$item['params']['id'].'>'.PHP_EOL;

			$params['btn_offset'] = isset($params['btn_offset']) ? $params['btn_offset'] : 3;
			$params['class'] = !empty($params['btn_offset']) ? $this->grid_type.'-'.(!empty($params['btn_width']) ? $params['btn_width'] : (12 - $params['btn_offset'])).' '.$this->grid_type.'-offset-'.$params['btn_offset'] : $this->grid_type.'-12';

			$html .= '<div class="'.$params['class'].'">'.PHP_EOL;;

			foreach ($this->btn_data as $item) {
				$html .= $item['form'].PHP_EOL;
			}
			$html .= '</div></div>'.PHP_EOL;
		}

		$html .= '</div>'.PHP_EOL;
		if (empty($params['no_form_tag'])) {
			$html .= '</form>'.PHP_EOL;
		}
		$this->clear();

		return $html;
	}

	public function clear() {
		$this->form_data = array();
		$this->btn_data = array();
		return $this;
	}
}
