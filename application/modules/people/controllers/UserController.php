 <?php
class People_UserController extends Zend_Controller_Action
{

    public function init()
    {
        $this->_user = Zend_Auth::getInstance()->getIdentity();
        $amedia = new Amedia_Acl();
        $this->view->isVendor = $amedia->isVendor($this->_user->user_level);
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
        $this->acl = Zend_Registry::get('amediaAcl');
        $this->view->pageID = 'people';
    }

    public function indexAction()
    {
        
        $user = new People_Model_User();

        // Pagination
        $page = $this->_getParam('page', 1);

        $GroupsOrder = $this->_getParam('groups', '0');        
        $statsFilter = $this->_getParam('stats_filter', 'no');
        $OrderField = $this->_getParam('order_by', 'user_name');
        $OrderType = $this->_getParam('order_type', 'asc');
        $UserStatus = $this->_getParam('status', 'active');

        $OrderBy = array('user_surname ' . $OrderType, 'user_firstname asc');
        if ($OrderField == 'user_name') $OrderBy = array('user_firstname ' . $OrderType, 'user_surname asc');
        if ($OrderField == 'last_seen') $OrderBy = 'user_last_login ' . $OrderType;

        $where = '';


        if (strtolower($statsFilter) == 'yes')
        {
            // View only active yields by default when applying Stats Filter
            $where.= "user_is_deleted = 'no' ";

            $statsType = strtolower($this->_getParam('stats_type', ''));
            $statsWhen = strtolower($this->_getParam('stats_when', ''));
            $statsBy = strtolower($this->_getParam('stats_by', ''));

            $userDate = new Zend_Date();
            if (trim($this->view->userData->user_timezone) != '')
                $userDate->setTimezone($this->view->userData->user_timezone);
            $userTimezoneOffset = $userDate->get(Zend_Date::GMT_DIFF_SEP);

            $whereType = '';
            $whereDate = '';
            $whereBy = '';

            if ($statsWhen != '')
            {

                if ($statsWhen == 'today')
                {
                    $whereDate = $userDate->toString('YYYY-MM-dd');
                }
                elseif ($statsWhen == 'yesterday')
                {
                    $whereDate = $userDate->sub(1, Zend_Date::DAY)->toString('YYYY-MM-dd');
                }
                else
                {
                    if (strlen($statsWhen) == 8)
                    {
                        $userDate->set(substr($statsWhen, 0, 4), Zend_Date::YEAR);
                        $userDate->set(substr($statsWhen, 4, 2), Zend_Date::MONTH);
                        $userDate->set(substr($statsWhen, 6, 2), Zend_Date::DAY);
                        $whereDate = $userDate->toString('YYYY-MM-dd');
                    }
                    else
                        $whereDate = $userDate->toString('YYYY-MM-dd');
                    $statsWhen = $userDate->get(Zend_Date::DATE_MEDIUM);
                }
            }
            if ($statsType != '')
            {
                if ($statsType == 'added')
                    $where.= "AND CAST(CONVERT_TZ(user_auth.user_created, '+00:00', '$userTimezoneOffset') AS date) = '$whereDate' ";
                if ($statsType == 'loggedin')
                {
                    if ($statsBy == '')
                    {
                        $activity = new People_Model_Activity();
                        $activities = $activity->fetchAll("activity_object_type = 'user' AND activity_type = 'login' AND activity_result = 'success' AND CAST(CONVERT_TZ(activity_date, '+00:00', '$userTimezoneOffset') AS date) = '$whereDate'");
                        $loggedinUsers = array();
                        foreach($activities as $activity)
                        {
                            if ( ! in_array($activity->ID_user, $loggedinUsers))
                                $loggedinUsers[] = $activity->ID_user;
                        }
                        $where.= "AND user.ID_user IN (" . implode(',', $loggedinUsers) . ") ";
                    }
                    $statsType = 'who Logged In';
                }
            }

            $userName = '';
            if ($statsBy != '')
            {
                $where.= "AND user.ID_user = '$statsBy' ";
                $patternMaker = new People_Model_User();
                $patternMaker = $patternMaker->find($statsBy);
                $userName = $patternMaker->user_firstname . ' ' . $patternMaker->user_surname;
            }

            if ($userName == '')
                $this->view->pageTitle = sprintf($this->translate->_("Users %1\$s %2\$s"), ucwords($statsType), ucwords($statsWhen));
            else
                $this->view->pageTitle = sprintf($this->translate->_("Users %1\$s %2\$s by %3\$s"), ucwords($statsType), ucwords($statsWhen), $userName);
        }
        else
        {
            if ($UserStatus == 'active')
                $where = "user_is_deleted = 'no'";
            else
                $where = "user_is_deleted = 'yes'";
        }

        //get all companies 


        $companyModel = new Company_Model_Company();

        $where1 = "company_is_deleted = 'no'";
        $order = "company_name";
        // here is where the magic happens
        $companies = Zend_Paginator::factory($companyModel->fetchAll($where1,$order));
        $companies->setItemCountPerPage(100);
        $companies->setCurrentPageNumber($page);
        $companies->setPageRange(3);
        //        
       
     if($GroupsOrder!= '' && $GroupsOrder > 0  ){
                $where.= 'AND company_user.ID_company = '.$GroupsOrder ;
        }
        $this->view->Grouporder = $GroupsOrder;
        $this->view->companies = $companies;
        // here is where the magic happens
        $paginator = Zend_Paginator::factory($user->fetchAll($where, $OrderBy));
        $paginator->setItemCountPerPage(100);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(3);
        $this->view->useFullWidth = true;
        $this->view->current_order_by = $OrderField;
        $this->view->current_order_type = $OrderType;
        $this->view->current_user_status = $UserStatus;
        $this->view->current_page = $page;
        $this->view->current_group=  $GroupsOrder;
        $this->view->headTitle($this->view->pageTitle);
        $this->view->paginator = $paginator;

    }
    public function getAddrAvatarAction(){
        $ID_file = $this->_getParam('id_avatar', 0);
        $this->_helper->layout()->disableLayout();      
        if($ID_file !== null && $ID_file!== "0") {
            $fileModel = new People_Model_File();
            $avatarFile = $fileModel->find($ID_file);
            $imageLib = Amedia_ImageLib::getInstance();
            $imageLib->setSourceFile($avatarFile->file_full_path, $avatarFile->file_name);
            $this->view->avatarInfo = $this->view->serverUrl($imageLib->resize('128x128')->getUrl()); 
        } else {
            $this->view->avatarInfo = $this->view->serverUrl('/images/client128.gif');
        }

        $this->_helper->viewRenderer('addavatar', null, false);        

    }


