<?php
namespace Area\Core;
/**
 * Class Localization
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

function __($msgid) {
	return Localization::_gettext($msgid);
}

function _gettext($msgid) {
	return Localization::_gettext($msgid);
}

function _ngettext($singular, $plural, $number) {
	return Localization::_ngettext($singular, $plural, $number);
}

class Localization {

	public static $isActive = false;
	//public static $localization = false;
	public static $translations;

	protected $encoding = 'UTF-8';
	protected $supported_locales;
	protected $locales_path;
	protected $locale_version;
	protected $locale;

	private $_cachePath;

	/**
	 * __construct
	 * @return void
	 */
	public function __construct($config){
		/* app configs file
		$config['localization'] = array(

			'isActive' => false,

			'supported_locales' => array(
				'default' => 'en_GB',

				'ch' => 'fr_FR', //Switzerland
				'ch-de' => 'de_DE', //Switzerland

				'ru' => 'ru_RU', //Russia
				'fi' => 'en_GB', //Finland
				'de' => 'de_DE', //Germany
				'no' => 'en_GB', //Norway
				'fr' => 'fr_FR', //France
				'ca' => 'fr_FR', //Canada
				'ca-en' => 'en_GB', //Canada

				'cz' => 'en_GB', //CZ
				'sk' => 'en_GB', //SK
				'us' => 'en_GB', //USA
				'it' => 'en_GB', //Italy
				'sw' => 'en_GB', //Sweden
				'at' => 'de_DE', //Austria
				'po' => 'en_GB', //Poland
				'jp' => 'en_GB', //Japan
				'sp' => 'en_GB', //Spain

				'uk' => 'en_GB', //England
			)

		);
		*/
		if(@$config['isActive']){
			self::$isActive = true;
			$this->supported_locales = $config['supported_locales'];
			if(isset($config['locales_path'])){
				$this->setLocalesPath( $config['locales_path'] );
			}
		}
	}

	/**
	 * Static
	 */
	public static function __($msgid) {
		return self::_gettext($msgid);
	}

	public static function _gettext($msgid) {
		if(empty(Localization::$translations[$msgid])) return $msgid;
		else return Localization::$translations[$msgid];
	}

	public static function _ngettext($singular, $plural, $number) {
		if($number > 1){
			if(empty(Localization::$translations[$plural][$plural])) return $plural;
			else return Localization::$translations[$plural][$plural];
		}else{
			if(empty(Localization::$translations[$plural][$singular])) return $singular;
			else return Localization::$translations[$plural][$singular];
		}
	}


	/**
	 * Configure la localization d'après le rewrites
	 * @return void
	 */
	public function setConfig( $locale_version ){
		$this->locale_version = $locale_version;
		$this->locale = $this->supported_locales[$locale_version];

		// gettext setup
		setlocale(LC_MESSAGES, $this->getLocale());
		// Set the text domain as 'messages'
		$domain = 'locales';
		bindtextdomain($domain, $this->getLocalesPath());
		// bind_textdomain_codeset is supported only in PHP 4.2.0+
		if (function_exists('bind_textdomain_codeset'))
			bind_textdomain_codeset($domain, $this->encoding);
		textdomain($domain);

		$this->parser(__BASEDIR__.'/junk/'.App::$app.'/locales');
		self::$translations = $this->parse($this->getLocalesPath().'/'.$this->getLocale().'/LC_MESSAGES/'.$domain.'.po');

		header("Content-type: text/html; charset=".$this->encoding);
	}

	public function setLocalesPath( $path ){
		$this->locales_path = $path;
	}

	public function getLocalesPath(){
		return !empty($this->locales_path) ? $this->locales_path : App::$appPath.'/locales';
	}

	/**
	 * getSupported
	 * @return array
	 */
	public function getSupported(){
		return $this->supported_locales;
	}

	/**
	 * locale
	 * @return string
	 */
	public function getLocale(){
		return $this->locale;
	}

	/**
	 * locale version
	 * @return string
	 */
	public function getLocaleVersion(){
		return $this->locale_version;
	}


	/**
	 * POParser // https://github.com/tanakahisateru/php-gettext-alternative
	 */
	public function parser($cachePath=false)
    {
        if($cachePath !== FALSE) {
            $this->setCachePath($cachePath);
        }
        else {
            if (function_exists('sys_get_temp_dir')) {
                $this->setCachePath(sys_get_temp_dir());
            } elseif (substr(PHP_OS, 0, 3) == 'WIN') {
                if (file_exists('c:\\WINNT\\Temp\\')) {
                    $this->setCachePath('c:\\WINNT\\Temp\\');
                } else {
                    $this->setCachePath('c:\\WINDOWS\\Temp\\');
                }
            } else {
                $this->setCachePath('/tmp/');
            }
        }
    }

    public function setCachePath($path)
    {
        $this->_cachePath = rtrim($path, '/') .'/';
    }

    public function getCachePath()
    {
        return $this->_cachePath;
    }

    public function parse($pofile, $reparse=FALSE)
    {
        if(!file_exists($pofile)) {
            return FALSE;
        }
        if(!$reparse) {
            $result = $this->_tryLoadParsedCache($pofile);
            if($result !== FALSE) {
                return $result;
            }
        }
        $result = $this->parseFromString(file_get_contents($pofile));
        if($result !== FALSE) {
            $caceh = $this->_cacheFilePathFor($pofile);
            file_put_contents($caceh, serialize($result));
        }
        return $result;
    }

    private function parseFromString($str)
    {
        $result = array();
        $msgid = NULL;
        $is_plural = FALSE;
        $plural_id = NULL;
        $msgstr = NULL;
        $lines = explode("\n", $str);
        foreach($lines as $n=>$line) {
            if(preg_match('/^\s*#/', $line, $match)) {
                continue;
            }
            elseif(preg_match('/^\s*msgid\s*"(.*)"/', $line, $match)) {
                if(is_null($msgid) || !is_null($msgstr)) {
                    $result[$msgid] = $msgstr;
                }
                $msgid = stripcslashes($match[1]);
                $is_plural = FALSE;
                $msgstr = NULL;
            }
            elseif(preg_match('/^\s*msgid_plural\s*"(.*)"/', $line, $match)) {
                if(is_null($msgid) || !is_null($msgstr)) {
                    $result[$msgid] = $msgstr;
                }
                $msgid_singular = $msgid;
				$msgid = stripcslashes($match[1]);
                $is_plural = TRUE;
                $msgstr = NULL;
            }
            elseif(preg_match('/^\s*msgstr\s*"(.*)"/', $line, $match)) {
                if(is_null($msgid) || !is_null($msgstr) || $is_plural) {
                    trigger_error('Illegal format at ' . $n, E_USER_WARNING);
                    return FALSE;
                }
                if(isset($result[$msgid])) {
                    trigger_error('Illegal format at ' . $n, E_USER_WARNING);
                    return FALSE;
                }
                $plural_id = NULL;
                $msgstr = stripcslashes($match[1]);
            }
            elseif(preg_match('/^\s*msgstr\[([0-9]+)\]\s*"(.*)"/', $line, $match)) {
                if(is_null($msgid) || !(is_null($msgstr) || is_array($msgstr)) || !$is_plural) {
                    trigger_error('Illegal format at ' . $n, E_USER_WARNING);
                    return FALSE;
                }
                if(isset($result[$msgid])) {
                    trigger_error('Illegal format at ' . $n, E_USER_WARNING);
                    return FALSE;
                }
                if(is_null($msgstr)) {
                    $msgstr = array();
                }
                $plural_id = $match[2];
                if(isset($msgstr[$plural_id])) {
                    return FALSE;
                }
				if(!isset($i)) $i = 0;
				if(++$i == 1 && isset($msgid_singular)){
					$msgstr[$msgid_singular] = stripcslashes($match[2]);
				}elseif($i == 2){
					$msgstr[$msgid] = stripcslashes($match[2]);
				}elseif($i == 3){
					$msgstr[$msgid_singular] = stripcslashes($match[2]);
				}elseif($i == 4){
					$msgstr[$msgid] = stripcslashes($match[2]);
					unset($i);
					unset($msgid_singular);
				}else{
					$msgstr[$plural_id] = stripcslashes($match[2]);
				}
            }
            elseif(preg_match('/^\s*"(.*)"/', $line, $match)) {
                if(is_null($msgid)) {
                    trigger_error('Illegal format at ' . $n, E_USER_WARNING);
                    return FALSE;
                }
                if(is_null($msgstr)) {
                    $msgid .= stripcslashes($match[1]);
                }
                elseif(!$is_plural) {
                    $msgstr .= stripcslashes($match[1]);
                }
                else {
                    $msgstr[$plural_id] .= stripcslashes($match[1]);
                }
            }
        }
        if(!is_null($msgid)) {
            $result[$msgid] = $msgstr;
        }

        return $result;
    }

    private function _cacheFilePathFor($pofile)
    {
        $uid = md5(realpath($pofile)); // TODO more unique name
        return $this->getCachePath() . 'pocache-' . $uid . '.ser';
    }

    private function _tryLoadParsedCache($pofile)
    {
        $stat = lstat($pofile);
        $lastupd = max($stat['mtime'], $stat['ctime']);

        $cache = $this->_cacheFilePathFor($pofile);
        if(!file_exists($cache)) {
            return FALSE;
        }

        $stat = lstat($cache);
        if($stat['mtime'] < $lastupd || $stat['ctime'] < $lastupd) {
            return FALSE;
        }

        return unserialize(file_get_contents($cache));
    }

}

?>
