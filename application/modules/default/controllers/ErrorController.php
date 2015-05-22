<?php
class ErrorController extends Zend_Controller_Action
{
    /**
     * @var Amedia_Acl
     */
    protected $_amediaAcl;
    /**
     * @var Zend_Translate
     */
    protected $_translate;

    public function init()
    {
        $this->_amediaAcl = Zend_Registry::get('amediaAcl');
        $this->_translate = Zend_Registry::get('translate');
    }

    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');

        switch ($errors->type)
        {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = $this->_translate->_('Page not found');
                $this->view->headTitle($this->view->message);
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = $this->_translate->_('Application error');
                $this->view->headTitle($this->view->message);
                break;
        }

        $this->view->exception = $errors->exception;
        $this->view->request   = $errors->request;

        $this->view->pageID    = 'error404';
        $this->view->pageClass = 'error-page';
        $this->view->pageTitle = "Uh oh! Ninja's have stolen this page!";
        $this->view->mainRightRail = '';
    }
    
    public function privilegesAction()
    {
        $this->view->message = $this->_translate->_('Oops! Access was denied');
        $this->view->headTitle($this->view->message);
        $this->view->pageID    = 'error404';
        $this->view->pageClass = 'error-page';
        $this->view->pageTitle = $this->view->message;
        $this->view->mainRightRail = '';
    }


}

