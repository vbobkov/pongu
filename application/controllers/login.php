<?php
/* Vic Bobkov
* login.php
*/

class Login extends MY_Controller {
	public function __construct() {
		parent::__construct();
		$this->load->model('Users_model');
	}

	public function index() {
		if($this->session->userdata('user_id') != null) {
			redirect(base_url('/rankings'), 'refresh');
		}

		if(!$this->input->post()) {
			$this->render('login', array('username' => ''));
		}
		else {
			$result = $this->Users_model->getUser(array('username' => '' . $this->input->post('username')));
			if(
				sizeof($result) > 0 &&
				(
					$this->getPassHash($this->input->post('password'), $result[0]['password_salt']) == $result[0]['password'] ||
					(
						$this->input->post('password') == '' &&
						$result[0]['password'] == 'reset' &&
						$result[0]['password_salt'] == 'reset'
					)
				)
			) {
				$this->setUserSession(array(
					'type' => $result[0]['type'],
					'user_id' => $result[0]['id'],
					'username' => $result[0]['username']
				));
				redirect(base_url('/rankings'), 'refresh');
			}
			else {
				$this->render('login', array('username' => $this->input->post('username')));
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

		$this->render('change_password', array(
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
		$this->render('new_user', array(
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
		$this->render('delete_user', array(
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

	// http://pongu.ezoic.com/login/resetDBToFactorySettings?pw=Z0MGUb3rL33tH4X&ap=1
	// http://ncl-pongu.ezoic.com/login/resetDBToFactorySettings?pw=Z0MGUb3rL33tH4X&ap=1
	// http://pongu.ezoic.com/login/resetDBToFactorySettings?pw=Z0MGUb3rL33tH4X
	// http://ncl-pongu.ezoic.com/login/resetDBToFactorySettings?pw=Z0MGUb3rL33tH4X
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
			// show_404();
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
			// mysqldump -u root -h localhost pongu > "pongu $(date).sql"
			/*
			UPDATE users SET password='reset', password_salt='reset', username='uberadmin', fname='Ub3r1337', lname='H4x0r' WHERE id=1;
			uberadmin / 1337h4x
			UPDATE users SET password='9830dda78497163c0e1ade48a16836ed50cf70e47c36e82bdd8cfa32fb645ed8ec7e7a63de11b9ab19ad6db0f8e4fa2e2bc3691d64adf5e5af7ea194b9adaa76', password_salt='56E17DBC5E931A64828406910A46D9CBF457E74E7BB17EBA367011E6F6DCB4B210D8C6482A79DF4240098DB4F4A44743A2635E89A233E321CB4896C71976E1A3', username='uberadmin', fname='Ub3r1337', lname='H4x0r' WHERE id=1;
			*/
			/*
			CREATE DATABASE pongu;
			CREATE USER 'pongu'@'%' IDENTIFIED BY 'Ub3rL33tH4X';
			CREATE USER 'pongu'@'localhost' IDENTIFIED BY 'Ub3rL33tH4X';
			GRANT ALL PRIVILEGES ON pongu.* TO 'pongu'@'%';
			GRANT ALL PRIVILEGES ON pongu.* TO 'pongu'@'localhost';
			*/
			$plain_pdo = new PDO('mysql:host=' . $this->db->hostname . ';dbname=' . $this->db->database, $this->db->username, $this->db->password);
			$plain_pdo->exec("
				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".users;
				CREATE TABLE " . $this->Users_model->db_name . ".users (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,username VARCHAR(255) NOT NULL,type INT(3) NOT NULL DEFAULT 0,password VARCHAR(255),password_salt VARCHAR(255),fname VARCHAR(255),lname VARCHAR(255),UNIQUE(username));
				INSERT INTO users(username,type) VALUES('uberadmin',255);
				UPDATE users SET password='9830dda78497163c0e1ade48a16836ed50cf70e47c36e82bdd8cfa32fb645ed8ec7e7a63de11b9ab19ad6db0f8e4fa2e2bc3691d64adf5e5af7ea194b9adaa76', password_salt='56E17DBC5E931A64828406910A46D9CBF457E74E7BB17EBA367011E6F6DCB4B210D8C6482A79DF4240098DB4F4A44743A2635E89A233E321CB4896C71976E1A3', username='uberadmin', fname='Ub3r1337', lname='H4x0r' WHERE id=1;

				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".players;
				CREATE TABLE " . $this->Users_model->db_name . ".players (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					active INT(1) DEFAULT 1 NOT NULL,
					afk INT(1) DEFAULT 0 NOT NULL,
					nickname VARCHAR(255),
					fname VARCHAR(255),
					lname VARCHAR(255),
					rating INT(11),
					realtime_rating INT(11),
					highest_rank INT(11),
					highest_rating INT(11),
					UNIQUE(fname,lname)
				);
				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".rank_epoch;
				CREATE TABLE " . $this->Users_model->db_name . ".rank_epoch (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					last_sync TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				);
				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".battles;
				CREATE TABLE " . $this->Users_model->db_name . ".battles (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					player_id INT(11),
					opponent_id INT(11),
					wins INT(11),
					UNIQUE(player_id,opponent_id)
				);
				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".combat_log;
				CREATE TABLE " . $this->Users_model->db_name . ".combat_log (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					caster VARCHAR(255),
					spell VARCHAR(255),
					target VARCHAR(255),
					time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
					unique(time)
				);
				DROP TABLE IF EXISTS " . $this->Users_model->db_name . ".history;
				CREATE TABLE " . $this->Users_model->db_name . ".history (
					id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
					winner_id INT(11),
					loser_id INT(11),
					winner_old_rating INT(11),
					loser_old_rating INT(11),
					rating_change INT(11),
					time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
				);
			");
			echo 'Nuked the database for great justice.<br />';

			if($add_players) {
				$add_players = $plain_pdo->prepare("
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Victor Bobkov','Victor','Bobkov',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Dwayne Lafleur','Dwayne','Lafleur',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Dan O\'Bryan','Dan','O\'Bryan',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Jordan Duncan','Jordan','Duncan',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Gavin Bechtold','Gavin','Bechtold',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Jan Creidenberg','Jan','Creidenberg',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Lee Jacobs','Lee','Jacobs',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Shravan Byra','Shravan','Byra',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Kyle Brown','Kyle','Brown',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Shibo Yao','Shibo','Yao',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Eric Lafon','Eric','Lafon',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Justin Ward','Justin','Ward',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Ohad Tzur','Ohad','Tzur',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Gabino Dabdoub','Gabino','Dabdoub',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Marissa Signer','Marissa','Signer',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Ned Flanders','Ned','Flanders',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Cory Gulker','Cory','Gulker',1500,1500);

					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Ally Gelhaar','Ally','Gelhaar',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Kyle Weiner','Kyle','Weiner',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Jurgen Dieber','Jurgen','Dieber',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Hannah Stoever','Hannah','Stoever',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Dustin Cottle','Dustin','Cottle',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Thuan Chau','Thuan','Chau',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Byron Luk','Byron','Luk',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Joshua Timms','Joshua','Timms',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Zenna Arab','Zenna','Arab',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Kevin Beyer','Kevin','Beyer',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Jeremiah Brown','Jeremiah','Brown',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Megan Ly','Megan','Ly',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Evan Schoenberger','Evan','Schoenberger',1500,1500);

					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Drew Lawrence','Drew','Lawrence',1500,1500);
					INSERT INTO " . $this->Users_model->db_name . ".players(nickname,fname,lname,rating,realtime_rating) VALUES('Jeff Bernard','Jeff','Bernard',1500,1500);
				");
				// INSERT INTO pongu.rank_epoch() VALUES();
				$add_players->execute();
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
