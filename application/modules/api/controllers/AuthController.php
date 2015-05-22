<?php

class Api_AuthController extends Zend_Controller_Action
{
    private $translate;

    public function init()
    {
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
        $this->_helper->layout()->disableLayout();
    }

    public function indexAction()
    {
        header('Content-Type: text/xml');
        $this->view->status = 'error';
        $this->view->message = 'General Error';
        
        $request = $this->getRequest();
        $loginForm = $this->getLoginForm();
        
        if ($request->isPost())
        {
            if ($loginForm->isValid($request->getPost()))
            {
                // get the username and password from the form
                $email = $loginForm->getValue('email');
                $password = $loginForm->getValue('password');

                $dbAdapter = Zend_Db_Table::getDefaultAdapter();
                $authAdapter = new Zend_Auth_Adapter_DbTable($dbAdapter);

                $authAdapter->setTableName('vw_users_active')
                        ->setIdentityColumn('user_email')
                        ->setCredentialColumn('user_password')
                        ->setCredentialTreatment('SHA1(?) AND user_level = "superadmin"');
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
                    // We need to set to default some vars here if they are not set
                    if (trim((string)$userInfo->user_timezone) == '')
                        $userInfo->user_timezone = 'America/Los_Angeles';
                    if (trim((string)$userInfo->user_locale) == '')
                        $userInfo->user_locale = 'en_US';

                    // Update Last Login
                    $dbAdapter->getConnection()->exec('UPDATE user_auth SET user_last_login = \'' . date('Y-m-d H:i:s') . '\' WHERE ID_user = ' . $userInfo->ID_user);

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
                    
                    $this->view->status = 'success';
			        $this->view->message = 'Logged in successfully';
                }
                else
                {
                    switch($result->getCode())
                    {
                        case Zend_Auth_Result::FAILURE:
                    		$this->view->status = 'error';
			        		$this->view->message = 'Wrong email or password provided. Please try again.';
                            break;

                        case Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND:
                    		$this->view->status = 'error';
			        		$this->view->message = 'Wrong email provided. Please try again.';
                            break;

                        case Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS:
                    		$this->view->status = 'error';
			        		$this->view->message = 'Wrong email provided. Please try again.';
                            break;

                        case Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID:
                    		$this->view->status = 'error';
			        		$this->view->message = 'Wrong password provided. Please try again.';
                            break;

                        case Zend_Auth_Result::FAILURE_UNCATEGORIZED:
                    		$this->view->status = 'error';
			        		$this->view->message = 'General Error. Please try again.';
                            break;

                        default:
                    		$this->view->status = 'error';
			        		$this->view->message = 'General error. Please try again.';
                    }
                }
            }
        }
    }
    
    protected function getLoginForm()
    {
        $email = new Zend_Form_Element_Text('email');
        $email->setLabel($this->translate->_('Email') . ':')
                ->setRequired(true);

        $password = new Zend_Form_Element_Password('password');
        $password->setLabel($this->translate->_('Password') . ':')
                ->setRequired(true);

        $loginForm = new Zend_Form();
        $loginForm->setAction('/login')
                ->setMethod('post')
                ->addElement($email)
                ->addElement($password);

        return $loginForm;
    }
}