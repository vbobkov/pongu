<?php
/* Vik Bobkov
* MY_Model.php
*/

class MY_Model extends CI_Model {
	private $db_config;
	private $db_config_path;

	public function __construct($load_database = true) {
		parent::__construct();
		$this->db_config_path = FCPATH . 'application/core/settings/db_config.json';
		$this->db_config = json_decode(file_get_contents($this->db_config_path), true);
		if($this->router->fetch_class() != 'admin' || $this->router->fetch_method() != 'editDBConfig') {
			$this->load->database($this->db_config['dev']);
		}
	}



	public function getDBConfig() {
		return $this->db_config;
	}

	public function updateDBConfig($new_config) {
		$this->db_config = $new_config;
		file_put_contents(FCPATH . 'application/core/settings/db_config.json', json_encode($this->db_config));
	}



	public function addToTable($table_name, $rows = array()) {
		if(sizeof($rows) < 1) {
			return false;
		}
		$sql1 = "INSERT INTO pongu." . $table_name . "(";
		$sql2 = ") VALUES";

		$insert_sql_row;
		$insert_sql = null;
		foreach($rows as $row) {
			$insert_sql_row = $this->getInsertSQL($row);
			if($insert_sql_row != null && $insert_sql == null) {
				$insert_sql = array(
					'insert_names' => $insert_sql_row['insert_names'],
					'insert_param_markers' => '(' . $insert_sql_row['insert_param_markers'] . ')',
					'values' => $insert_sql_row['values']
				);
			}
			else {
				$insert_sql['insert_param_markers'] .= ',(' . $insert_sql_row['insert_param_markers'] . ')';
				$insert_sql['values'] = array_merge($insert_sql['values'], $insert_sql_row['values']);
			}
		}

		return $this->db->query(
			$sql1 .
			$insert_sql['insert_names'] .
			$sql2 .
			$insert_sql['insert_param_markers'],
			$insert_sql['values']);
	}

	public function deleteFromTable($table_name, $id_name, $params = array(), $filters = "") {
		$result = array();

		if(isset($params['ids']) && is_array($params['ids']) && sizeof($params['ids']) > 0) {
			$sql1 = "DELETE FROM pongu." . $table_name . " WHERE " . $id_name . " IN(";
			$sql2 = ") ";
			if(is_array($params['ids'])) {
				foreach($params['ids'] as $idx => $id) {
					$params['ids'][$idx] = "'" . htmlentities($id, ENT_NOQUOTES) . "'";
				}
				$params['ids'] = implode(',', $params['ids']);
			}
			$result = $this->db->query($sql1 . $params['ids'] . $sql2 . " " . $filters);
		}
		else {
			$result = $this->db->query("DELETE FROM pongu." . $table_name . " " . $filters);
		}
		if(is_object($result)) {
			return $result->result_array();
		}
		else {
			return $result;
		}
	}

	public function getFromTable($table_name, $id_name, $params = array(), $filters = "") {
		$result = array();
		if(!isset($params['columns'])) {
			$params['columns'] = "*";
		}
		else {
			$params['columns'] = implode(',', $params['columns']);
		}

		if(isset($params['ids']) && is_array($params['ids'])) {
			if(sizeof($params['ids']) > 0) {
				$sql1 = "SELECT " . $params['columns'] . " FROM pongu." . $table_name . " WHERE " . $id_name . " IN(";
				$sql2 = ") ";
				if(is_array($params['ids'])) {
					foreach($params['ids'] as $idx => $id) {
						$params['ids'][$idx] = "'" . htmlentities($id, ENT_NOQUOTES) . "'";
					}
					$params['ids'] = implode(',', $params['ids']);
				}
				$result = $this->db->query($sql1 . $params['ids'] . $sql2 . " " . $filters)->result_array();
			}
			else {
				$result = array();
			}
		}
		else {
			$result = $this->db->query("SELECT " . $params['columns'] . " FROM pongu." . $table_name . " " . $filters)->result_array();
		}
		return $result;
	}

