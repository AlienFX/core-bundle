<?php
namespace Area\Core;
/**
 * Class Template
 * @author ALIENFX EURL <eurl@alienfx.net>
 */

use League\Plates;

class Template extends \League\Plates\Engine {
	
	public function __construct(){
				
		parent::__construct(App::$appPath.'/templates');
		$this->loadConfiguration();
		
	}
	
	public function loadConfiguration(){
		//$engine->addFolder('emails', '/path/to/emails');
		//$engine->loadExtension(new \League\Plates\Extension\Asset('/path/to/public'));
		$this->loadExtension(new ExtensionLocalization());
		
	}
	
}

use League\Plates\Engine;
use League\Plates\Extension\ExtensionInterface;

class ExtensionLocalization implements ExtensionInterface
{
    public function register(Engine $engine)
    {
        $engine->registerFunction('__', array($this, '__'));
		$engine->registerFunction('_gettext', array($this, '_gettext'));
		$engine->registerFunction('_ngettext', array($this, '_ngettext'));
		
    }

    public function __($msgid)
    {
        return Localization::__($msgid);
    }
	
	public function _gettext($msgid)
    {
        return Localization::_gettext($msgid);
    }
	
	public function _ngettext($singular, $plural, $number)
    {
        return Localization::_ngettext($singular, $plural, $number);
    }

}