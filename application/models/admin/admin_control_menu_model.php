<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Admin_control_menu_model extends CI_Model
{
	var $menus = array();

	function __construct()
	{
		parent::__construct();
		$this->load->database();

		$this->menus = array(
			'top'                               => array(
				'Сайт'                          => array(
					'global_settings'           => 'Глобальные настройки',
					'change_access'             => 'Смена доступа в админ-панель',
					'logout'                    => 'Выйти',
				),
				'Меню'                          => array(
					'manage_menu/main'          => 'Главное меню',
					'manage_menu/help'          => 'Справка',
					'manage_menu/services'      => 'Услуги',
				),
				'Контент'                       => array(
					'manage_content'            => 'Список контента',
					'manage_content/add'        => 'Добавить контент',
					'1'                         => '',
					'manage_content/categories' => 'Категории контента',
					'2'                         => '',
					'manage_partners'           => 'Партнеры',
				),
				'Сделки'                        => array(
					'manage_order'              => 'Общий список сделок',
					'manage_order/active'       => 'Список активных сделок',
					'manage_order/completed'    => 'Список завершенных сделок',
					'1'                         => '',
					'manage_order/regions'      => 'Список регионов',
					'2'                         => '',
					'manage_order/payments'     => 'Список выплат',
				),
				'Пользователи'                  => array(
					'manage_user'               => 'Все пользователи',
					'manage_user/cleaners'      => 'Работники',
					'manage_user/clients'       => 'Клиенты',
					'manage_user/activated'     => 'Активированные',
					'manage_user/inactivated'   => 'Неактивированные',
				),
				/*
				'История операций'                       => array(
					'manage_history'                     => 'Все операции',
					'manage_history/refillng'            => 'Пополнения',
					'manage_history/withdrawing'         => 'Снятия',
					'manage_history/facilities'          => 'Услуги',
				),
				'Статистика'                             => array(
					'manage_statistic'                   => 'Общая статистика',
					'manage_statistic/paid_products'     => 'Статистика по продажам',
					'manage_statistic/user_incomes'      => 'Статистика по выплатам',
				),*/
			),
		);

	}

	function get_control_menu($name = false){
		if($name){
			$current_menu = isset($this->menus[$name]) ? $this->menus[$name] : '';
			if(empty($current_menu)){
				return false;
			}

			if($this->ion_auth->is_admin()){
				return $this->generate_html_menu($current_menu);
			}


		}
	}


	function generate_html_menu($menu = false){
		if(empty($menu)){
			return false;
		}

		$html = '<header class="navbar navbar-default navbar-fixed-top">
			<div class="container">
			<div class="navbar-header">
			<button class="navbar-toggle" type="button" data-toggle="collapse" data-target=".general_menu">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			</button>
			<a href="'.base_url(ADM_URL).'" class="navbar-brand">4U</a>
			</div>
			<nav class="collapse navbar-collapse general_menu" role="navigation">
			<ul class="nav navbar-nav">';
		foreach($menu as $name => $items){
			if(empty($items)){
				continue;
			}
			if(is_array($items)){
				$html .= '<li class="dropdown">'."\n".
					'<a href="#" class="dropdown-toggle" data-toggle="dropdown">'.$name.' <b class="caret"></b></a>'."\n".
					'<ul class="dropdown-menu">';
				foreach($items as $link => $subname){
					if(!empty($subname)){
						$html .= '<li><a href="'.site_url(ADM_URL.$link).'">'.$subname.'</a></li>';
					}else{
						$html .= '<li class="divider"></li>';
					}
				}
				$html .= '</ul></li>';
			}
		}
		$html .= '</ul>
			</nav>
			</div>
			</header>';

		return $html;
	}
}