	public function updateTable($table_name, $id_name, $columns, $filter) {
		$update_sql = $this->getUpdateSQL(null, $columns);
		if($update_sql == null) {
			return 'getUpdateSQL() returned null';
		}

		if($this->db->query(
			"UPDATE pongu." . $table_name . " SET " . $update_sql['set'] . " " . $filter,
			$update_sql['values']
		)) {
			return true;
		}
		else {
			return '[updateTable] database update call failed';
		}
	}

	public function updateTableByID($table_name, $id_name, $columns) {
		$update_sql = $this->getUpdateSQL($id_name, $columns);
		if($update_sql == null) {
			return 'getUpdateSQL() returned null';
		}

		if($this->db->query(
			"UPDATE pongu." . $table_name . " SET " . $update_sql['set'] . " WHERE " . $id_name . " = ?",
			$update_sql['values']
		)) {
			return true;
		}
		else {
			return '[updateTableByID] database update call failed';
		}
	}

	public function importRows($table_name, $id_name, $rows, $insert_columns, $update_columns, $blobs = array()) {
		if(!is_array($rows) || sizeof($rows) < 1) {
			return;
		}

		$SQL_addRow = "INSERT INTO pongu." . $table_name . "(";
		$SQL_addRow2 = ") VALUES(";
		$SQL_updateRow = "UPDATE pongu." . $table_name . " SET ";
		$SQL_updateRow2 = " WHERE " . $id_name . " = ?";

		if(!is_array($insert_columns)) {
			$insert_columns = array();
		}
		else {
			$ic = array();
			foreach($insert_columns as $column_name) {
				$ic[$column_name] = $column_name;
			}
			$insert_columns = $ic;
			$ic = null;
		}
		if(!is_array($update_columns)) {
			$update_columns = array();
		}
		else {
			$uc = array();
			foreach($update_columns as $column_name) {
				$uc[$column_name] = $column_name;
			}
			unset($uc[$id_name]);
			$update_columns = $uc;
			$uc = null;
		}

		$plain_pdo = new PDO('mysql:host=' . $this->db->hostname . ';dbname=' . $this->db->database, $this->db->username, $this->db->password);
		// $plain_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		// $plain_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

		$insert_sql = $this->getInsertSQL($insert_columns);
		$insert_stmt = $plain_pdo->prepare(
			$SQL_addRow .
			$insert_sql['insert_names'] .
			$SQL_addRow2 .
			$insert_sql['insert_param_markers'] . ')'
		);

		$update_sql = $this->getUpdateSQL(null, $update_columns);
		$update_stmt = $plain_pdo->prepare(
			$SQL_updateRow .
			$update_sql['set'] .
			$SQL_updateRow2
		);

		$plain_pdo->beginTransaction();
		$current_id;
		$row_map_policy;
		$import_row;
		foreach($rows as &$row) {
			$row_id = $row[$id_name];
			$import_row = array();
			$import_row_names = array();

			if($row_id == -1) {
				foreach($row as $name => $value) {
					if(!isset($insert_columns[$name])) {
						unset($row[$name]);
					}
				}
				foreach($insert_columns as $idx => $name) {
					if($name != $id_name) {
						$import_row_names[] = $name;
						if(in_array($name, $blobs)) {
							$import_row[] = $row[$name];
						}
						else {
							$import_row[] = htmlentities($row[$name], ENT_NOQUOTES);
						}
						
					}
				}

				if(sizeof($blobs) > 0) {
					$i = 1;
					$handles = array();
					foreach($import_row as $idx => $value) {
						if(in_array($import_row_names[$idx], $blobs)) {
							$insert_stmt->bindValue($i, $value, PDO::PARAM_LOB);
							if(get_resource_type($value) == 'stream') {
								$handles[] = $value;
							}
						}
						else {
							$insert_stmt->bindValue($i, $value);
						}
						$i++;
					}
					$insert_stmt->execute();
					foreach($handles as $handle) {
						fclose($handle);
					}
				}
				else {
					$insert_stmt->execute(array_values($import_row));
				}
				$row[$id_name] = $plain_pdo->lastInsertId();
			}
			else {
				foreach($row as $name => $value) {
					if(!isset($update_columns[$name])) {
						unset($row[$name]);
					}
				}
				foreach($update_columns as $name) {
					$import_row[] = htmlentities($row[$name], ENT_NOQUOTES);
				}

				$update_cols = array_merge($import_row, array($id_name => $row_id));
				if(sizeof($blobs) > 0) {
					$i = 1;
					$handles = array();
					foreach($update_cols as $idx => $value) {
						if(in_array($import_row_names[$idx], $blobs)) {
							$update_stmt->bindValue($i, $value, PDO::PARAM_LOB);
							if(get_resource_type($value) == 'stream') {
								$handles[] = $value;
							}
						}
						else {
							$update_stmt->bindValue($i, $value);
						}
						$i++;
					}
					$insert_stmt->execute();
					foreach($handles as $handle) {
						fclose($handle);
					}
				}
				else {
					$update_stmt->execute(array_values($update_cols));
				}
				$row[$id_name] = $row_id;
			}
		}
		$plain_pdo->commit();
		return $rows;
	}

