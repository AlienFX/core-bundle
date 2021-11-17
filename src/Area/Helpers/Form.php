<?php
namespace Area\Helpers;

class Form {
	public $datas = array();

	public $checks = array();
	public $errors = array();
	public $success = false;

	private $onSubmit = array();
	private $onSuccess;
	private $onErrors;

	public function __construct(){

	}
	//
	public function addData($datas=array()){
		$this->datas = array_merge($this->datas, $datas);
	}
	public function __get($nom){
		return isset($this->datas[$nom]) ? $this->datas[$nom] : NULL;
	}
	//
	public function onSubmit($key, $func=NULL){
		if(gettype($key) == 'object'){
			$func = $key;
			$key = 'default';
		}
		$this->onSubmit[$key] = $func;
	}
	public function submit($key='default'){
		call_user_func($this->onSubmit[$key], $this);
	}
	//
	public function hasSuccess(){
		return $this->success;
	}
	public function onSuccess($func){
		$this->onSuccess = $func;
	}
	public function success(){
		$this->success = true;
		@call_user_func($this->onSuccess, $this);
	}
	//
	public function onErrors($func){
		$this->onErrors = $func;
	}
	public function errors(){
		@call_user_func($this->onErrors, $this);
	}
	public function getErrors(){
		return $this->errors;
	}
	public function getErrorsMessages(){
		$errors = array();
		foreach($this->getErrors() as $error){
			if(is_string($error)){
				$errors[] = $error;
			}
		}
		if(!count($errors)) $errors[] = 'Une erreur est survenue, merci de vérifier les données saisies!';
		return $errors;
	}
	public function hasErrors(){
		return (bool)count($this->errors);
	}
	public function hasError($nom){
		return (bool)isset($this->errors[$nom]);
	}
	//
	public function check($nom, $required = false, $message = 1){
		$this->checks[$nom] = array(
			'val' => isset($_POST[$nom]) ? (!is_array($_POST[$nom]) ? htmlspecialchars($_POST[$nom]) : $_POST[$nom]) : NULL
		);
		switch($required){
			case 'required':
				if(!isset($this->checks[$nom]['val']) || empty($this->checks[$nom]['val'])){
					$this->errors[$nom] = $message;
				}
			break;
			case 'required_checkbox':
				if(!isset($this->checks[$nom]['val']) || !count($this->checks[$nom]['val'])){
					$this->errors[$nom] = $message;
				}
			break;
			case 'required_email':
				if(!isset($this->checks[$nom]['val']) || empty($this->checks[$nom]['val'])){
					$this->errors[$nom] = $message;
				}
				if(!filter_var($this->checks[$nom]['val'], FILTER_VALIDATE_EMAIL)){
					$this->errors[$nom] = $message;
				}
			break;
			case 'required_phone':
				if(!isset($this->checks[$nom]['val']) || empty($this->checks[$nom]['val'])){
					$this->errors[$nom] = $message;
				}
				$this->checks[$nom]['val'] = preg_replace('/([^0-9+]+)/', '', $this->checks[$nom]['val']);
				if(empty($this->checks[$nom]['val']) || strlen($this->checks[$nom]['val']) < 10){
					$this->errors[$nom] = $message;
				}
			break;
		}
	}
	public function checkFunc($nom, $func){
		$this->checks[$nom] = array(
			'val' => isset($_POST[$nom]) ? (!is_array($_POST[$nom]) ? htmlspecialchars($_POST[$nom]) : $_POST[$nom]) : NULL
		);
		call_user_func($func, $nom, $this);
	}
	public function checks(){
		if(count($this->errors)){
			$this->errors();
			return false;
		}else{
			return true;
		}
	}
	//
	public function get($nom){
		return isset($this->checks[$nom]['val'])?$this->checks[$nom]['val']:NULL;
	}
	public function set($nom, $valeur){
		if(!is_array($this->checks[$nom])) $this->checks[$nom] = array();
		$this->checks[$nom]['val'] = $valeur;
	}
	//
	public function listen($submit){
		if(isset($submit))
			$this->submit();

		$this->template->addData(array('form' => $this));
	}
	public function listenAll(){
		foreach($this->onSubmit as $key => $onSubmit){
			if(isset($_POST[$key]))
				$this->submit($key);
		}

		$this->template->addData(array('form' => $this));
	}

}
