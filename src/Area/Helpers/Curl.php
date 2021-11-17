<?php
namespace Area\Helpers;
/**
 * Class Curl
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

class Curl {

	protected $handle;
	protected $response;
  protected $referer = '';
	protected $headers = array();
	protected $datas = array();
	protected $cookiename = false;

	public function __construct(){
		$this->handle = curl_init();
	}

	public function setopt($cle, $valeur){
		curl_setopt($this->handle, $cle, $valeur);
	}

	public function execute($url, $autoreset=true){
		if(substr($url, 0, 4) != 'http')
			return $this->zp($url);

    if($this->referer == '')
       $this->referer = 'https://'.parse_url($url)['host'].'/';
    $this->setopt(CURLOPT_REFERER, $this->referer);
    $this->referer = $url;

    if(file_exists(__BASEDIR__.'/public/upload/cacert.pem'))
      $this->setopt( CURLOPT_CAINFO, __BASEDIR__.'/public/upload/cacert.pem' );

		if(count($this->headers))
			$this->setopt( CURLOPT_HTTPHEADER, $this->headers );

    $this->setopt( CURLOPT_SSL_VERIFYPEER, 0 );
		$this->setopt( CURLOPT_RETURNTRANSFER, 1 );
    $this->setopt( CURLOPT_FOLLOWLOCATION, 1 );
		$this->setopt( CURLOPT_URL, $url );
		$this->response = curl_exec($this->handle);

	  if($autoreset) $this->reset();
		$httpCode = $this->getInfo( CURLINFO_HTTP_CODE );
		if($this->response && $httpCode != 404) {
			return $this->response;
		}else{
			return false;
		}
	}

	public function getInfo($valeur){
		return curl_getinfo($this->handle, $valeur);
	}

	public function getResponse(){
		return $this->response;
	}

	public function reset(){
		/*$this->close();
		$this->datas = array();
		$this->handle = curl_init();
		$this->setUserAgent();
		$this->setCookieSession();*/
		$this->setopt( CURLOPT_POST, NULL );
		$this->setopt( CURLOPT_POSTFIELDS, NULL );
		$this->datas = array();
	}

	public function close(){
		curl_close($this->handle);
	}

  public function post($datas=array()){
		$this->setopt( CURLOPT_POST, 1 );
		$this->setopt( CURLOPT_POSTFIELDS, $datas );
		$this->datas = $datas;
  }

	public function headers($datas=array()){
		$this->headers = array_merge($this->headers, $datas);
	}

	public function setProxy($proxy){
		$this->setopt( CURLOPT_PROXY, $proxy );
		return $proxy;
	}

	public function setUserAgent(){

		$useragents = array(

			/* Latest UPDATED 18 Nov 2018 - https://techblog.willshouse.com/2012/01/03/most-common-user-agents/ */
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/64.0.3282.140 Safari/537.36 Edge/17.17134',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36',
			'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.1 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.1 Safari/605.1.15',
			'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (X11; Linux x86_64; rv:60.0) Gecko/20100101 Firefox/60.0',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36',
			'Mozilla/5.0 (iPad; CPU OS 12_0_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.13; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 OPR/56.0.3051.52',
			'Mozilla/5.0 (X11; Linux x86_64; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:60.0) Gecko/20100101 Firefox/60.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.67 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 OPR/56.0.3051.99',
			'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 YaBrowser/18.9.1.954 Yowser/2.5 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1.2 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:62.0) Gecko/20100101 Firefox/62.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_0) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.36 Edge/16.16299',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:64.0) Gecko/20100101 Firefox/64.0',
			'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/68.0.3440.106 YaBrowser/18.9.1.954 Yowser/2.5 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/51.0.2704.106 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'Mozilla/5.0 (Windows NT 10.0; WOW64; Trident/7.0; Touch; rv:11.0) like Gecko',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/69.0.3497.81 Chrome/69.0.3497.81 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Mozilla/5.0 (iPad; CPU OS 12_1 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0 Mobile/15E148 Safari/604.1',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_12_6) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/12.0.1 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/67.0.3396.99 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_5) AppleWebKit/603.3.8 (KHTML, like Gecko) Version/10.1.2 Safari/603.3.8',
			'Mozilla/5.0 (Windows NT 10.0; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:61.0) Gecko/20100101 Firefox/61.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_4) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/11.1 Safari/605.1.15',
			'Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36 OPR/56.0.3051.52',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/70.0.3538.77 Chrome/70.0.3538.77 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.12; rv:63.0) Gecko/20100101 Firefox/63.0',
			'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:60.0) Gecko/20100101 Firefox/60.0',
			'Mozilla/5.0 (Windows NT 5.1; rv:52.0) Gecko/20100101 Firefox/52.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/69.0.3497.100 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.102 Safari/537.36',

			//https://udger.com/resources/ua-list/browser-detail?browser=Chrome
			//https://udger.com/resources/ua-list/browser-detail?browser=Firefox
			//https://udger.com/resources/ua-list/browser-detail?browser=Safari

			/* Firefox 40.1 - http://www.useragentstring.com/pages/Firefox/ */
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1',
			'Mozilla/5.0 (Windows NT 6.3; rv:36.0) Gecko/20100101 Firefox/36.0',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10; rv:33.0) Gecko/20100101 Firefox/33.0',
			'Mozilla/5.0 (X11; Linux i586; rv:31.0) Gecko/20100101 Firefox/31.0',
			'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:31.0) Gecko/20130401 Firefox/31.0',

			/* Chrome 41.0.2228.0 - http://www.useragentstring.com/pages/Chrome/ */
			'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_10_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.1 Safari/537.36',
			'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',
			'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2227.0 Safari/537.36',

			/* Safari */
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_3) AppleWebKit/537.75.14 (KHTML, like Gecko) Version/7.0.3 Safari/7046A194A',
			'Mozilla/5.0 (iPad; CPU OS 6_0 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10A5355d Safari/8536.25',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/537.13+ (KHTML, like Gecko) Version/5.1.7 Safari/534.57.2',
			'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/534.55.3 (KHTML, like Gecko) Version/5.1.3 Safari/534.53.10',

			/* Android Webkit Browser */
			'Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
			'Mozilla/5.0 (Linux; U; Android 4.0.3; de-ch; HTC Sensation Build/IML74K) AppleWebKit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30',
			'Mozilla/5.0 (Linux; U; Android 2.3; en-us) AppleWebKit/999+ (KHTML, like Gecko) Safari/999.9',
			'Mozilla/5.0 (Linux; U; Android 2.3.5; zh-cn; HTC_IncredibleS_S710e Build/GRJ90) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1',

			/* iPhone & iPad */
			'Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) CriOS/30.0.1599.12 Mobile/11A465 Safari/8536.25',
			'Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53',
			'Mozilla/5.0 (iPad; CPU OS 7_0 like Mac OS X) AppleWebKit/537.51.1 (KHTML, like Gecko) Mobile/11A465',
			'Mozilla/5.0 (iPhone; CPU iPhone OS 6_1_3 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) Version/6.0 Mobile/10B329 Safari/8536.25',

			/* BlackBerry */
			'Mozilla/5.0 (BlackBerry; U; BlackBerry 9900; en) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.1.0.346 Mobile Safari/534.11+',
			'Mozilla/5.0 (BlackBerry; U; BlackBerry 9860; en-US) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.0.0.254 Mobile Safari/534.11+',
			'Mozilla/5.0 (BlackBerry; U; BlackBerry 9850; en-US) AppleWebKit/534.11+ (KHTML, like Gecko) Version/7.0.0.254 Mobile Safari/534.11+'

		);

		$this->setopt( CURLOPT_USERAGENT, $useragent = $useragents[rand(0, count($useragents)-1)] );
		return $useragent;
	}

  public function setCookieSession($name=false){
    if(!$name && !$this->cookiename) $name = uniqid();
		elseif(!$name && $this->cookiename) $name = $this->cookiename;
		$this->cookiename = $name;
    $this->setopt( CURLOPT_COOKIESESSION, true );
    $this->setopt( CURLOPT_COOKIEJAR, $this->cookiename );
    $this->setopt( CURLOPT_COOKIEFILE, $this->cookiename );
  }


	////////////////////////
	/// PROCESS ZENNOPOSTER
	////////////////////////
	public function zpProcess($url, $post=false, $cache_time=1800){

		$id = false;
		$post = $post ? http_build_query($post) : false;
		$path = explode('/', $url, 4);
		$path_encoded = md5($path[3].($post ? $post : '')).'.cache';
		$path_dir = __BASEDIR__.'/junk/zp/'.substr($path_encoded, 0, 1).'/'.substr($path_encoded, 1, 1).'/';

		//////
		$url_exists = \App::$sql->select_one_row('SELECT * FROM zp_urls WHERE url_href = "'.$url.'"'.($post ? ' AND url_href_posts = "'.$post.'"' : '').'');
		if((!$url_exists || $url_exists['url_status'] == 2) && !file_exists($path_dir.$path_encoded) || (time()-filemtime($path_dir.$path_encoded) > $cache_time)){

			if(!$url_exists){

				\App::$sql->insert('INSERT IGNORE INTO zp_urls
						SET
							url_date_insert = "'.date('c').'",
							url_date_asked = "'.date('c').'",
							url_href = "'.$url.'",
							'.($post ? 'url_href_posts = "'.$post.'",' : '').'
							url_priority = 10');

				$id = \App::$sql->insert_id();

			}else{

				\App::$sql->update('UPDATE zp_urls
						SET
							url_date_asked = "'.date('c').'",
							url_date_processed = "0000-00-00 00:00:00",
							url_priority = 10,
							url_status = 0
						WHERE
							id = "'.$url_exists['id'].'"');

				$id = $url_exists['id'];

			}
			@unlink($path_dir.$path_encoded);

		}else{

			$id = $url_exists['id'];

		}
		//////

		return $id ? array('id' => $id, 'file' => $path_dir.$path_encoded) : false;

	}

	public function zpPreloads($urls, $cache_time=1800, $async=false, $async_timeout=180){

		$urlsIds = array();
		foreach($urls as $url){
			if($process = $this->zpProcess($url['href'], (isset($url['post'])?$url['post']:false), $cache_time))
				$urlsIds[] = $process['id'];
		}

		for($i=0;$i<($async_timeout/5);$i++){

			$nb = \App::$sql->select('SELECT id FROM zp_urls WHERE url_status < 2 AND id IN ('.implode(',', $urlsIds).')');
			if(!count($nb))
				return true;

			if($async)
				return false;

			sleep(5);
		}
		return false;

	}

	public function zp($url, $cache_time=1800, $async=false, $async_timeout=120){

		if($process = $this->zpProcess($url, (count($this->datas)?$this->datas:false), $cache_time))
			$this->reset();

		for($i=0;$i<($async_timeout/5);$i++){

			if(file_exists($process['file']))
				return file_get_contents($process['file']);

			if($async)
				return false;

			sleep(5);
		}
		return false;

	}

	////////////////////////
	/// PROCESS NODEJS
	////////////////////////
	public function node($url){

		$posts = (count($this->datas)?http_build_query($this->datas):'');
		$this->reset();

		$exec = exec('cd '.__BASEDIR__.'/bin/cloudflare-bypass/; node cf-proxy.js "'.$url.'" "'.$posts.'" 2>&1', $out, $err);
		if($err || !is_array($out)) return false;

		return implode(' ', $out);

	}

}

?>
