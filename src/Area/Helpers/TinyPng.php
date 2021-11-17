<?php
namespace Area\Helpers;
/**
 * Class TinyPng
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

class TinyPng {
	
	protected $key;
	protected $input;
	protected $output;
	protected $erreurs;

	public function __construct($input, $output=NULL){ 

		$this->key = isset(\App::$configs['app']['helpers']['tinypng']['key']) && 
							\App::$configs['app']['helpers']['tinypng']['key'] != '<your api key>' ? 
							\App::$configs['app']['helpers']['tinypng']['key'] : false;
		$this->input = $input;
		$this->output = $output;

	}

	public function run(){

		if(!$this->key){
			$this->erreurs = 'You must define configs/app/helpers/tinypng/key with valid api key';
			return $this->input;
		}

		if(!file_exists($this->output)){

			$request = curl_init();
			curl_setopt_array($request, array(
			  CURLOPT_URL => "https://api.tinypng.com/shrink",
			  CURLOPT_USERPWD => "api:" . $this->key,
			  CURLOPT_POSTFIELDS => file_get_contents($this->input),
			  CURLOPT_BINARYTRANSFER => true,
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_HEADER => true,
			  /* Uncomment below if you have trouble validating our SSL certificate.
			     Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
			  /*CURLOPT_CAINFO => __BASEDIR__ . "/junk/cacert.pem",*/
			  CURLOPT_SSL_VERIFYPEER => false
			));

			$response = curl_exec($request);
			if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
			  /* Compression was successful, retrieve output from Location header. */
			  $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
			  foreach (explode("\r\n", $headers) as $header) {
			    if (substr($header, 0, 10) === "Location: ") {
			      $request = curl_init();
			      curl_setopt_array($request, array(
			        CURLOPT_URL => substr($header, 10),
			        CURLOPT_RETURNTRANSFER => true,
			        /* Uncomment below if you have trouble validating our SSL certificate. */
			        /*CURLOPT_CAINFO => __BASEDIR__ . "/junk/cacert.pem",*/
			        CURLOPT_SSL_VERIFYPEER => false
			      ));
			      file_put_contents($this->output, curl_exec($request));
			    }
			  }
			  return $this->output;
			} else {
			  $this->erreurs = curl_error($request);
			  return $this->input;
			}

		}else{
			return $this->output;
		}

	}

	public function getErreurs(){
		return $this->erreurs;
	}

	static public function hook( $rewrites ){
		header('Content-type: image/png');
		$png = new self(__BASEDIR__.'/web/images/'.$rewrites->getPath().'.png', __BASEDIR__.'/junk/default/cache/images/tinypng/'.md5($rewrites->getPath()).'.png');
		die(readfile($png->run()));
	}
	
}