    public function addtocompanyAction()
    {
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $company = new Company_Model_Company();
        $company->find($ID_company);

        if ( ! $company->ID_company)
            return $this->_redirect('/company/manage');

        $ID_user = $this->addAction(true, $company);

        if ($ID_user > 0)
        {
            $cuser = new Company_Model_CompanyUser();
            $cuser->setID_company($ID_company);
            $cuser->setID_user($ID_user);
            $ID_company_user = $cuser->save();

             // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_company_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('company_user');
            $activity->setActivity_type('add');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();
            return $this->_redirect('/company/manage/view/id/' . $ID_company);
        }
        $this->view->pageTitle = $this->translate->_('Add People to ').$company->company_name;
        $this->view->headTitle($this->translate->_('Add People to ').$company->company_name);
    }
    
    public function addAction($return = false, $company = null)
    {

        $Json = $this->_getParam('json', false);
        $this->view->json = $Json;
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/application/jquery.form.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/user.js', $type = "text/javascript");
        $this->view->pageTitle = 'Add People';
        $this->view->headTitle('Add People');
        $formUserAdd = new People_Form_UserAdd();
        $this->view->form = $formUserAdd;

        $request = $this->getRequest();
        $values = $request->getPost();
        $priv_model = new Amedia_Model_Privileges();
        $this->view->myPrivName = $priv_model->find(Zend_Auth::getInstance()->getIdentity()->user_level); 

        if ($request->isPost() && $formUserAdd->isValid($values))
        {
            $this->_helper->flashMessenger->addMessage('User successfully saved');
            $user = new People_Model_User($formUserAdd->getValues());
            $user->user_is_deleted = 'no';
            $user->user_permanently_deleted = 'no';
            $user->user_locale = ($user->user_locale == '')? 'America/Los_Angeles' : $user->user_locale;
            $ID_user = $user->save();
            $cuser = new Company_Model_CompanyUser();
            $cuser->setID_company($values['ID_company']);
            $cuser->setID_user($ID_user);
            $ID_company_user = $cuser->save();
            
            $userAuth = new People_Model_UserAuth();
            $userAuth->setID_user($ID_user);
            $userAuth->setUser_salt('');
            if ((trim($formUserAdd->getValue('user_password')) == '') && (trim($formUserAdd->getValue('password_conf')) == ''))
            {
                $password = $userAuth->generatePassword(8, 4);
            }
            else
            {
                $password = $formUserAdd->getValue('user_password');

            }
            $userAuth->setUser_password(sha1($password));
            $userAuth->setUser_status(0);
            if($values['ID_auth']!=''){
                $userAuth->setID_auth($values['ID_auth']);
            }
            $userAuth->setUser_avatar_file($values['user_avatar_file']);
            $userAuth->setUser_level($values['user_level']);
            $userAuth->save();

            $userOptional = new People_Model_UserOptional();
            $userOptional->setID_user($ID_user);
            $userOptional->setUser_city('');
            $userOptional->setUser_state('');
            $userOptional->setUser_zip('');
            $userOptional->setUser_department($formUserAdd->getValue('user_department'));            
            $userOptional->setUser_phone($formUserAdd->getValue('user_phone'));
            $userOptional->setUser_mobile($formUserAdd->getValue('user_mobile'));
            $userOptional->setUser_country('');
            $userOptional->setUser_region('');

            $userOptional->save();

            $email_body = 'Hi ' . $formUserAdd->getValue('user_firstname') . '!' . "\n";
            $email_body.= "\n";
            $email_body.= $formUserAdd->getValue('welcome_message') . "\n";
            $email_body.= "\n";
            $email_body.= 'Here is your new account info:' . "\n";
            $email_body.= 'Email: ' . $formUserAdd->getValue('user_email') . "\n";
            $email_body.= 'Password: ' . $password . "\n";
            $email_body.= "\n";
            $email_body.= 'To access, please go to:' . "\n";
            $email_body.= $this->view->serverUrl('/login') . "\n";
            $email_body.= "\n";
            $email_body.= 'Best,' . "\n";
            $email_body.= 'Kandy Kiss' . "\n";
            $email = new Zend_Mail();
            $email->setSubject('[Kandy Kiss] - New Account Information');
            $email->setFrom('do_not_reply@kandykiss.com', 'Do Not Reply');
            $email->addTo($formUserAdd->getValue('user_email'), $formUserAdd->getValue('user_firstname') . ' ' . $formUserAdd->getValue('user_surname'));
            $email->setBodyText($email_body);
            // $email->send();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('add');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();
            
        if(!$Json){
            return $this->_redirect('/people/user/view/id/' . $ID_user);
        }
         else {
                $this->_helper->json(array('status' => 'ok', 'data' => $ID_user));    
         }
            

        } elseif ($request->isPost() && !$formUserAdd->isValid($values) && $Json) {
                $this->_helper->json(array('status' => 'ok', 'data' => ':O' ));
        } else { 
 
               $this->view->avatarInfo = 'images/client128.gif';
                $company = new Company_Model_Company();
                $this->view->companyOptions = $company->fetchActiveCompaniesOptions();

                $privileges = new Amedia_Model_Privileges();
                $this->view->roleOptions = $privileges->fetchAll();
                $this->view->mainRightRail = $this->view->whoIsOnline; // by default the whoIsOnline box is the right rail
                $this->view->languageOptions = array(
                    'en_US' => $this->translate->_('English'), 
                    'zh_CN' => $this->translate->_('Chinese')
                );

            if($Json) {
                $this->_helper->json(array('status' => 'ok', 'content' =>$this->view->render('/user/add.phtml') ));
            }

        }
        
    }

