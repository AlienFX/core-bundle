<?php
namespace Area\Core;
/**
 * Class Sql
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

use \PDO;

class Sql {

	private $log = false;
	private $debug = false;
	private $connection = false;

	/**
	 * connexion
	 * @param string $host host
	 * @param string $user user
	 * @param string $password password
	 * @param string $base base
	 * @return void
	 */
	public function connexion($host, $user, $password, $db){
		//if($_SERVER['REMOTE_ADDR'] == '') $this->debug = true;
		//if($_SERVER['REMOTE_ADDR'] == '') $this->log = true;
		if($_SERVER['HTTP_HOST'] == 'crons.valuebetennis.fr') $this->log = true;

		try {
	    $this->connection = new PDO("mysql:host=$host;dbname=".$db, $user, $password);
	    $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }catch(PDOException $e){
			//throw new Exception('Impossible de se connecter : ' . mysqli_connect_error());
    	echo "Impossible de se connecter : " . $e->getMessage();
    }
	}

	public function close(){
		$this->connection = null;
	}

	/* Query */

	/**
	 * query
	 * @param string $query s
	 * @return void
	 */
	public function query($query){
		if($this->debug){
			echo "[SQL] $query \n";
		}
		try {
			$ressource_query = $this->connection->prepare($query);
			$ressource_query->execute();
			return $ressource_query;
		}catch(PDOException $e) {
			if($this->debug){
				echo "[SQL Error] ".$ressource_query->errorInfo()." \n";
			}
			return false;
		}
	}
	/**
	 * insert
	 * @param string $query s
	 * @return void
	 */
	public function insert($query){
		return $this->query($query);
	}
	/**
	 * update
	 * @param string $query s
	 * @return void
	 */
	public function update($query){
		return $this->query($query);
	}
	/**
	 * delete
	 * @param string $query s
	 * @return void
	 */
	public function delete($query){
		return $this->query($query);
	}

	/* Query Raccourci */

	/**
	 * select
	 * @param string $query s
	 * @param bool $cache s
	 * @return void
	 */
	public function select($query, $cache=false){
		if($this->log || $this->debug) $time_start = microtime(true);

		$ressource_query = $this->query($query);
		$resultats = array();
		while($row = $this->fetch_assoc($ressource_query)){
			if(isset($row['id'])){
				$resultats[$row['id']] = $row;
			}else{
				$resultats[] = $row;
			}
		}

		if($this->log || $this->debug){
			$time_stop = microtime(true);
			$time = $time_stop - $time_start;
			if($this->log && $time >= 0.03) \App::log('sql')->info(str_replace(array('	', '  ', '  ', '  '), ' ', $query), array('t' => round($time, 3), 'url' => $_SERVER['REQUEST_URI']));
			if($this->debug) echo "      => $time \n";
		}
		return $resultats;
	}
	/**
	 * select_one
	 * @param string $query s
	 * @param bool $cache s
	 * @return void
	 */
	public function select_one($query, $cache=false){
		if($this->log || $this->debug) $time_start = microtime(true);

		$ressource_query = $this->query($query);
		if($ressource_fetch = $this->fetch_assoc($ressource_query)){
			$resultats = array_shift($ressource_fetch);
		}

		if($this->log || $this->debug){
			$time_stop = microtime(true);
			$time = $time_stop - $time_start;
			if($this->log && $time >= 0.03) \App::log('sql')->info(str_replace(array('	', '  ', '  ', '  '), ' ', $query), array('t' => round($time, 3), 'url' => $_SERVER['REQUEST_URI']));
			if($this->debug) echo "      => $time \n";
		}
		return isset($resultats) ? $resultats : false;
	}
	/**
	 * select_one_row
	 * @param string $query s
	 * @param bool $cache s
	 * @return void
	 */
	public function select_one_row($query, $cache=false){
		if($this->log || $this->debug) $time_start = microtime(true);

		$ressource_query = $this->query($query);
		if($ressource_fetch = $this->fetch_assoc($ressource_query)){
			$resultats = $ressource_fetch;
		}

		if($this->log || $this->debug){
			$time_stop = microtime(true);
			$time = $time_stop - $time_start;
			if($this->log && $time >= 0.03) \App::log('sql')->info(str_replace(array('	', '  ', '  ', '  '), ' ', $query), array('t' => round($time, 3), 'url' => $_SERVER['REQUEST_URI']));
			if($this->debug) echo "      => $time \n";
		}
		return isset($resultats) ? $resultats : false;
	}

	/* Methods */

	/**
	 * fetch_assoc
	 * @param string $ressource_query s
	 * @return void
	 */
	public function fetch_assoc($ressource_query){
		return $ressource_query->fetch(PDO::FETCH_ASSOC);
	}
	/**
	 * num_rows
	 * @param string $ressource_query s
	 * @return void
	 */
	public function num_rows($ressource_query){
		return $ressource_query->fetchColumn();
	}
	/**
	 * insert_id
	 * @return void
	 */
	public function insert_id(){
		return $this->connection->lastInsertId();
	}

	public function lock_tables($tables=array()){
		$this->connection->beginTransaction();
		$this->connection->exec('LOCK TABLES '.implode(', ', $tables).';');
	}

	public function unlock_tables($commit=true){
		if($commit) $this->connection->commit();
		else $this->connection->rollBack();
		$this->connection->exec('UNLOCK TABLES;');
	}

}
