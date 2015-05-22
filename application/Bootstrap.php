<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    var $frontController;

    protected function _initActionHelper()
    {
        $writer = new Zend_Log_Writer_Firebug();
        $logger = new Zend_Log($writer);
        Zend_Registry::set('logger', $logger);
    }

    protected function _initCache()
    {
        $frontendOptions = array(
            'lifetime' => 28800, // cache lifetime of 8 hours
            'automatic_serialization' => true
        );

        $backendOptions = array(
            'file_name_prefix' => 'amc_cache',
            'cache_dir' => realpath(APPLICATION_PATH . '/../cache') // Directory where to put the cache files
        );

        $fileCache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
        $outputFileCache = Zend_Cache::factory('Output', 'File', $frontendOptions, $backendOptions);
        Zend_Registry::set('fileCache', $fileCache);
        Zend_Registry::set('outputFileCache', $outputFileCache);
    }

    protected function _initAutoload()
    {
        // TODO: Review this section
        $registry = Zend_Registry::getInstance();
        $registry->set('db_config', $this->getOption('resources'));
        $db_config = Zend_Registry::get('db_config');
        $db = Zend_Db::factory($db_config['db']['adapter'], $db_config['db']['params']);
        Zend_Registry::set('db', $db);
        Zend_Db_Table::setDefaultAdapter($db);
        Zend_Db_Table_Abstract::setDefaultMetadataCache(Zend_Registry::get('fileCache'));
        Zend_Db_Table::setDefaultMetadataCache(Zend_Registry::get('fileCache'));

        // Set MySQL Server Timezone to GMT -- GLOBAL
        // $db->query("SET GLOBAL time_zone = '+00:00'");
        // Set MySQL Server Timezone to GMT -- SESSION
        $db->query("SET SESSION time_zone = '+00:00'");
        $db->query("SET time_zone = '+00:00'");

        $auth = Zend_Auth::getInstance();
        $acl = new Amedia_Acl();
        $this->frontController = Zend_Controller_Front::getInstance();
        $this->frontController->registerPlugin(new Amedia_AuthorizationPlugin($auth, $acl));

        /*
         * Translation
         */
        $translate = new Zend_Translate('gettext', APPLICATION_PATH . '/languages/en/default.mo', 'en');
        $translate->addTranslation(APPLICATION_PATH . '/languages/zh/default.mo', 'zh');

        if (Zend_Auth::getInstance()->hasIdentity())
        { 
            //get session id
            $session_id = (isset(Zend_Auth::getInstance()->getIdentity()->session_id)) ? Zend_Auth::getInstance()->getIdentity()->session_id : '';
              
            //get saved session in the BD   
            $bd_session_id = $this->_getLastSessionID(Zend_Auth::getInstance()->getIdentity()->ID_user);
            
            if(trim($session_id) != trim($bd_session_id)){
                $flashMsgHelper = Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger');
                
                $flashMsgHelper->addMessage('Someone has logged on another computer with your same credentials.');
                              
                // clear everything - sessions is cleared also!
                Zend_Auth::getInstance()->clearIdentity();
                
                //load pre-requisites to can redirect to login page
                $router =   $this->frontController->getRouter();
                $req = new Zend_Controller_Request_Http();
                $router->route($req);
                $module = $req->getModuleName();

                //redirect                                    
                $response = new Zend_Controller_Response_Http();
                $response->setRedirect('/login');
                $this->frontController->setResponse($response);
            }
            
            
            // Check if logged in user has Locale setup
            if (isset(Zend_Auth::getInstance()->getIdentity()->user_locale) && (trim(Zend_Auth::getInstance()->getIdentity()->user_locale) != ''))
            {
                $locale = new Zend_Locale(Zend_Auth::getInstance()->getIdentity()->user_locale);
            }
            else
            {
                $locale = new Zend_Locale('en_US');
            }
            $translate->setLocale($locale->getLanguage());
        }
        else
        {
            $locale = new Zend_Locale('en_US');
            $translate->setLocale('en');
        }
        $registry->set('translate', $translate);
        $registry->set('locale', $locale);

        // Set Cache
        $locale->setCache(Zend_Registry::get('fileCache'));
        $translate->setCache(Zend_Registry::get('fileCache'));

        $autoloader = new Zend_Application_Module_Autoloader(array(
                'namespace' => 'Default_',
                'basePath' => dirname(__FILE__),
            ));
        return $autoloader;
    }

    protected function _initRoutes()
    {
        $this->frontController = Zend_Controller_Front::getInstance();

        /**
         * People Module Routers
         */
        // Login
        $this->frontController->getRouter()->addRoute(
            'login', new Zend_Controller_Router_Route(
                'login',
                array(
                    'module' => 'people',
                    'controller' => 'auth',
                    'action' => 'index'
                )
            )
        );

        // Logout
        $this->frontController->getRouter()->addRoute(
            'logout', new Zend_Controller_Router_Route(
                'logout',
                array(
                    'module' => 'people',
                    'controller' => 'auth',
                    'action' => 'logout'
                )
            )
        );

        $this->frontController->getRouter()->addRoute(
            'search', new Zend_Controller_Router_Route(
                'search',
                array(
                    'module' => 'search',
                    'controller' => 'index',
                    'action' => 'index'
                )
            )
        );
    }

    protected function _initViewHelpers()
    {
        $translate = Zend_Registry::get('translate');

        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->translate = $translate;

        $view->doctype('XHTML1_STRICT');
        $view->headMeta()->appendHttpEquiv('Content-Type', 'text/html;charset=utf8');

        // Loading Site Wide CSS Files
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/amedia-at.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl('/assets/css/print/base.css?' . KK_APP_VERSION), 'print');
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/framework/forms.amedia.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/framework/lightbox.amedia.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/framework/multiselect.amedia.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/framework/tabs.amedia.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/sourced/calendar-eightysix-v1.1-default.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/sourced/calendar-eightysix-v1.1-vista.css?' . KK_APP_VERSION);
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/sourced/calendar-eightysix-v1.1-osx-dashboard.css?' . KK_APP_VERSION);

        //IE6 notice.
        $view->headLink()->appendStylesheet($view->serverUrl() . '/assets/css/browsers/notice.ie6.css?' . KK_APP_VERSION);

        // Loading Site Wide JS Files
        $view->headScript()->appendFile($view->serverUrl() . '/assets/js/modules/global.js?' . KK_APP_VERSION, $type = "text/javascript");

        $view->headTitle()->setSeparator(' - ');
        $view->headTitle($translate->_('We Are Kandy Kiss'));

        $view->applicationBaseUrl = Zend_Controller_Front::getInstance()->getBaseUrl();
        $view->loggedIn = false;
        $view->mainRightRail = '';
        $view->lightBoxes = '';

        // TODO: Review this section, I'm not sure if it's the right way of doing it
        $view->amediaAcl = Zend_Registry::get('amediaAcl');
        $view->amediaAuth = Zend_Registry::get('amediaAuth');

        // TODO: I need to automate this
        $view->addHelperPath(APPLICATION_PATH . '/modules/default/views/helpers');

        if (Zend_Auth::getInstance()->hasIdentity())
        {
            $view->loggedIn = Zend_Auth::getInstance()->hasIdentity();
            $view->userData = Zend_Auth::getInstance()->getIdentity();
			
			
            if (trim($view->userData->user_timezone) == '')
                $view->userData->user_timezone = 'America/Los_Angeles';
            if (trim($view->userData->user_locale) == '')
                $view->userData->user_locale = 'en_US';

            $userIsVendor = in_array(Zend_Auth::getInstance()->getIdentity()->user_level, array('vendor_manager', 'vendor_staff'));
            $userIsClient = in_array(Zend_Auth::getInstance()->getIdentity()->user_level, array('client_company_manager', 'client_company_member'));

            if ( ! $userIsClient AND ! $userIsVendor)
            {
                $peopleAutoloader = new Zend_Application_Module_Autoloader(array(
                        'namespace' => 'People_',
                        'basePath' => APPLICATION_PATH . '/modules/people',
                    ));
                $peopleAutoloader->load('User');

                $user = new People_Model_User();
                $view->users = $user->fetchLastLoggedUsers();
                $view->addScriptPath(APPLICATION_PATH . '/modules/default/views/scripts');
                $view->whoIsOnline = $view->render('whoIsOnline.phtml', 'default', true);
            }
        }
    }

    protected function _initZFDebug()
    {
        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->registerNamespace('ZFDebug');

        $this->bootstrap('frontController');
        $frontController = $this->getResource('frontController');

        if ($this->hasOption('zfdebug'))
        {

            $options = $this->getOption('zfdebug');

            if (isset($options['plugins']['File']))
            {
                $options['plugins']['File']['base_path'] = realpath(APPLICATION_PATH . '../');
            }

            if ($this->hasPluginResource('db'))
            {
                $this->bootstrap('db');
                $db = $this->getPluginResource('db')->getDbAdapter();
                $options['plugins']['Database']['adapter'] = $db;
            }

            if (Zend_Registry::isRegistered('fileCache'))
            {
                $fileCache = Zend_Registry::get('fileCache');
                $options['plugins']['Cache']['backend']['File'] = $fileCache->getBackend();
            }

            $debug = new ZFDebug_Controller_Plugin_Debug($options);

            $frontController->registerPlugin($debug);
        }
    }
    
      
    public function _getLastSessionID($ID_user)
    {
        $dbAdapter = Zend_Db_Table::getDefaultAdapter();
        
        //get last user login
        $result = $dbAdapter->select('*')
            ->from('user_auth')
            ->where("ID_USER=".intval($ID_user));
            
        $result_user = $result->query()->fetchAll();
            
        return $result_user[0]["session_id"];
            
        
       } 

}
