<?php
namespace Area\Core;
/**
 * Class App
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

use Area\Core\Sql;
use Area\Core\Rewrites;
use Area\Core\Template;
use Area\Core\Localization;

class App {

	public static $debug = false;
	public static $debug_time = array();

	public static $app;
	public static $appPath;

	public static $sql;
	public static $rewrites;
	public static $template;
	public static $userauth;
	public static $localization;

	public static $configs;
	public static $loaded_bundles;

	public function __construct(){

		if($_SERVER['REMOTE_ADDR'] == '') self::$debug = true;
		if(self::$debug == true) self::$debug_time['startApp'] = microtime(true);

		self::$app = isset($_GET['app']) ? $_GET['app'] : 'default';
		self::$appPath = __BASEDIR__.'/app/'.self::$app;

		if(!is_dir(self::$appPath)) throw new \Exception('App "'.htmlspecialchars(self::$app).'" doesn\'t exists.', 404);

		# On charge les fichiers de configuration
		$this->loadConfiguration();
		$this->trigger('afterConfiguration');

		# On charge les fichiers de configuration des bundles
		$this->loadConfigurationBundles();
		$this->trigger('afterConfigurationBundles');

		# On initialise MySql
		$sql = new Sql();
		$sql->connexion(self::$configs['app']['mysql']['host'], self::$configs['app']['mysql']['user'], self::$configs['app']['mysql']['password'], self::$configs['app']['mysql']['db']);
		$sql->query("SET NAMES 'utf8mb4'");
		self::$sql = $sql;
		$this->trigger('afterSql');

		# On initialise la Localization si nécessaire dans ce projet
		$localization = new Localization(@self::$configs['app']['localization']);
		self::$localization = $localization;
		$this->trigger('afterLocalization');

		/*
			if(defined('APP_LOCALIZATION') && APP_LOCALIZATION){
				$supported = $localization->getSupported();
				array_shift($supported);
			}
			//'supported_locales' => $supported,
			//'locale' => $localization->getLocale(),
			//'localeVersion' => $localization->getLocaleVersion()
		*/

		# On décode l'Url
		$rewrites = new Rewrites();
		$rewrites->loadRules(array_merge(self::$configs['core']['rewrites'], self::$configs['app']['rewrites']));
		$rewrites->unmake($_GET['p']);
		self::$rewrites = $rewrites;
		$this->trigger('afterRewrites');

		# On initialise Template
		$template = new Template();
		//$template->rewrites = $rewrites;
		$template->addData(array('rewrites' => $rewrites));
		self::$template = $template;
		$this->trigger('afterTemplate');

		# On execute l'autoload des bundles
		//$this->execBundles();
		$this->trigger('afterBundles');

		# Si il y a un hook à exécuter, alors on le fait
		if($rewrites->getHook()){
			list($hook_class, $hook_method) = explode('::', $rewrites->getHook());
			$hook_class::$hook_method( $rewrites );
		}
		$this->ready();
	}

	public function end(){
		//try {
			if(self::$rewrites->getTpl()){
				$render = self::$template->render('pages/'.self::$rewrites->getTpl());
			}else{
				$render = self::$template->render('pages/'.self::$rewrites->getPage().'/'.self::$rewrites->getAction());
			}
			//if(self::$debug == true) self::$debug_time['endApp'] = microtime(true);
			//if(self::$debug == true) echo "[BENCH] ".(self::$debug_time['endApp'] - self::$debug_time['startApp'])." \n";

			return $render;
		//}catch(\Exception $e){
			//die($e->getMessage());
		//}
	}

	public function loadConfiguration(){
		/* Configuration */
		self::$configs = array(
			'core' => array(),
			'app' => array()
		);

		$config_files = glob(self::$appPath.'/configs/*.php');
		foreach($config_files as $config_file){
			include_once $config_file;
			self::$configs['app'] = array_merge(self::$configs['app'], $config);
		}
	}

	public function loadConfigurationBundles(){
		/* Bundles */
		self::$loaded_bundles = array();

		$loaded_bundles = glob(realpath(dirname(__FILE__).'/../../../..').'/*'); //glob(__BASEDIR__.'/vendor/alienfx-area/*');
		foreach($loaded_bundles as $loaded_bundle){
			self::$loaded_bundles[basename($loaded_bundle)] = array();

			/* Configuration ? */
			$config_files = glob($loaded_bundle.'/default/configs/*.php');
			foreach($config_files as $config_file){
				include_once $config_file;
				self::$loaded_bundles[basename($loaded_bundle)] = array_merge_recursive(self::$loaded_bundles[basename($loaded_bundle)], $config);
			}

			self::$configs['core'] = array_merge_recursive(self::$loaded_bundles[basename($loaded_bundle)], self::$configs['core']);
		}
	}

	public function execBundles(){
		/* Bundles */
		foreach(self::$loaded_bundles as $loaded_bundle => $configs){
			if( file_exists( realpath(dirname(__FILE__).'/../../../..').'/'.$loaded_bundle.'/default/autload.php') )
				include_once realpath(dirname(__FILE__).'/../../../..').'/'.$loaded_bundle.'/default/autload.php';
		}
	}

	public function trigger($name){
		$method = 'bind'.ucfirst($name);
		if(method_exists($this, $method)){
			$this->$method();
		}
	}
}
