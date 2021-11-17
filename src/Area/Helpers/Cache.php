<?php
namespace Area\Helpers;
/**
 * Class Curl
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

class Cache {

	protected $key;
	protected $cache_lifetime;
	protected $cache_version;
	protected $cache_dir;
	protected $cache_file;

	public function __construct($key, $cache_lifetime=3600, $cache_version = 1, $dir='/default/cache/files'){
		$this->key = md5($key);
		$this->cache_lifetime = $cache_lifetime;
		$this->cache_version = $cache_version;
		$this->cache_dir = $this->generate_cache_dir($dir);
		$this->cache_file = $this->cache_dir.'/'.$this->key.'-'.$this->cache_version.'.cache';
	}

	private function generate_cache_dir($dir){
		$dir = __BASEDIR__.'/junk'.$dir;
		@mkdir($dir);
		@mkdir($dir.'/'.substr($this->key, 0, 1));
		@mkdir($dir.'/'.substr($this->key, 0, 1).'/'.substr($this->key, 1, 1));
		$this->cache_dir = $dir.'/'.substr($this->key, 0, 1).'/'.substr($this->key, 1, 1);
	}

	public function getCache(){

		if(file_exists($this->cache_file) && time()-filemtime($this->cache_file) < $this->cache_lifetime)
			return json_decode(file_get_contents($this->cache_file), true)['cache'];

		return false;
	}

	public function getAge(){

		if(file_exists($this->cache_file))
			return filemtime($this->cache_file);

		return -1;
	}

	public function saveCache($contenu){

		$datas = array('cache' => $contenu);
		file_put_contents($this->cache_file, json_encode($datas));

		return $this->getAge();
	}
}

?>
