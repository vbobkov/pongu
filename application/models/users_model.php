<?php
/* Vic Bobkov
* users_model.php
*/

class Users_model extends MY_Model {
	private $SQL_getUser;
	private static $SQL_updateUser = "UPDATE users SET ";
	private static $SQL_updateUser2 = " WHERE id = ?";



	public function __construct($load_database = true) {
		parent::__construct();
		$this->SQL_getUser = "SELECT id,password,password_salt,type,username FROM " . $this->db_name . ".users";
	}

	public function getUser($params = array()) {
		if(!(isset($params['id']) || isset($params['username']))) {
			return array();
		}

		$result = array();
		if(isset($params['id'])) {
			$result = $this->db->query(
				Users_model::$SQL_getUser .
				" WHERE id = ?",
				array($params['id'])
			)->result_array();
		}
		else {
			$result = $this->db->query(
				Users_model::$SQL_getUser .
				" WHERE username = ?",
				array($params['username'])
			)->result_array();
		}
		return $result;
	}

	public function updateUser($id, $columns) {
		$update_sql = $this->getUpdateSQL($id, $columns);
		if($update_sql == null) {
			return;
		}

		return $this->db->query(
			Users_model::$SQL_updateUser .
			$update_sql['set'] .
			Users_model::$SQL_updateUser2,
			$update_sql['values']);
	}
}

/* End of file users_model.php */
/* Location: ./application/models/users_model.php */