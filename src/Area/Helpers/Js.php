<?php
namespace Area\Helpers;
/**
 * Class Js
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

class Js {
	
	public $path = '';
	
	public function __construct($path){ 
		
		$this->path = $path;
		
	}
	
	public function __toString(){ 
		return 'lool';
		/*if(!file_exists(__BASEDIR__.'/junk/'.$this->path.'/cache/js/'.$this->path.'.js')){
			return '// pack js error';
		}
		return file_get_contents(__BASEDIR__.'/junk/'.$this->path.'/cache/js/'.$this->path.'.js');*/
		/*file_get_contents(
			.
			'http://marijnhaverbeke.nl/uglifyjs?code_url='.urlencode('http://dev.alienfx.pro/js/vendor/jquery-1.11.0.min.js').
			
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/TweenMax.min.js').
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/TimelineMax.min.js').
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/plugins/ScrollToPlugin.min.js').
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/utils/SplitText.min.js').
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/utils/Draggable.min.js').
			'&code_url='.urlencode('http://dev.alienfx.pro/js/vendor/jquery.history.js').			
			
			'&code_url='.urlencode('http://dev.alienfx.pro/js/app.js')
		);*/

	}

	static public function hook($rewrites){
		header('Content-type: text/javascript');
		$js = new self($rewrites->getPath());
		die($js);
	}
	
}