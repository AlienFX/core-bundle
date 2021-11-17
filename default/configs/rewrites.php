<?php
$config['rewrites'] = array(

	'/css/(.+).min.css' => array(
		"page" => 'css',
		"varsConfig" => array('path'),
		"hook" => '\Area\Helpers\Css::hook'
	),
	
	'/images/(.+).min.png' => array(
		"page" => 'png',
		"varsConfig" => array('path'),
		"hook" => '\Area\Helpers\TinyPng::hook'
	),
	
);