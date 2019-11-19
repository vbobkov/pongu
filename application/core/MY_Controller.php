<?php
/* Vik Bobkov
* MY_Controller.php
*/

define('DEV', 255);
define('EMPLOYEE', 1);
define('GUEST', 0);

class MY_Controller extends CI_Controller {
	private static $admin_menu_items = array(
		'Sign In' => array(
			'url' => '/login',
			'target' => '',
			'allowed_users' => array(GUEST => 1)
			/*
			'submenu' => array(
				'' => array(
					'url' => '/',
					'target' => '',
					'allowed_users' => array(DEV => 1, EMPLOYEE => 1)
				),
				'' => array(
					'url' => '/',
					'target' => '',
					'allowed_users' => array(DEV => 1, EMPLOYEE => 1)
				)
			)
			*/
		),
		'Rankings' => array(
			'url' => '/rankings',
			'target' => '',
			'allowed_users' => array(DEV => 1, EMPLOYEE => 1)
		),
		'Sign Out' => array(
			'url' => '/login/logout',
			'target' => '',
			'allowed_users' => array(DEV => 1, EMPLOYEE => 1)
		)
	);



	public function __construct() {
		parent::__construct();
		$this->load->library('input');
		$this->load->library('session');
		$this->load->helper('url');
	}



	protected function buildMenu($menu_items, $url_prefix = '', $version = '') {
		$user_type = $this->session->userdata('type');
		if($user_type == null) {
			$user_type = 0;
		}
		return $this->buildMenuRecursive($menu_items, $user_type, $url_prefix);
	}

	private function buildMenuRecursive($menu, $user_type = null, $url_prefix = '') {
		$html = '';
		foreach($menu as $title => $item) {
			if(isset($item['allowed_users'][$user_type])) {
				$html .= '<span class="cssmenu-item link">';
				if(in_array($item['url'], array(null, '', '#'))) {
					$html .= $title;
				}
				else {
					$html .= '<a class="link" href="' . $url_prefix . $item['url'] . '" target="' . $item['target'] . '">' . $title . '</a>';
				}
				if(isset($item['submenu'])) {
					$html .= '<div class="cssmenu-item-list">' . $this->buildMenuRecursive($item['submenu'], $user_type) . '</div>';
				}
				$html .= '</span>';
			}
		}
		return $html;
	}



	protected function setUserSession($user_info = array()) {
		foreach($user_info as $key => $value) {
			$this->session->set_userdata($key, $value);
		}
	}

	protected function getCurrentURL() {
		$CI =& get_instance();
		$url = $CI->config->base_url($CI->uri->uri_string());
		return $_SERVER['QUERY_STRING'] ? $url.'?'.$_SERVER['QUERY_STRING'] : $url;
	}

	protected function filepathsToHTML($files, $start_html, $end_html, $add_timestamp = true) {
		$html = '';
		foreach($files as $file) {
			$html .= $this->filepathToHTML($file, $start_html, $end_html, $add_timestamp);
		}
		return $html;
	}

	protected function filepathToHTML($file, $start_html, $end_html, $add_timestamp = true) {
		if(strpos($file, 'http://') !== false || strpos($file, 'https://') !== false) {
			return $start_html . $file . $end_html;
		}
		else {
			if($add_timestamp) {
				if(file_exists($file)) {
					$file_version = '?v=' . filemtime($file);
				}
				else {
					$file_version = '?v=' . time();
				}
			}
			else {
				$file_version = '';
			}
			return $start_html . base_url($file) . $file_version . $end_html;
		}
	}



	protected function render($url, $data = array()) {
		if($this->input->is_ajax_request()) {
			$this->load->view($url);
		}
		else {
			$css_files = array();
			$js_files = array();
			if($this->session->userdata('user_id') != null && $this->session->userdata('type') > 0) {
				$css_files[] = 'assets/css/addons/jquery-ui.custom.css';
				$js_files[] = 'assets/js/addons/jquery-1.10.2.min.js';
				$js_files[] = 'assets/js/addons/jquery-ui.min.js';
				$js_files[] = 'assets/js/addons/json2.min.js';
				$js_files[] = 'assets/js/admin.js';
			}
			$css_files[] = 'assets/css/admin.css';

			// $version = filemtime('.git/index');
			$version = 1337;
			$t3h_menu = $this->buildMenu(MY_Controller::$admin_menu_items, '', $version);

			$this->load->view(
				'page_header',
				array(
					'header_includes' =>
						// $this->filepathToHTML('favicon.gif', '<link rel="icon" href="', '" type="image/gif">') .
						$this->filepathsToHTML($css_files, '<link href="', '" rel="stylesheet" type="text/css">', false) .
						$this->filepathsToHTML($js_files, '<script type="text/javascript" src="', '"></script>', false),
					'menu' => $t3h_menu
					// 'version' => $version
				)
			);
			$data['db_name'] = $this->db->database;
			$this->load->view($url, $data);
			$this->load->view('page_footer');
		}
	}
}

/* End of file MY_Controller.php */
/* Location: ./application/core/MY_Controller.php */