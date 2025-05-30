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
		$this->render('rankings_view');
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



	public function getLastMatch() {
		$last_match = $this->Users_model->getFromTable('history', 'id', array(), " ORDER BY id DESC LIMIT 1");
		if(sizeof($last_match) < 1) {
			$last_match = array();
		}
		else {
			$last_match = $last_match[0];
		}
		echo json_encode($last_match);
	}

	public function getRankings() {
		// $rankings = $this->Users_model->getFromTable('players', 'id');
		$rankings = $this->Users_model->getFromTable('players', 'id', array(), " WHERE active=1");
		$rank_epoch = $this->Users_model->getFromTable('rank_epoch', 'id', array(1));
		echo json_encode(array('rankings' => $rankings, 'rank_epoch' => $rank_epoch));
	}

	public function saveRankings() {
		if($this->input->post('rankings') != null) {
			$new_rankings = $this->input->post('rankings');
		}
		else {
			$new_rankings = array();
		}
		if($this->input->post('rank_epoch') != null) {
			$new_rank_epoch = $this->input->post('rank_epoch');
		}
		else {
			$new_rank_epoch = array();
		}
		$afk_grace_period = 3600*24*7*2; // 2 weeks
		foreach($new_rankings as $idx => $new_ranking) {
			if(time() - strtotime($new_ranking['last_played']) > $afk_grace_period) {
				$new_rankings[$idx]['afk'] = 1;
			}
			else {
				$new_rankings[$idx]['afk'] = 0;
			}
		}

		$column_names = array('id','nickname','fname','lname','rating','realtime_rating','highest_rank','highest_rating','last_played');
		$column_names2 = array('id','last_sync');
		$this->Users_model->importRows('players', 'id', $new_rankings, $column_names, $column_names);
		$this->Users_model->importRows('rank_epoch', 'id', array($new_rank_epoch), $column_names2, $column_names2);
	}

	public function getBattles() {
		echo json_encode(
			array_merge(
				$this->Users_model->getFromTable('battles', 'player_id', array('ids' => array($this->input->post('player_id')))),
				$this->Users_model->getFromTable('battles', 'opponent_id', array('ids' => array($this->input->post('player_id'))))
			)
		);
	}

	public function getCombatLog() {
		// echo json_encode($this->Users_model->getFromTable('combat_log', 'id'));
		// echo json_encode($this->Users_model->getFromTable('combat_log', 'id', array(), " ORDER BY id DESC LIMIT 18"));

		$combat_log = $this->Users_model->getFromTable('combat_log', 'id', array(), " ORDER BY id DESC LIMIT 18");
		usort($combat_log, array($this, "sortByID"));
		echo json_encode($combat_log);
	}

	public function saveBattles() {
		if($this->input->post('battle_history') != null) {
			$battle_history = $this->input->post('battle_history');
		}
		else {
			$battle_history = array();
		}
		if($this->input->post('battle_results') != null) {
			$battle_results = $this->input->post('battle_results');
		}
		else {
			$battle_results = array();
		}
		if(sizeof($battle_results) < 1) {
			return;
		}

		$player_ids = array();
		$existing_battles = array();
		foreach($battle_results as $result) {
			$player_ids[] = $result['player_id'];
		}

		$now = date("Y-m-d H:i:s", time());
		$new_rankings = array();
		foreach($player_ids as $player_id) {
			$new_rankings[] = array(
				'id' => $player_id,
				'last_played' => $now
			);
		}
		$column_names = array('id','last_played');
		$this->Users_model->importRows('players', 'id', $new_rankings, $column_names, $column_names);

		$existing_battles = $this->Users_model->getFromTable('battles', 'player_id', $player_ids);
		$current_existing_battle;
		foreach($battle_results as &$result) {
			foreach($existing_battles as $existing_battle) {
				if($existing_battle['player_id'] == $result['player_id'] && $existing_battle['opponent_id'] == $result['opponent_id']) {
					$result['id'] = $existing_battle['id'];
					$result['wins'] += $existing_battle['wins'];
				}
			}
		}

		$column_names = array('id','player_id','opponent_id','wins');
		$this->Users_model->importRows('battles', 'id', $battle_results, $column_names, $column_names);
		$column_names2 = array('id','winner_id','loser_id','winner_old_rating','loser_old_rating','rating_change');
		$this->Users_model->importRows('history', 'id', $battle_history, $column_names2, $column_names2);
	}

	public function saveCombatLog() {
		$COMBAT_LOG_LIMIT = 18;
		$combat_log = $this->input->post('combat_log');
		if(is_array($combat_log) && sizeof($combat_log) > 0) {
			$column_names = array_keys($combat_log[0]);
			$this->Users_model->importRows('combat_log', 'id', $combat_log, $column_names, $column_names);

			$updated_combat_log = $this->Users_model->getFromTable('combat_log', 'id');
			/*
			if(sizeof($updated_combat_log) > $COMBAT_LOG_LIMIT) {
				$combat_log_entries_to_delete = array();
				for($i = 0; $i < sizeof($updated_combat_log) - $COMBAT_LOG_LIMIT; $i++) {
					$combat_log_entries_to_delete[] = $updated_combat_log[$i]['id'];
				}
				$this->Users_model->deleteFromTable('combat_log', 'id', array('ids' => $combat_log_entries_to_delete));
			}
			*/
		}
	}

	public function undoLastMatch() {
		// DROP TABLE IF EXISTS history;CREATE TABLE history (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,winner_id INT(11),loser_id INT(11),winner_old_rating INT(11),loser_old_rating INT(11),rating_change INT(11),time TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
		// INSERT INTO history(winner_id,loser_id,rating_change) VALUES(1,2,25);
		// INSERT INTO history(winner_id,loser_id,rating_change) VALUES(1,2,15);
		// SELECT * FROM history;

		/*
		$last_change = $this->Users_model->getFromTable('history', 'id', array(), " ORDER BY id DESC LIMIT 1");
		if(sizeof($last_change) < 1) {
			return;
		}
		$last_change = $last_change[0];
		$players = $this->Users_model->getFromTable('players', 'id', array('ids' => array($last_change['winner_id'], $last_change['loser_id'])));
		$winner = null;
		$loser = null;
		foreach($players as $player) {
			if($player['id'] == $last_change['winner_id']) {
				$winner = $player;
			}
			else if($player['id'] == $last_change['loser_id']) {
				$loser = $player;
			}
		}
		if($winner == null || $loser == null) {
			return;
		}
		$reverted_rankings = array(
			array(
				'id' => $last_change['winner_id'],
				'realtime_rating' => $winner['realtime_rating'] - $last_change['rating_change']
			),
			array(
				'id' => $last_change['loser_id'],
				'realtime_rating' => $loser['realtime_rating'] + $last_change['rating_change']
			)
		);
		// print_r('<pre>');
		// print_r($last_change);
		// print_r($players);
		// print_r($reverted_rankings);
		// print_r('</pre>');
		// update players set realtime_rating = 1780 where nickname='Hawk';update players set realtime_rating = 1709 where nickname='Pendulum';
		// update players set realtime_rating = 1762 where nickname='Hawk';update players set realtime_rating = 1727 where nickname='Pendulum';
		// http://54.149.229.47/rankings/undoLastMatch
		*/

		// $column_names = array('id', 'realtime_rating');
		// $this->Users_model->importRows('players', 'id', $reverted_rankings, $column_names, $column_names);
		$this->Users_model->deleteFromTable('combat_log', 'id', array(), " ORDER BY id DESC LIMIT 1");
		$this->Users_model->deleteFromTable('history', 'id', array(), " ORDER BY id DESC LIMIT 1");
	}

	private function sortByID($e1, $e2) {
		return $e1['id'] - $e2['id'];
	}
}

/* End of file login.php */
/* Location: ./application/controllers/login.php */
