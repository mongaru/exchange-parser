<?php
/*
 * library/My/AuthorizationPlugin.php
 * Extends Zend_Controller_Plugin_Abstract
*/
class Amedia_AuthorizationPlugin extends Zend_Controller_Plugin_Abstract
{
    /**
     * Zend Auth
     *
     * @var Zend_Auth $_auth
     */
    private $_auth;
    
    /*
     * Zend ACL
     *
     * @var Zend_Acl $_acl
     */
    private $_acl;

    /*
     * No Auth
     *
     * @var array $_noauth
     */
    private $_noauth = array('module' => 'people', 'controller' => 'auth', 'action' => 'index');

    /*
     * No ACL
     *
     * @var array $_noacl
     */
    private $_noacl = array('module' => 'default', 'controller' => 'error', 'action' => 'privileges');

    /**
     *
     * @param Zend_Auth $auth
     * @param Zend_Acl $acl
     */
    public function __construct(Zend_Auth $auth, Zend_Acl $acl)
    {
        $this->_auth = $auth;
        $this->_acl = $acl;

        // TODO: Review this section, I'm not sure if it's the right way of doing it
        $registry = Zend_Registry::getInstance();
        $registry->set('amediaAuth', $auth);
        $registry->set('amediaAcl', $acl);
    }

    /**
     *
     * @param Zend_Controller_Request_Abstract $request 
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {

        // TODO: Review this section, I'm not sure if it's the right way of doing it
        // To be accessible from within the Controllers
        $request->setParam('amediaAuth', $this->_auth);
        $request->setParam('amediaAcl', $this->_acl);

        $role = 'guest';
        if (Zend_Auth::getInstance()->hasIdentity())
        {
            // Update Last Login
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $dbAdapter->getConnection()->exec("UPDATE user_auth SET user_last_login = '" . date('Y-m-d H:i:s') . "' WHERE ID_user = " . Zend_Auth::getInstance()->getIdentity()->ID_user);
            $role = isset(Zend_Auth::getInstance()->getIdentity()->user_level) ? Zend_Auth::getInstance()->getIdentity()->user_level : 'user';
        }

        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        $resource = $module . '_' . $controller;

        if ( ! $this->_acl->has($resource))
        {
            $resource = null;
        }
        
        if ( ! $this->_acl->isAllowed($role, $resource, $action))
        {
            if ( ! $this->_auth->hasIdentity())
            {
                $module = $this->_noauth['module'];
                $controller = $this->_noauth['controller'];
                $action = $this->_noauth['action'];
            }
            else
            {
                $module = $this->_noacl['module'];
                $controller = $this->_noacl['controller'];
                $action = $this->_noacl['action'];
            }
        }

        $request->setModuleName($module);
        $request->setControllerName($controller);
        $request->setActionName($action);
    }
}