    public function editAction()
    {

        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/application/jquery.form.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/user.js', $type = "text/javascript");

        $ID_user = $this->getRequest()->getParam('id', 0);
            $request = $this->getRequest();

        if ($ID_user <= 0 && !$request->isPost())
            return $this->_redirect('/people/user');
        
        // Instantiates User Add form
        $formUserEdit = new People_Form_UserEdit();

        // Assign select values
        $roles = array();
        $this->view->isEdit = true;
        // Assign select values
        $user = new People_Model_User();
        $company = $user->getUserCompany($ID_user);
        $this->view->userCompany = $company;
        $locales = array(
            'en_US'  => $this->translate->_('English'),
            'zh_CN'  => $this->translate->_('Chinese')
        );
        $this->view->form = $formUserEdit;

        // Gets Request Vars
            $user = new People_Model_User();
            
        if ($request->isPost() && $formUserEdit->isValid($request->getPost()))
        {
            $values = $request->getPost();       
            $user = new People_Model_User($formUserEdit->getValues());
            $user->user_is_deleted = 'no';
            $user->user_permanently_deleted = 'no';
            $user->user_locale = ($user->user_locale == '')? 'America/Los_Angeles' : $user->user_locale;
            $ID_user = $user->save();

            $cuser = new Company_Model_CompanyUser();

            $cuser->getCompanyUser($values['ID_user']);
            $cuser->setID_company($values['ID_company']);
            $ID_company_user = $cuser->save();

            //var_dump($cuser);

            $userAuth = new People_Model_UserAuth();
            $userAuth->setID_user($ID_user);
            $userAuth->setUser_salt('');
            $password = '';
            if (!((trim($formUserEdit->getValue('user_password')) == '') || (trim($formUserEdit->getValue('password_conf')) == '')))
            {
                $password = $formUserEdit->getValue('user_password');
                $userAuth->setUser_password(sha1($password));
            }
            
            $userAuth->setUser_status(0);
            if($values['ID_auth']!=''){
                $userAuth->setID_auth($values['ID_auth']);
            }
            if($values['user_avatar_file']!=''){
                $userAuth->setUser_avatar_file($formUserEdit->getValue('user_avatar_file'));
            }            
            $userAuth->setUser_level($values['user_level']);
            $userAuth->save();
            $userOptional = new People_Model_UserOptional();
            $userOptional->setID_user($ID_user);
            $userOptional->setID_optional($values['ID_optional']);
            $userOptional->setUser_city('');
            $userOptional->setUser_department($formUserEdit->getValue('user_department'));                        
            $userOptional->setUser_state('');
            $userOptional->setUser_zip('');
            $userOptional->setUser_phone($formUserEdit->getValue('user_phone'));
            $userOptional->setUser_mobile($formUserEdit->getValue('user_mobile'));
            $userOptional->setUser_country('');
            $userOptional->setUser_region('');

            $userOptional->save();
       
            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('add');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            $this->_helper->flashMessenger->addMessage('User successfully Edited');
            return $this->_redirect('/people/user/view/id/' . $ID_user);
        } 
        else 
        {
            $da_ = $user->find($request->getParam('id', Zend_Auth::getInstance()->getIdentity()->ID_user));

            $this->view->pageTitle = $this->translate->_('Edit User') . $user->user_firstname . ' ' . $user->user_surname;
            $this->view->headTitle($this->translate->_('Edit User') . $user->user_firstname . ' ' . $user->user_surname);
            $forpopulate  = $da_->completlyArrayData();
            $forpopulate['ID_company'] = $company['ID_company']; 
            
                //avatar data
                $fileModel = new People_Model_File();
                if($forpopulate['user_avatar_file'] !== null && $forpopulate['user_avatar_file'] !== "0"){
                    $avatarFile = $fileModel->find($forpopulate['user_avatar_file']);
                    
                    $imageLib = Amedia_ImageLib::getInstance();
                    $imageLib->setSourceFile($avatarFile->file_full_path, $avatarFile->file_name);
                    $this->view->avatarInfo = $this->view->serverUrl($imageLib->resize('128x128')->getUrl());            
                } else {
                    $this->view->avatarInfo = 'images/client128.gif';
                } 


            $formUserEdit->populate($forpopulate);

            $this->view->formErrorMessages = $formUserEdit->getErrorMessages();
            $this->view->userInfo = $user;
            $userActionsDisplay = array(
                'showEdit'         => false,
                'showChangePass'   => false,
                'showTrash'        => false,
                'showChangeAvatar' => true
            );

            $this->view->languageOptions = array(
                'en_US' => $this->translate->_('English'), 
                'zh_CN' => $this->translate->_('Chinese')
            );            
     
            $privileges = new Amedia_Model_Privileges();
            $this->view->roleOptions = $privileges->fetchAll();
            $company = new Company_Model_Company();
            $this->view->companyOptions = $company->fetchActiveCompaniesOptions();
          //  $this->view->mainRightRail .= $this->view->render('UserActions.phtml');
            $this->view->ID_user = $request->getParam('id');
            $this->view->lightBoxes .= $this->view->render('lbChangeAvatar.phtml');

            $this->_helper->viewRenderer('add', null, false);
            $this->view->roles = $roles;
            $this->view->locales = $locales;
    }
}

