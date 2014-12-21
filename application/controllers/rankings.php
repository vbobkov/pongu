<?php
/* Vic Bobkov
* rankings.php
*/

class Rankings extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('Users_model');
		if($this->session->userdata('user_id') == null || $this->session->userdata('type') < 1) {
			redirect(base_url('/login'), 'refresh');
		}
	}

	public function index() {
		$this->render('rankings');
	}

	public function editDBConfig() {
		if($this->session->userdata('type') != 255) {
			show_404();
			return;
		}
		$this->load->model('Users_model');
		if($this->input->post()) {
			$post = $this->input->post();
			$new_config = array();
			$checkbox_names = array('pconnect', 'db_debug', 'cache_on', 'autoinit', 'stricton');

			$t0k3nz;
			foreach($post as $input_name => $value) {
				$t0k3nz = explode('__', $input_name);
				if(!isset($new_config[$t0k3nz[1]])) {
					$new_config[$t0k3nz[1]] = array();
				}
				if(in_array($t0k3nz[2], $checkbox_names)) {
					if($value == 'on') {
						$new_config[$t0k3nz[1]][$t0k3nz[2]] = true;
					}
					else {
						$new_config[$t0k3nz[1]][$t0k3nz[2]] = false;
					}
				}
				else {
					$new_config[$t0k3nz[1]][$t0k3nz[2]] = $value;
				}
			}
			$this->Users_model->updateDBConfig($new_config);
		}
		$this->render(
			'admin_edit_db_config',
			array(
				'db_config' => $this->Users_model->getDBConfig()
			)
		);
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */
