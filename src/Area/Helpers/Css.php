<?php
namespace Area\Helpers;
/**
 * Class Css
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

use \leafo\scssphp;

class Css {
	
	public function __construct($path){ 

		$scss = new \scssc();
		return $scss->compile(
			file_get_contents(__BASEDIR__.'/web/css/scss/'.$path.'.scss')
		);

	}

	static public function hook($rewrites){
		header('Content-type: text/css');
		$css = new self($rewrites->getPath());
		die($css);
	}
	
}