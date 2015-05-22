<?php 
/**
 *
 * @category    Controller
 * @package     default 
 * @author:     Alvaro Mercado - alvaro@amediacreative.com
 * @copyright:  Amedia Creative Inc - http://www.amediacreative.com 
 */
class BackUpController extends Zend_Controller_Action
{
	
	function init(){
        if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/login');
      
	}


	function indexAction(){
		$DateForConsult = $this->_request->getParam('date', 0);
		echo $DateForConsult;
	}


}

 ?>