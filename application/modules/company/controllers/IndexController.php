<?php

class Company_IndexController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
		$this->view->logged_in = Zend_Auth::getInstance()->hasIdentity();
    }

    public function indexAction()
    {
        // action body
        if ( ! Zend_Auth::getInstance()->hasIdentity())
        	$this->_redirect('/people/auth/index');
    }


}