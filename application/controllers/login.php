<?php
/* Vic Bobkov
* login.php
*/

class Login extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('Users_model');
		if($this->session->userdata('user_id') == null || $this->session->userdata('type') < 1) {
			redirect(base_url('/login'), 'refresh');
		}
	}

	public function index() {
		if($this->session->userdata('user_id') != null) {
			redirect(base_url('/admin'), 'refresh');
		}

		if(!$this->input->post()) {
			$this->renderAdmin('login', array('username' => ''));
		}
		else {
			$result = $this->Users_model->getUser(array('username' => '' . $this->input->post('username')));
			if(sizeof($result) > 0 && $this->getPassHash($this->input->post('password'), $result[0]['password_salt']) == $result[0]['password']) {
				$this->setUserSession(array(
					'type' => $result[0]['type'],
					'user_id' => $result[0]['id'],
					'username' => $result[0]['username']
				));
				redirect(base_url('/admin'), 'refresh');
			}
			else {
				$this->renderAdmin('login', array('username' => $this->input->post('username')));
			}
		}
	}

	public function change_password() {
		if($this->session->userdata('username') == null || $this->session->userdata('username') == '') {
			show_404();
			return;
		}

		if($this->session->userdata('type') == 255 && $this->input->post('username') != null) {
			$username = $this->input->post('username');
		}
		else {
			$username = $this->session->userdata('username');
		}
		$message = '&lt;0x5F3759DF&gt;: strong password is advised';

		if($this->input->post()) {
			if($this->session->userdata('type') != 255 && $this->session->userdata('username') != $this->input->post('username')) {
				$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">WAT J00 TRYING TO DO SCRIPT KIDDIE??</span>';
			}
			else {
				$result = $this->Users_model->getUser(array('username' => '' . $username));
				if($this->input->post('password_new') == null && $this->input->post('password_new') == ''){
					$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">new pass cannot be blank</span>';
				}
				else if($this->input->post('password_new') != $this->input->post('password_new_confirmation')){
					$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">confirmation and new pass do not match</span>';
				}
				else if(sizeof($result) > 0 && $this->getPassHash($this->input->post('password_old'), $result[0]['password_salt']) == $result[0]['password']) {
					$salt = hash('sha512', microtime());
					if($this->Users_model->updateUser(
						$result[0]['id'],
						array(
							'password' => $this->getPassHash($this->input->post('password_new'), $salt),
							'password_salt' => $salt)))
					{
						$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(0,192,0)">pass change is great success</span>';
					}
				}
				else {
					$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">old password is wrong</span>';
				}
			}
		}

		$this->renderAdmin('change_password', array(
			'message' => $message,
			'username' => $username
		));
	}

	public function new_user() {
		if($this->session->userdata('username') == null || $this->session->userdata('username') == '' || $this->session->userdata('type') != 255) {
			show_404();
			return;
		}

		$message = '&lt;0x5F3759DF&gt;: make it so';
		$username = '';
		$type = '';
		if($this->input->post()) {
			if($this->input->post('password_new') == null && $this->input->post('password_new') == ''){
				$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">new pass cannot be blank</span>';
			}
			else if($this->input->post('password_new') != $this->input->post('password_new_confirmation')){
				$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">confirmation and new pass do not match</span>';
			}
			else {
				$username = $this->input->post('username');
				$result = $this->Users_model->getUser(array('username' => '' . $username));
				if(sizeof($result) > 0) {
					$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">user already exists</span>';
				}
				else {
					$salt = hash('sha512', microtime());
					$column_names = array('id', 'username', 'type', 'password', 'password_salt', 'fname', 'lname');
					$new_user = array(
						'id' => -1,
						'username' => $username,
						'type' => $this->input->post('type'),
						'password' => $this->getPassHash($this->input->post('password_new'), $salt),
						'password_salt' => $salt,
						'fname' => $this->input->post('fname'),
						'lname' => $this->input->post('lname')
					);
					$result = $this->Users_model->importRows('users', 'id', array($new_user), $column_names, $column_names);
					if($result[0]['id'] != -1) {
						$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">successfully created user: ' . $username . '</span>';
						$username = '';
					}
					else {
						$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">user already exists: ' . $username . '</span>';
					}
				}
			}
		}
		$this->renderAdmin('new_user', array(
			'message' => $message,
			'username' => $username,
			'type' => $type
		));
	}

	public function delete_user() {
		if($this->session->userdata('username') == null || $this->session->userdata('username') == '' || $this->session->userdata('type') != 255) {
			show_404();
			return;
		}

		$message = '&lt;0x5F3759DF&gt;: make it so';
		$username = '';
		if($this->input->post()) {
			$username = $this->input->post('username');
			$this->Users_model->deleteFromTable('users', 'username', $params = array('ids' => array($username)));
			if(mysql_affected_rows() > 0) {
				$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">deleted user: ' . $username . '</span>';
			}
			else {
				$message = '&lt;0x5F3759DF&gt;: <span style="color: rgb(255,0,0)">user does not exist: ' . $username . '</span>';
			}
		}
		$this->renderAdmin('delete_user', array(
			'message' => $message,
			'username' => $username
		));
	}

	public function logout() {
		// $this->session->sess_destroy();
		$this->setUserSession(array(
			'type' => null,
			'user_id' => null,
			'username' => null
		));
		redirect(base_url('/login'), 'refresh');
	}

	// http://localhost/login/resetDBToFactorySettings?pw=Z0MGUb3rL33tH4X&ap=1
	public function resetDBToFactorySettings() {
		$pw = 'Z0MGUb3rL33tH4X';
		if(
			$this->input->get('pw') != $pw &&
			(
				$this->session->userdata('username') == null ||
				$this->session->userdata('username') == '' ||
				$this->session->userdata('type') != 255
			)
		) {
			show_404();
			return;
		}
		$pw = null;

		if($this->input->get('ap') != null && $this->input->get('ap') != '') {
			$add_players = true;
		}
		else {
			$add_players = false;
		}

		try {
			$plain_pdo = new PDO('mysql:host=' . $this->db->hostname . ';dbname=' . $this->db->database, $this->db->username, $this->db->password);
			$plain_pdo->exec("
				DROP TABLE IF EXISTS users;
				CREATE TABLE users (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,username VARCHAR(255) NOT NULL,type INT(3) NOT NULL DEFAULT 0,password VARCHAR(255),password_salt VARCHAR(255),fname VARCHAR(255),lname VARCHAR(255),UNIQUE(username));
				INSERT INTO users(username,type) VALUES('uberadmin',255);
				UPDATE users SET password='9830dda78497163c0e1ade48a16836ed50cf70e47c36e82bdd8cfa32fb645ed8ec7e7a63de11b9ab19ad6db0f8e4fa2e2bc3691d64adf5e5af7ea194b9adaa76', password_salt='56E17DBC5E931A64828406910A46D9CBF457E74E7BB17EBA367011E6F6DCB4B210D8C6482A79DF4240098DB4F4A44743A2635E89A233E321CB4896C71976E1A3', username='uberadmin', fname='Ub3r1337', lname='H4x0r' WHERE id=1;

				DROP TABLE IF EXISTS ratings;
				CREATE TABLE ratings (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					nickname VARCHAR(255),
					fname VARCHAR(255),
					lname VARCHAR(255),
					rating INT(11),
					wins INT(11),
					losses INT(11),
					UNIQUE(player)
				);
			");
			echo 'Nuked the database for great justice.<br />';

			if($add_players) {
				// INSERT INTO global_js(platform,filename,js) VALUES('VMS', 'vmsite', '');
				// UPDATE site_scripts SET js='', ads_json='{\"top\":[],\"left\":[],\"right\":[],\"bottom\":[]}' WHERE domain_key='dunzo-net';
				$sample1_vars = array(
					htmlentities(file_get_contents(FCPATH . 'application/core/settings/js/sample1/sample1_js.js'), ENT_NOQUOTES),
					file_get_contents(FCPATH . 'application/core/settings/js/sample1/sample1_ads.json'),
					htmlentities(file_get_contents(FCPATH . 'application/core/settings/html/sample1/home.html'), ENT_NOQUOTES)
				);

				$sample1 = $plain_pdo->prepare("
					INSERT INTO pages(active,visitor_type,domain_key,url,title,js_scripts,ads_json) VALUES(1,'paid','dunzo-net','10-unfortunate-personal-name-stories', '10 Unfortunate Personal Name Stories', '', '');
					INSERT INTO pages(active,visitor_type,domain_key,url,title,js_scripts,ads_json) VALUES(1,'organic','dunzo-net','10-unfortunate-personal-name-stories', '10 Unfortunate Personal Name Stories', '', '');

					INSERT INTO articles(page_id,position,title,bottom_content,gallery_width,gallery_height) VALUES(1, 0, '10 Unfortunate Personal Name Stories', 'The whole situation is really silly', 600, 622);
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 0, 'http://www.albinoblacksheep.com/flash/960/base.jpg', 'wat', 'dis is a test yo');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 1, '/uploads/__WP_2014_10__personal-1.jpg', '', 'The man with last name &apos;Cocaine&apos; who was arrested for drug possession');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 2, '/uploads/__WP_2014_10__personal-2.jpg', '', 'The Pakistani diplomat who was rejected because his name translates to &quot;biggest dick&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 3, '/uploads/__WP_2014_10__personal-3.jpg', '', 'The eight-year-old Sydney girl whose name is &lsquo;tearing her family apart&rsquo;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 4, '/uploads/__WP_2014_10__personal-4.jpg', '', '10-year-old Icelandic girl who was denied a passport because her name is &quot;Harriet&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 5, '/uploads/__WP_2014_10__personal-5.jpg', '', 'The woman who renamed herself Skywalker and was refused a passport for &quot;copyright reasons&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 6, '/uploads/__WP_2014_10__personal-6.jpg', '', 'The French woman denied access to the U.S. because the name on her passport sounds like Al Qaeda');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 7, '/uploads/__WP_2014_10__personal-7.jpg', '', 'The man named God who has credit issues because of it');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 8, '/uploads/__WP_2014_10__personal-8.jpg', '', 'The child who was refused a birthday cake with his name, Adolph Hitler, on it');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 9, '/uploads/__WP_2014_10__personal-9.jpg', '', 'The 9 year old who was granted permission to change her given name, Talula Does the Hula From Hawaii');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(1, 10, '/uploads/__WP_2014_10__personal-10.jpg', '', 'The Indiana officials who were against honoring four term mayor because of his name, Harry Baals');

					INSERT INTO articles(page_id,position,title,bottom_content,gallery_width,gallery_height) VALUES(2, 0, '10 Unfortunate Personal Name Stories', 'The whole situation is really silly', 600, 622);
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 0, 'http://www.albinoblacksheep.com/flash/960/base.jpg', 'wat', 'dis is a test yo');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 1, '/uploads/__WP_2014_10__personal-1.jpg', '', 'The man with last name &apos;Cocaine&apos; who was arrested for drug possession');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 2, '/uploads/__WP_2014_10__personal-2.jpg', '', 'The Pakistani diplomat who was rejected because his name translates to &quot;biggest dick&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 3, '/uploads/__WP_2014_10__personal-3.jpg', '', 'The eight-year-old Sydney girl whose name is &lsquo;tearing her family apart&rsquo;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 4, '/uploads/__WP_2014_10__personal-4.jpg', '', '10-year-old Icelandic girl who was denied a passport because her name is &quot;Harriet&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 5, '/uploads/__WP_2014_10__personal-5.jpg', '', 'The woman who renamed herself Skywalker and was refused a passport for &quot;copyright reasons&quot;');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 6, '/uploads/__WP_2014_10__personal-6.jpg', '', 'The French woman denied access to the U.S. because the name on her passport sounds like Al Qaeda');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 7, '/uploads/__WP_2014_10__personal-7.jpg', '', 'The man named God who has credit issues because of it');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 8, '/uploads/__WP_2014_10__personal-8.jpg', '', 'The child who was refused a birthday cake with his name, Adolph Hitler, on it');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 9, '/uploads/__WP_2014_10__personal-9.jpg', '', 'The 9 year old who was granted permission to change her given name, Talula Does the Hula From Hawaii');
					INSERT INTO article_images(article_id,position,image_url,image_tooltip,top_image_caption) VALUES(2, 10, '/uploads/__WP_2014_10__personal-10.jpg', '', 'The Indiana officials who were against honoring four term mayor because of his name, Harry Baals');

					INSERT INTO menus(domain,domain_key,menus_json) VALUES('test.com', 'test-com', '{\"News\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Entertainment\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Life\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Feed\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Dating\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Celebs\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"More\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}}}');
					INSERT INTO menus(domain,domain_key,menus_json) VALUES('dunzo.net', 'dunzo-net', '{\"News\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Entertainment\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Life\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Feed\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Dating\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"Celebs\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}},\"More\": {\"url\": \"/home\",\"target\": \"\",\"allowed_users\": {\"0\": 1, \"255\": 1, \"1\": 1}}}');

					INSERT INTO site_scripts(domain_key,js,ads_json) VALUES('test-com', '', '');
					INSERT INTO site_scripts(domain_key,js,ads_json) VALUES('dunzo-net', '', '');

					UPDATE site_scripts SET js=?, ads_json=? WHERE domain_key='dunzo-net';
					INSERT INTO menu_pages(domain_key,url,title,content) VALUES('dunzo-net', '/home', 'Testing', ?);
				");
				$sample1->execute($sample1_vars);
				echo 'Added players.<br />';
			}
		}
		catch (PDOException $e)
		{
			echo $e->getMessage();
			die();
		}
	}

	private function getPassHash($pass, $salt) {
		return hash('sha512', $salt . '-' . $pass . '-' . $salt);
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */
