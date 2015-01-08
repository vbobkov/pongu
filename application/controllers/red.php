<?php
/* Vic Bobkov
* red.php
*/

// require_once 'phar://predis/predis_0.8.4.phar'; // predis
class Red extends MY_Controller {
	private static $REDIS_SERVER = '127.0.0.1';
	private static $REDIS_SCHEME = 'tcp';
	private static $REDIS_PORT = 6379;
	private $redis = null;



	public function __construct() {
		parent::__construct();
		$this->redis = $this->initRedis();
	}



	public function saveMatchUpdates() {
		if($this->redis == null) { return; }

		$rankings = json_encode($this->input->post('rankings'));
		$combat_log = $this->input->post('combat_log');
		if(!is_array($combat_log) || sizeof($combat_log) < 1) {
			$combat_log = '[]';
		}
		else {
			$combat_log = json_encode($combat_log);
		}


		$this->redis->flushAll();
		$this->redis->zadd('pongu_ts_players', microtime(true), $rankings);
		$this->redis->zadd('pongu_ts_combat_log', microtime(true), $combat_log);
		// print_r($this->input->post('combat_log'));
		// $this->redis->zadd('pongu_ts_players', microtime(true), 'wtf');
		// $this->redis->set(microtime(true), 'wtf');
		// $this->addGoog();
	}

	public function getMatchUpdates() {
		if($this->redis == null) { return; }

		$rankings = $this->redis->zrangebyscore(
			'pongu_ts_players',
			$this->input->post('redis_last_synced'),
			'+inf',
			array('withscores' => true)
		);
		$combat_log = $this->redis->zrangebyscore(
			'pongu_ts_combat_log',
			$this->input->post('redis_last_synced'),
			'+inf',
			array('withscores' => true)
		);

		echo json_encode(array(microtime(true), $rankings, $combat_log));
	}



	private function initRedis() {
		$redis = null;
		try {
			$redis  = new Predis\Client(array(
				'scheme' => Red::$REDIS_SCHEME,
				'host'   => Red::$REDIS_SERVER,
				'port'   => Red::$REDIS_PORT
			));
			$redis->ping();
		}
		catch(Exception $e) {
			// TO DO: handle the following (maybe a user notification, etc) more gracefully:
			// Predis\CommunicationException
			// Predis\ConnectionException
			$redis = null;
		}

		return $redis;
	}
}

/* End of file red.php */
/* Location => ./application/controllers/red.php */
