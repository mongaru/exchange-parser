<?php

class Amedia_Controller_Action_Helper_Logger extends Zend_Controller_Action_Helper_Abstract {

	private $logger;

	function __construct() {

		if (Zend_Registry::isRegistered('logger'))
		{
			$this->logger = Zend_Registry::get('logger');
		}

	}

	function __call($name, $arguments)
	{

		if ($this->logger)
		{
			try
			{
				$this->logger->$name($arguments[0]);
			}
			catch(Zend_Exception $e)
			{
				self::direct($e->getTrace(), Zend_Log::ERR);
			}
		}

	}

	function direct($message, $priority)
	{
		if ($this->logger)
		{
			$this->logger->log($message, $priority);
		}
	}
}

?>