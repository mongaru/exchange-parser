<?php

class People_AuthController extends Zend_Controller_Action
{
    private $translate;

    public function init()
    {
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
    }

    public function indexAction()
    {
        // If we're already logged in, just redirect
        if (Zend_Auth::getInstance()->hasIdentity())
        {
            $this->_redirect('/');
        }
        
        $request = $this->getRequest();
        $loginForm = $this->getLoginForm();
        $this->view->errorMessage = '';
        $this->view->wrongEmail = false;
        $this->view->wrongPassword = false;
        
        
        //get error messages from the bootstrap
        $msgr = $this->_helper->FlashMessenger;
        $messages = $msgr->getMessages();

        //show error messages if the same user has logged on other computer              
        if (COUNT($messages) > 0){
             $this->view->errorMessage = $this->translate->_($messages);     
        }
                    
        $this->view->useFullWidth = true;
        if ($request->isPost())
        {
            if ($loginForm->isValid($request->getPost()))
            {
              
                // get the username and password from the form
                $email = $loginForm->getValue('email');
                $password = $loginForm->getValue('password');
                $redirectTo = $request->getParam('redirect_to', '/');

                $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

                $authAdapter->setTableName('vw_users_active')
                        ->setIdentityColumn('user_email')
                        ->setCredentialColumn('user_password')
                        ->setCredentialTreatment('SHA1(?)');
                        
                $authAdapter->setIdentity($email)
                        ->setCredential($password);

                $auth = Zend_Auth::getInstance();
                $result = $auth->authenticate($authAdapter);

                // is the user a valid one?
                if ($result->isValid())
                {
                    // get all info about this user from the login table
                    // ommit only the password, we don't need that
                    $userInfo = $authAdapter->getResultRowObject(null, 'password');
                
                      
                    $user = new People_Model_User();
                    
                    //check if the user is logged in another machine
                   /* if (!($user->getLastActuallyLogin($userInfo->ID_user))){
                           Zend_Auth::getInstance()->clearIdentity();
                          $this->view->errorMessage = $this->translate->_("This user is logged in another machine, please log out first.");
                    }else{   */   
                        
                        // We need to set to default some vars here if they are not set
                        if (trim((string)$userInfo->user_timezone) == '')
                            $userInfo->user_timezone = 'America/Los_Angeles';
                        if (trim((string)$userInfo->user_locale) == '')
                            $userInfo->user_locale = 'en_US';
                            
                                                
    
                        $session_key = $this->_generateKeySession();
                        
                        //add session_id property on the user session object
                        $userInfo->{'session_id'} = $session_key;
                                            
                        
                        
                        // Update Last Login
                        $dbAdapter->getConnection()->exec('UPDATE user_auth SET session_id= \' '.$session_key .' \' , user_last_login = \'' . date('Y-m-d H:i:s') . '\' WHERE ID_user = ' . $userInfo->ID_user);
                        
                        // Get Company Info
                        
    
                        // the default storage is a session with namespace Zend_Auth
                        $authStorage = $auth->getStorage();
                        $authStorage->write($userInfo);
    
                        // Log Activity
                        $activity = new People_Model_Activity();
                        $activity->setID_object($userInfo->ID_user);
                        $activity->setID_user($userInfo->ID_user);
                        $activity->setActivity_object_type('user');
                        $activity->setActivity_type('login');
                        $activity->setActivity_result('success');
                        $activity->setActivity_date(date('Y-m-d H:i:s'));
                        $activity->save();
                        
                        if ((strtolower($userInfo->user_department) == 'clients') && ($redirectTo == '/'))
                            $redirectTo = '/saleskit/manage';
                        $this->_redirect($redirectTo);
                  //  }    
                }
                else
                {

                    switch($result->getCode())
                    {
                        case Zend_Auth_Result::FAILURE:
                            $this->view->errorMessage = $this->translate->_("Wrong email or password provided. Please try again.");
                            $this->view->wrongEmail = true;
                            $this->view->wrongPassword = true;
                            break;

                        case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                            $this->view->wrongEmail = true;
                            break;

                        case Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS:
                            $this->view->wrongEmail = true;
                            break;

                        case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                            $this->view->wrongPassword = true;
                            break;

                        case Zend_Auth_Result::FAILURE_UNCATEGORIZED:
                            $this->view->errorMessage = $this->translate->_("Wrong email or password provided. Please try again.");
                            break;

                        default:
                            $this->view->errorMessage = $this->translate->_("Wrong email or password provided. Please try again.");
                    }
                }
            }
        }
        
        $this->view->loginForm = $loginForm;
        $requestParams = $this->_getAllParams();
        $currentLoginUri = $requestParams['module'] . '_' . $requestParams['controller'] . '_' . $requestParams['action'];
        
        if ($currentLoginUri == 'people_auth_index')
            $this->view->redirectTo = '/';
        else
            $this->view->redirectTo = $this->getRequest()->getRequestUri();
        
        //$this->_helper->layout()->disableLayout();     
        $this->view->pageTitle = $this->translate->_('Login');
        $this->view->headTitle($this->view->title);
        $this->view->domReady = "reset_forms();\nsubmit_toggle();\n";
    }

    public function logoutAction()
    {
        if (Zend_Auth::getInstance()->hasIdentity())
        {
            // Update Last Login
            $dbAdapter = Zend_Db_Table::getDefaultAdapter();
            $dbAdapter->getConnection()->exec("UPDATE user_auth SET user_last_login = '" . date('Y-m-d H:i:s') . "' WHERE ID_user = " . Zend_Auth::getInstance()->getIdentity()->ID_user);

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('logout');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();


            // clear everything - sessions is cleared also!
            Zend_Auth::getInstance()->clearIdentity();
        }

        $this->_redirect('/login');
    }

    protected function getLoginForm()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel($this->translate->_('Email') . ':')
                ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel($this->translate->_('Password') . ':')
                ->setRequired(true);

        $submit = new Zend_Form_Element_Submit('login');
        $submit->setLabel($this->translate->_('Login'));

        $loginForm = new Zend_Form();
        $loginForm->setAction('/login')
                ->setMethod('post')
                ->addElement($email)
                ->addElement($password)
                ->addElement($submit);

        return $loginForm;
    }
    
    private function _generateKeySession($length=15,$uc=TRUE,$n=TRUE,$sc=FALSE)
    {
    	$source = 'abcdefghijklmnopqrstuvwxyz';
    	if($uc==1) $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	if($n==1) $source .= '1234567890';
    	if($sc==1) $source .= '|@#~$%()=^*+[]{}-_';
    	if($length>0){
    		$rstr = "";
    		$source = str_split($source,1);
    		for($i=1; $i<=$length; $i++){
    			mt_srand((double)microtime() * 1000000);
    			$num = mt_rand(1,count($source));
    			$rstr .= $source[$num-1];
    		}
    
    	}
    	return $rstr;
    }


}