	public function uploadFilesToDB($files, $output_json = true) {
		$accepted_mime_types = array(
			'image/bmp',
			'image/x-windows-bmp',
			'image/png',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/svg+xml',
			'image/tiff'
			// 'image/vnd.djvu',
			// 'image/example',
		);
		$images = array();
		$image_columns = array('id', 'name', 'type', 'size', 'data');

		foreach($files as $file) {
			// move_uploaded_file($file['tmp_name'], FCPATH . '/assets/uploads/' . $file['name']);
			// print_r(file_get_contents($file['tmp_name']));
			if(in_array($file['type'], $accepted_mime_types)) {
				$images[] = array(
					'id' => -1,
					'name' => $file['name'],
					'type' => $file['type'],
					'size' => $file['size'],
					'data' => fopen($file['tmp_name'], 'rb')
				);
			}
		}
		// $imported_images = $this->importRows('images', 'id', $images, $image_columns, $image_columns);
		$imported_images = $this->importRows('images', 'id', $images, $image_columns, $image_columns, array('data'));
		foreach($imported_images as &$image) {
			unset($image['data']);
		}

		if($output_json) {
			echo json_encode($imported_images);
		}
	}



	protected function getInsertSQL($columns, $id_name = 'id') {
		if(!is_array($columns) || sizeof($columns) < 1) {
			return null;
		}
		unset($columns[$id_name]);

		$insert_sql = array('insert_names' => "", 'insert_param_markers' => "", 'values' => array());
		foreach($columns as $name => $value) {
			$insert_sql['insert_names'] .= $name . ",";
			$insert_sql['insert_param_markers'] .= "?,";
			// $insert_sql['values'][] = $value;
			// $insert_sql['values'][] = htmlentities($value, ENT_QUOTES);
			$insert_sql['values'][] = htmlentities($value, ENT_NOQUOTES);
		}
		if(substr($insert_sql['insert_names'], -1) == ',') {
			$insert_sql['insert_names'] = substr($insert_sql['insert_names'], 0, -1);
		}
		if(substr($insert_sql['insert_param_markers'], -1) == ',') {
			$insert_sql['insert_param_markers'] = substr($insert_sql['insert_param_markers'], 0, -1);
		}
		return $insert_sql;
	}

	protected function getUpdateSQL($id, $columns) {
		if(!is_array($columns) || sizeof($columns) < 1) {
			return null;
		}

		$update_sql = $this->getUpdateSQLParseValues($columns);
		if($id != null) {
			if(!is_array($id)) {
				$update_sql['values'][] = $id;
			}
			else {
				foreach($id as $key_piece) {
					$update_sql['values'][] = $key_piece;
				}
			}
		}
		return $update_sql;
	}

	private function getUpdateSQLParseValues($columns) {
		$update_sql = array('set' => "", 'values' => array());
		$current_val;
		foreach($columns as $name => $value) {
			$update_sql['set'] .= $name . "=?,";
			// $update_sql['values'][] = htmlentities($value, ENT_QUOTES);
			$current_val = htmlentities($value, ENT_NOQUOTES);
			$update_sql['values'][] = empty($current_val) ? NULL : $current_val;
		}
		if(substr($update_sql['set'], -1) == ',') {
			$update_sql['set'] = substr($update_sql['set'], 0, -1);
		}
		return $update_sql;
	}
}

/* End of file MY_Model.php */
/* Location: ./application/core/MY_Model.php */