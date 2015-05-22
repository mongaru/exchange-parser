<?php

class LangController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
    	if ( ! Zend_Auth::getInstance()->hasIdentity())
			$this->_redirect('login');
    }

    public function indexAction()
    {
        // action body
    }

    public function switchAction()
    {
    	$this->_helper->viewRenderer->setNoRender(true);
		$this->_helper->layout()->disableLayout();
		
		$languages = array('en' => 'en_US', 'zh' => 'zh_CN');
		$lang = $this->_request->getParam('id', 'en');
		
		$translate = Zend_Registry::get('translate');
		$locale = Zend_Registry::get('locale');
    	
		Zend_Auth::getInstance()->getIdentity()->user_locale = $languages[$lang];
		$view->userData = Zend_Auth::getInstance()->getIdentity();
			
		$locale->setLocale($languages[$lang]);
		$translate->setLocale($locale->getLanguage());
			
		if (trim($view->userData->user_timezone) == '')
			$view->userData->user_timezone = 'America/Los_Angeles';
		if (trim($view->userData->user_locale) == '')
			$view->userData->user_locale = 'en_US';

		$this->_redirect('/');
    }

}

