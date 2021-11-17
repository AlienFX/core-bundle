<?php
namespace Area\Core;
/**
 * Class Rewrites
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

class Rewrites {

	protected $app_rewrites = array();

	protected $originalUrl;
	protected $url;
	protected $rules = array();

	protected $titre;
	protected $metas = array();

	protected $infos = false;

	public function __construct(){
		if(isset($_SESSION['rewrites_infos'])){
			$this->infos = $_SESSION['rewrites_infos'];
			unset($_SESSION['rewrites_infos']);
		}
	}

	/**
	 * charge les rules de rewrites
	 *
	 * @param array $rules rules récupérer dans la config de l'app
	 * @return void
	 */
	public function loadRules( $rules ){
		$this->rules = array_reverse($rules);
	}

	/**
	 * charge la classe pour une autre app
	 *
	 * @param string $app le nom de l'app
	 * @return Core_Rewrites
	 */
	public function loadApp( $app ){
		if(!isset($this->app_rewrites[$app])){
			include_once(__BASEDIR__.'/app/'.$app.'/configs/rewrites.php');
			$rewrites = new self();
			$rewrites->loadRules( $config['rewrites'] );
			$this->app_rewrites[$app] = $rewrites;
		}
		return $this->app_rewrites[$app];
	}

	/**
	 * détermine la localization
	 *
	 * @param array $rules rules récupérer dans la classe core.localization.php
	 * @return $url filtrée sans la localization
	 */
	public function loadLocalization( $url ){
		$supported = App::$localization->getSupported();
		foreach($supported as $locale_version => $locale){
			if(preg_match('#^/'.$locale_version.'/#', $url)){
				App::$localization->setConfig($locale_version);
				$url = preg_replace('#^/'.$locale_version.'/#', '/', $url);
				return $url;
			}
		}

		/*foreach($supported as $locale_version => $locale){
			if(preg_match('#'.$locale_version.'#', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE']))){
				App::$localization->setConfig($locale_version);
				$this->redirige( '/'.App::$localization->getLocaleVersion().$this->originalUrl );
			}
		}*/

		//App::$localization->setConfig('uk');
		//$this->redirige( '/'.App::$localization->getLocaleVersion().$this->originalUrl );

		App::$localization->setConfig('default');
		return $url;
	}

	/**
	 * permet d'unmake l'url par rapport aux rules de rewrites
	 *
	 * @param string $url url de la page fournie depuis le htaccess
	 * @return void
	 */
	public function unmake( $url ){
		$this->originalUrl = $url;
		$this->url['url'] = $url;

		if(Localization::$isActive){
			$this->url['url'] = $this->loadLocalization( $this->url['url'] );
		}

		foreach($this->rules as $regex => $rule){

			if(preg_match('#^'.$regex.'$#', $this->url['url'], $vars)){
				$this->url = $rule;
				$this->url['vars'] = array();
				for($n = 1; $n < count($vars); $n++){
					$this->url['vars'][$rule['varsConfig'][$n-1]] = $vars[$n];
				}
				break;
			}

		}

	}

	/**
	 * permet de make l'url par rapport aux rules de rewrites
	 *
	 * @param array $page nom de la page
	 * @param array $vars vars à fournir pour l'url de la page
	 * @param array $QSA ajouter les qsa à l'url de la page
	 * @param boolean $recode recode l'url
	 * @return string
	 */
	public function make( $page, $vars = array(), $QSA = array(), $recode = true ){

		foreach($this->rules as $regex => $rule){

			if($rule['page'] == $page){

				$varsConfig = isset($rule['varsConfig']) ? array_flip($rule['varsConfig']) : array();

				if(!count(array_diff_key($varsConfig, $vars)) && !count(array_diff_key($vars, $varsConfig))){

					$url = $regex;
					$vars_values = array_values($vars);
					preg_match_all('#(\([^\)]+\))#', $regex, $replaces, PREG_PATTERN_ORDER);
					if(isset($replaces[0])){
						for($n = 0; $n < count($replaces[0]); $n++){
							if(isset($vars_values[$n])){
								//$url = str_replace($replaces[0][$n], ($recode ? $this->recode($vars_values[$n]) : $vars_values[$n]), $url);
								$url = substr_replace($url, ($recode ? $this->recode($vars_values[$n]) : $vars_values[$n]), strpos($url, $replaces[0][$n]), strlen($replaces[0][$n]));
							}
						}
					}

					if( count($QSA) ){
						$url.= '?'.http_build_query($QSA);
					}

					return /*PATH_URL.*/$url;

				}

			}

		}

		return $this->make('404');

	}

	/**
	 * permet de make l'url sans retourner de nom de domaine et path par rapport aux rules de rewrites
	 *
	 * @param array $page nom de la page
	 * @param array $vars vars à fournir pour l'url de la page
	 * @param array $QSA ajouter les qsa à l'url de la page
	 * @param boolean $recode recode l'url
	 * @return string
	 */
	public function makeWith( $domain, $page, $vars = array(), $QSA = array(), $recode = true ){
		return $domain.$this->make( $page, $vars, $QSA, $recode );
	}

	/**
	 * permet de récupérer le hook
	 *
	 * @return string
	 */
	public function getHook(){
		return isset($this->url['hook']) ? $this->url['hook'] : NULL;
	}

	/**
	 * permet de récupérer la page
	 *
	 * @return string
	 */
	public function getPage(){
		return $this->url['page'];
	}

	/**
	 * permet de récupérer l'action
	 *
	 * @return string
	 */
	public function getAction(){
		return isset($this->url['vars']['action']) ? $this->url['vars']['action'] : 'default';
	}

	/**
	 * permet de récupérer l'url du site
	 *
	 * @return string
	 */
	public function getNdd(){
		return SITE_URL;
	}

	/**
	 * permet de récupérer l'url de la page
	 *
	 * @return string
	 */
	public function getCurrentUrl(){
		return $this->originalUrl;
	}


	/**
	 * permet de récupérer le titre de la page
	 *
	 * @return string
	 */
	public function getTitle(){
		return !empty($this->titre) ? $this->titre : false;
	}

	/**
	 * permet de setter le titre de la page
	 *
	 * @return void
	 */
	public function setTitle( $titre ){
		$this->titre = $titre;
	}

	/**
	 * permet de récupérer les metas de la page
	 *
	 * @return array
	 */
	public function getMetas(){
		return $this->metas;
	}

	/**
	 * permet de setter un meta
	 *
	 * @return void
	 */
	public function addMeta( $cle, $valeur ){
		$this->metas[$cle] = $valeur;
	}

	/**
	 * permet de recuperer les messages d'infos de redirection
	 *
	 * @return array
	 */
	public function getInfos( $cle=false ){
		return $cle?(isset($this->infos[$cle])?$this->infos[$cle]:NULL):$this->infos;
	}

	/**
	 * permet de rediriger une page
	 *
	 * @param string $url url de la page
	 * @param array $infos messages d'infos de redirection
	 * @return void
	 */
	public function redirige($url, $infos=false){
		if($infos){
			 $_SESSION['rewrites_infos'] = $infos;
		}
		die(header('Location: '.$url));
	}

	/**
	 * recode l'url
	 *
 	 * @param string $url url de la page
	 * @param array $replace s'il faut remplacer des caractères
	 * @param array $delimiter le delimiter
	 * @return string url finale
	 */
	function recode($url, $replace=array(), $delimiter='-', $replaceAccents=true){
		if( !empty($replace) ) {
			$url = str_replace((array)$replace, ' ', $url);
		}

		$clean = $url;

		if($replaceAccents){
			$clean = htmlentities($clean, ENT_NOQUOTES, 'utf-8');
			$clean = preg_replace('#&([A-za-z])(?:acute|cedil|caron|circ|grave|orn|ring|slash|th|tilde|uml);#', '\1', $clean);
			$clean = preg_replace('#&([A-za-z]{2})(?:lig);#', '\1', $clean); // pour les ligatures e.g. '&oelig;'
			$clean = preg_replace('#&[^;]+;#', '', $clean); // supprime les autres caractères
		}

		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $clean);
		$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
		$clean = preg_replace("/[_|+ -]+/", $delimiter, $clean); /* \/ */
		$clean = strtolower(trim($clean, '-'));

		return $clean;
	}

	/**
	 * permet de récupérer les variables décodées depuis l'url de la page
	 *
	 * @param string $name nom du paramètre à récupérer
	 * @param array $arguments arguments
	 * @return mixed
	 */
	public function __call( $name, $arguments ){
		if(substr($name, 0, 3) == 'set'){
			$this->url['vars'][strtolower(str_replace('set', '', $name))] = $arguments[0];
		}elseif(isset($this->url['vars'][strtolower(str_replace('get', '', $name))])){
			return $this->url['vars'][strtolower(str_replace('get', '', $name))];
		}
	}

}

?>