    public function myAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/user.js', $type = "text/javascript");
                $this->view->languageOptions = array(
                    'en_US' => $this->translate->_('English'), 
                    'zh_CN' => $this->translate->_('Chinese')
                );

        $this->view->pageTitle = $this->translate->_('Edit My Profile');
        $this->view->headTitle($this->view->pageTitle);

        // Instantiates User Add form
        $formUserMy = new People_Form_UserMy();

        $locales = array(
            'en_US'  => $this->translate->_('English'),
            'zh_CN'  => $this->translate->_('Chinese')
        );

        $this->view->form = $formUserMy;
        $user = new People_Model_User();
        $company = $user->getUserCompany(Zend_Auth::getInstance()->getIdentity()->ID_user);
        $this->view->userCompany = $company;
        // Gets Request Vars
        $request = $this->getRequest();

        //$this->_helper->viewRenderer->setNoRender();

        if (($request->isPost()) && ($formUserMy->isValid($request->getPost())))
        {
            $values = $request->getPost();                   
            //var_dump($request->getPost());
            $ID_user = Zend_Auth::getInstance()->getIdentity()->ID_user;
            $user = new People_Model_User();
            $user->find($ID_user);
            $user->setOptions($formUserMy->getValues());
            //var_dump($user); die();
            $user->save();

            $userAuth = new People_Model_UserAuth();
            $userAuth->find($ID_user);
            $password = '';
            
            if (!((trim($formUserMy->getValue('user_password')) == '') || (trim($formUserMy->getValue('password_conf')) == '')))
            {
                $password = $formUserMy->getValue('user_password');
                $userAuth->setUser_password(sha1($password));
            }
            
            $userAuth->setUser_status(0);
            
            if($values['ID_auth']!=''){
                $userAuth->setID_auth($values['ID_auth']);
            }
            
            if($values['user_avatar_file']!=''){
                $userAuth->setUser_avatar_file($formUserMy->getValue('user_avatar_file'));
            }            

            //$userAuth->setUser_level($values['user_level']);
            $userAuth->save();


        
            $userOptional = new People_Model_UserOptional();
            $userOptional->setID_optional($formUserMy->getValue('ID_optional'));
            $userOptional->setID_user($ID_user);
            $userOptional->setUser_address1($formUserMy->getValue('user_address1'));
            $userOptional->setUser_address2($formUserMy->getValue('user_address2'));
            $userOptional->setUser_city('');
            $userOptional->setUser_state('');
            $userOptional->setUser_zip('');
            $userOptional->setUser_position($formUserMy->getValue('user_position'));
            //$userOptional->setUser_department('Yields Department');
            $userOptional->setUser_phone($formUserMy->getValue('user_phone'));
            $userOptional->setUser_phone_ext($formUserMy->getValue('user_phone_ext'));
            $userOptional->setUser_mobile($formUserMy->getValue('user_mobile'));
            $userOptional->setUser_fax($formUserMy->getValue('user_fax'));
            $userOptional->setUser_aim($formUserMy->getValue('user_aim'));
            $userOptional->setUser_msn($formUserMy->getValue('user_msn'));
            $userOptional->setUser_country('');
            $userOptional->setUser_region('');
            $userOptional->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('edit');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

           return $this->_redirect('/people/user/view/id/' . $ID_user);
        }
        else
        {
            $user = new People_Model_User();
            $user->find(Zend_Auth::getInstance()->getIdentity()->ID_user);
    
            $userAuth = $user->getuser_auth();
            $userOptional = $user->getuser_optional();
            $values['ID_user'] = $user->getID_user();
            $values['user_email'] = $user->getuser_email();
            $values['user_firstname'] = $user->getuser_firstname();
            $values['user_surname'] = $user->getuser_surname();
            $values['user_position'] = $userOptional->user_position;
            $values['user_phone'] = $userOptional->user_phone;
            $values['user_phone_ext'] = $userOptional->user_phone_ext;
            $values['user_mobile'] = $userOptional->user_mobile;
            $values['user_fax'] = $userOptional->user_fax;
            $values['user_locale'] = $user->getuser_locale();
            $values['user_timezone'] = $user->getuser_timezone();
            $values['user_aim'] = $userOptional->user_aim;
            $values['user_msn'] = $userOptional->user_msn;
            $values['ID_auth'] = $userAuth->ID_auth;
            $values['ID_optional'] = $userOptional->ID_optional;
            $formUserMy->populate($values);

            $this->view->formErrorMessages = $formUserMy->getErrorMessages();
            $company = $user->getUserCompany($user->ID_user);
            $this->view->userCompany = $company;
            $this->view->userInfo = $user;
            $userActionsDisplay = array(
                'showEdit'         => false,
                'showChangePass'   => true,
                'showTrash'        => false,
                'showChangeAvatar' => true
            );

            $da_ = $user->find($request->getParam('id', Zend_Auth::getInstance()->getIdentity()->ID_user));
            $forpopulate  = $da_->completlyArrayData();
            $forpopulate['ID_company'] = $company['ID_company']; 
            
                //avatar data
                $fileModel = new People_Model_File();
                if($forpopulate['user_avatar_file'] !== null && $forpopulate['user_avatar_file'] !== "0"){
                    $avatarFile = $fileModel->find($forpopulate['user_avatar_file']);
                    $this->view->avatarFile =  $avatarFile->ID_file;
                    $imageLib = Amedia_ImageLib::getInstance();
                    $imageLib->setSourceFile($avatarFile->file_full_path, $avatarFile->file_name);
                    $this->view->avatarInfo = $this->view->serverUrl($imageLib->resize('128x128')->getUrl());            
                } else {
                    $this->view->avatarInfo = 'images/client128.gif';
                } 

            $this->view->assign($userActionsDisplay);
            $this->view->mainRightRail .= $this->view->render('UserActions.phtml');
            $this->view->ID_user = $request->getParam('id');
            $this->view->lightBoxes .= $this->view->render('lbChangeAvatar.phtml');
        }
    }

    public function changepasswordAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/framework/multiselect.amedia.fx.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/framework/lightbox.amedia.fx.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/extensions/user.edit.fx.js', $type = "text/javascript");
        $this->view->domReady = "reset_forms();\nsubmit_toggle();\n";


        // Gets Request Vars
        $request = $this->getRequest();

        $user = new People_Model_User();
        if ($this->acl->isAllowed(Zend_Auth::getInstance()->getIdentity()->user_level, 'users_changepassword'))
        {
            $idUser = $request->getParam('id', Zend_Auth::getInstance()->getIdentity()->ID_user);
            $user->find($idUser);
            $this->view->pageTitle = $this->translate->_('Change password for user ') . '"' . $user->user_firstname . ' ' . $user->user_surname . '"';
            $this->view->headTitle($this->translate->_('Change password for user ') . '"' . $user->user_firstname . ' ' . $user->user_surname . '"');
        }
        else
        {
            $idUser = Zend_Auth::getInstance()->getIdentity()->ID_user;
            $user->find($idUser);
            $this->view->pageTitle = $this->translate->_('Change My Password');
            $this->view->headTitle($this->translate->_('Change My Password'));
        }

        // Instantiates User Add form
        $form = new People_Form_UserChangePassword();

        $this->view->form = $form;

        $this->_helper->viewRenderer->setNoRender();

        $passValidator = new Zend_Validate_Identical();
        $passValidator->setToken($this->_request->getParam('user_password'));
        $passValidator->setMessage($this->translate->_('Passwords does not match.'), Zend_Validate_Identical::NOT_SAME);
        $form->getElement('password_conf')->addValidator($passValidator);

        if (($request->isPost()) && ($form->isValid($request->getPost())))
        {
            $newPassword = $request->getParam('user_password');
            $userAuth = new People_Model_UserAuth();
            $userAuth->find($idUser);
            $userAuth->setUser_password(sha1($newPassword));
            $userAuth->save();

            $user = new People_Model_User();
            $user->find($idUser);

            $email_body = 'Hi ' . $user->user_firstname . '!' . "\n";
            $email_body.= "\n";
            $email_body.= 'Your password has been changed.' . "\n";
            $email_body.= 'Here is your new login information:' . "\n";
            $email_body.= 'Email: ' . $user->user_email . "\n";
            $email_body.= 'Password: ' . $newPassword . "\n";
            $email_body.= "\n";
            $email_body.= 'To access, please go to:' . "\n";
            $email_body.= $this->view->serverUrl('/login') . "\n";
            $email_body.= "\n";
            $email_body.= 'If you did not request this change, please contact the system administrator immediately.' . "\n";
            $email_body.= "\n";
            $email_body.= 'Best,' . "\n";
            $email_body.= 'Kandy Kiss' . "\n";
            $email = new Zend_Mail();
            $email->setSubject('[Kandy Kiss] - Password Change Notification');
            $email->setFrom('do_not_reply@kandykiss.com', 'Do Not Reply');
            $email->addTo($user->user_email, $user->user_firstname . ' ' . $user->user_surname);
            $email->setBodyText($email_body);
            $email->send();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($idUser);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('change_password');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            return $this->_redirect('/people/user/view/id/' . $idUser);
        }
        else
        {
            $form->populate(array('ID_user' => $idUser));
            $this->view->formErrorMessages = $form->getErrorMessages();
            $this->view->ID_user = $idUser;
            $this->getResponse()->setBody($this->view->render('user/changepassword.phtml'));
        }
    }

    public function viewAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/framework/lightbox.amedia.fx.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/extensions/user.fx.js', $type = "text/javascript");
        
        $this->view->messages = $this->_helper->flashMessenger->getMessages();

        $user = new People_Model_User();
        $ID_user = $this->_getParam('id', 1);
        $userData = $user->find($ID_user);
        //avatar data
        $fileModel = new People_Model_File();

        if($userData->user_auth['user_avatar_file'] != null && $userData->user_auth['user_avatar_file'] != "0"){
            $avatarFile = $fileModel->find($userData->user_auth['user_avatar_file']);

            $imageLib = Amedia_ImageLib::getInstance();
            $imageLib->setSourceFile($avatarFile->file_full_path, $avatarFile->file_name);
            $this->view->avatarInfo = $this->view->serverUrl($imageLib->resize('128x128')->getUrl());            
        } else {
            $this->view->avatarInfo = 'images/client128.gif';
        } 



        //var_dump($userData);
        $userCompany = $user->getUserCompany($ID_user);
        $this->view->userInfo = $userData;
        $this->view->userCompany = $userCompany;

        $this->view->pageTitle = $userData->user_firstname . ' ' . $userData->user_middlename . ' ' . $userData->user_surname;
        $this->view->headTitle($userData->user_firstname . ' ' . $userData->user_middlename . ' ' . $userData->user_surname);

        $userActionsDisplay = array(
            'showEdit'         => true,
            'showChangePass'   => true,
            'showTrash'        => true,
            'showChangeAvatar' => true
        );
        
        $this->view->assign($userActionsDisplay);
        $this->view->ID_user = $ID_user;
        $this->view->useFullWidth = true;

        $permissionsModel = new Amedia_Model_Privileges();
        $this->view->permissionsdata = $permissionsModel->find($userData->user_auth->user_level);
        
        $this->view->lightBoxes .= $this->view->render('lbChangeAvatar.phtml');
        $this->view->mainRightRail .= $this->view->render('UserActions.phtml');
        $this->view->profileEdited = $this->getRequest()->getParam('edited', 'no');
    
}
    public function trashAction()
    {
        $ID_user = $this->_getParam('id', 0);
        if ($ID_user > 0)
        {
            $user = new People_Model_User();
            $user->find($ID_user);
            $user->setUser_is_deleted('yes');
            $user->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('trash');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();
        }
        if ($_SERVER['HTTP_REFERER'])
            return $this->_redirect($_SERVER['HTTP_REFERER']);
        else
            return $this->_redirect('/people/user');
    }

    public function restoreAction()
    {
        $user = new People_Model_User();
        $ID_user = $this->_getParam('id', -999);
        if ($ID_user > 0)
        {
            $user->find($ID_user);
            $user->setUser_is_deleted('no');
            $user->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_user);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('restore');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();
        }
        if ($_SERVER['HTTP_REFERER'])
            return $this->_redirect($_SERVER['HTTP_REFERER']);
        else
            return $this->_redirect('/people/user');
    }

    public function updateavatarAction()
    {
    // Instantiates HTTP File Transfer
        $fileAdapter = new Zend_File_Transfer_Adapter_Http();

        // use Byte string for file sizes
        $fileAdapter->setOptions(array('useByteString' => false));

        // sets files destination
        $fileAdapter->setDestination('uploads');

    	/*
    	 * File Validations
    	 */
        // Set a min size of 20 and a max size of 8000000 bytes
        $fileAdapter->addValidator('Size', false, array('min' => 20, 'max' => 8000000));

        // Limits the extesions to jpg, gif and png files
        $fileAdapter->addValidator('Extension', false, array('jpg', 'gif', 'png'));

        $files = $fileAdapter->getFileInfo();

        foreach($files as $file => $info)
        {
            // file uploaded?
            if ( ! $fileAdapter->isUploaded($file))
            {
                echo "Why haven't you uploaded the file?";
                continue;
            }

            // validators are ok?
            if ( ! $fileAdapter->isValid($file))
            {
                echo "Sorry but $file is not what we wanted.";
                continue;
            }

            if ($fileAdapter->receive($file))
            {
                $fileData = $fileAdapter->getFileInfo($file);
                $fileInfo = array();
                $fileInfo['ID_user']           = Zend_Auth::getInstance()->getIdentity()->ID_user;
                $fileInfo['file_title']        = 'Avatar';
                $fileInfo['file_description']  = 'Avatar file';
                $fileInfo['file_display']      = 'yes';
                $fileInfo['file_views']        = 0;
                $fileInfo['file_is_image']     = 'yes';
                $fileInfo['file_name']         = $fileAdapter->getFileName($file, false);
                $fileInfo['file_type']         = $fileAdapter->getMimeType($file);
                $fileInfo['file_hash']         = $fileAdapter->getHash('crc32', $file);
                $fileInfo['file_path']         = '';
                $fileInfo['file_full_path']    = $fileAdapter->getFileName($file);
                $fileInfo['file_raw_name']     = '';
                $fileInfo['file_orig_name']    = '';
                $fileInfo['file_extension']    = '';
                $fileInfo['file_size']         = $fileAdapter->getFileSize($file);
                $fileInfo['file_image_width']  = '';
                $fileInfo['file_image_height'] = '';
                $fileInfo['file_image_type']   = '';
                $fileModel = new People_Model_File($fileInfo);
                $fileId = $fileModel->save();

                $user = new People_Model_UserAuth();
                $user->find($this->getRequest()->getParam('ID_user'));

                $user->setUser_avatar_file($fileId);
                $user->save();

                // Log Activity
                $activity = new People_Model_Activity();
                $activity->setID_object($this->getRequest()->getParam('ID_user'));
                $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
                $activity->setActivity_object_type('user');
                $activity->setActivity_type('change_avatar');
                $activity->setActivity_result('success');
                $activity->setActivity_date(date('Y-m-d H:i:s'));
                $activity->save();

                return $this->_redirect('/people/user/view/id/' . $this->getRequest()->getParam('ID_user'));
            }
        }
    }

    /**
     * Set User Avatar JSON Handler
     */
    public function setavatarAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');
        $id = $this->_request->getParam('id', '');
        $userId = $this->_request->getParam('userId', '');
        $data['status'] = 'error';
        $data['message'] = $this->translate->_('Error processing your request.');
        if ($id != '')
        {
            $user = new People_Model_UserAuth();
            $user->find($userId);
            $user->setUser_avatar_file($id);
            $user->save();

            $avatar = new People_Model_File();
            $avatar->find($id);
            if ($avatar->getfile_full_path() != '')
            {
                $imageLib = Amedia_ImageLib::getInstance();
                $imageLib->setSourceFile($avatar->getfile_full_path(), $avatar->getfile_name());
                $avatarFile = $imageLib->resize('64x64')->getUrl();
            }
            else
            {
                $avatarFile = $this->serverUrl('/images/avatars/default64x64.gif');
            }

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($userId);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('change_avatar');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            $data['status'] = 'success';
            $data['payload'] = array('avatarPath' => $avatarFile);
        }

        $json = json_encode($data);
        echo $json;
    }

    /*
     * Get User Avatar JSON Handler
     */
    public function getavatarAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');
        $id = $this->_request->getParam('id', '');
        $data['status'] = 'error';
        $data['message'] = $this->translate->_('Error processing your request.');
        if ($id != '')
        {
            $user = new People_Model_UserAuth();
            $user->find($id);
            $fileIdAvatar = $user->getuser_avatar_file();

            $avatar = new People_Model_File();
            $avatar->find($fileIdAvatar);
            if ($avatar->getfile_full_path() != '')
            {
                $imageLib = Amedia_ImageLib::getInstance();
                $imageLib->setSourceFile($avatar->getfile_full_path(), $avatar->getfile_name());
                $avatarFile = $imageLib->resize('64x64')->getUrl();
            }
            else
            {
                $avatarFile = $this->serverUrl('/images/avatars/default64x64.gif');
            }
            $data['status'] = 'success';
            $data['payload'] = array('avatarPath' => $avatarFile);
        }

        $json = json_encode($data);
        echo $json;
    }

    /*
     * Remove Avatar JSON Handler
     */
    public function removeavatarAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');
        $id = $this->_request->getParam('id', '');
        $data['status'] = 'error';
        $data['message'] = $this->translate->_('Error processing your request.');
        if ($id != '')
        {
            $user = new People_Model_UserAuth();
            $user->find($id);
            $user->setUser_avatar_file(null);
            $user->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($id);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('user');
            $activity->setActivity_type('remove_avatar');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            $data['status'] = 'success';
            $data['message'] = '';
            $data['payload'] = array('avatarPath' => '/images/avatars/default64x64.gif');
        }

        $json = json_encode($data);
        echo $json;
    }

    /**
     * List Roles
     */
    private function _listRoles($user_level, $company_type = null)
    {
        $roles = array();
        switch ($user_level)
        {
            case 'superadmin':

                if ( ! in_array($company_type, array('client', 'vendor')))
                    $roles['Admins'] = array(
                        'superadmin'            => $this->translate->_('Super Administrator')
                    );

                if ( ! in_array($company_type, array('client', 'vendor')))
                    $roles['Yields'] = array(
                        'yield_admin'           => $this->translate->_('Yield Administrator'),
                        'yielder_manager'       => $this->translate->_('Yielder Manager'),
                        'pattern_maker_manager' => $this->translate->_('Pattern Maker Manager'),
                        'pattern_maker'         => $this->translate->_('Pattern Maker'),
                        'yielder'               => $this->translate->_('Yielder'),
                        'user'                  => $this->translate->_('User')
                    );

                if ( ! in_array($company_type, array('client', 'vendor')))
                    $roles['Sales'] = array(
                        'sales_admin'           => $this->translate->_('Sales Administrator'),
                        'sales_manager'         => $this->translate->_('Sales Manager'),
                        'sales_staff'           => $this->translate->_('Sales Staff')
                    );

                if ( ! in_array($company_type, array('client', 'vendor')))
                    $roles['Costing'] = array(
                        'costing_manager'=> $this->translate->_('Costing Manager'),
                        'costing_staff' => $this->translate->_('Costing Staff')
                    );

                if (in_array($company_type, array('client')))
                    $roles['Clients'] = array(
                        'client_company_manager'=> $this->translate->_('Client Company Manager'),
                        'client_company_member' => $this->translate->_('Client Company Member')
                    );
                
                if (in_array($company_type, array('vendor')))
                    $roles['Vendors'] = array(
                        'vendor_manager'=> $this->translate->_('Vendor Manager'),
                        'vendor_staff' => $this->translate->_('Vendor Staff')
                    );
                break;

            case 'yield_admin':
                $roles['Yields'] = array(
                    'yielder_manager'       => $this->translate->_('Yielder Manager'),
                    'pattern_maker_manager' => $this->translate->_('Pattern Maker Manager'),
                    'pattern_maker'         => $this->translate->_('Pattern Maker'),
                    'yielder'               => $this->translate->_('Yielder'),
                    'user'                  => $this->translate->_('User')
                );
                break;

            case 'yielder_manager':
                $roles['Yields'] = array(
                    'yielder'               => $this->translate->_('Yielder')
                );
                break;

            case 'pattern_maker_manager':
                $roles['Yields'] = array(
                    'pattern_maker'         => $this->translate->_('Pattern Maker')
                );
                break;

            case 'yielder':
                $roles['Yields'] = array(
                    'user'                  => $this->translate->_('User')
                );
                break;

            case 'pattern_maker':
                $roles['Yields'] = array(
                    'user'                  => $this->translate->_('User')
                );
                break;

            case 'sales_admin':
                $roles['Sales'] = array(
                    'sales_manager'         => $this->translate->_('Sales Manager'),
                    'sales_staff'           => $this->translate->_('Sales Staff')
                );
                $roles['Clients'] = array(
                    'client_company_manager'=> $this->translate->_('Client Company Manager'),
                    'client_company_member' => $this->translate->_('Client Company Member')
                );
                break;

            case 'sales_manager':
                $roles['Sales'] = array(
                    'sales_staff'           => $this->translate->_('Sales Staff')
                );
                $roles['Clients'] = array(
                    'client_company_manager'=> $this->translate->_('Client Company Manager'),
                    'client_company_member' => $this->translate->_('Client Company Member')
                );
                break;

            case 'client_company_manager':
                $roles['Clients'] = array(
                    'client_company_manager'=> $this->translate->_('Client Company Manager'),
                    'client_company_member' => $this->translate->_('Client Company Member')
                );
                break;

            case 'costing_manager':
                $roles['Costing'] = array(
                    'costing_staff' => $this->translate->_('Costing Staff')
                );
                $roles['Vendors'] = array(
                    'vendor_manager'=> $this->translate->_('Vendor Manager'),
                    'vendor_staff' => $this->translate->_('Vendor Staff')
                );
                break;

            case 'vendor_manager':
                $roles['Vendors'] = array(
                    'vendor_manager'=> $this->translate->_('Vendor Manager'),
                    'vendor_staff' => $this->translate->_('Vendor Staff')
                );
                break;
                
             case 'costing_staff':
                $roles['Vendors'] = array(
                    'vendor_manager'=> $this->translate->_('Vendor Manager'),
                    'vendor_staff' => $this->translate->_('Vendor Staff')
                );
                break;
        }
        return $roles;
    }

}