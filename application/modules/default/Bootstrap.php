<?php

class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initAppAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
				'namespace' => 'Default_',
				'basePath' => dirname(__FILE__)
			)
		);
		if(Zend_Auth::getInstance()->hasIdentity()){
	        //set date time zone
	        date_default_timezone_set(Zend_Auth::getInstance()->getIdentity()->user_timezone);      			
		}

		return $autoloader;
	}
}