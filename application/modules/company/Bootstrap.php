<?php

class Company_Bootstrap extends Zend_Application_Module_Bootstrap
{
	protected function _initAppAutoload()
	{
		$autoloader = new Zend_Application_Module_Autoloader(
			array(
				'namespace' => 'Company_',
				'basePath' => dirname(__FILE__)
			)
		);
		return $autoloader;
	}
}