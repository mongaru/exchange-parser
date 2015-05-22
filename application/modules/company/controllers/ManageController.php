<?php
class Company_ManageController extends Zend_Controller_Action
{

    public function init()
    {
        /* Initialize action controller here */
        $this->translate = Zend_Registry::get('translate');
        $this->view->pageID = 'people';

        $this->_db = Zend_Db_Table::getDefaultAdapter();

       // $this->view->mainRightRail .= " "; // to display main rail

        $this->view->moduleName = $this->getRequest()->getModuleName();
        $this->view->controllerName = $this->getRequest()->getControllerName();
        $this->view->actionName = $this->getRequest()->getActionName();

    }

    public function indexAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl('/assets/js/modules/company.js?' . KK_APP_VERSION), $type = "text/javascript");

        $this->view->pageTitle = $this->translate->_('Groups');
        $company = new Company_Model_Company();

        // Pagination
        $page = is_numeric($pn = $this->_getParam('page', 1)) ? $this->_getParam('page', 1) : 1;
        $OrderBy = ($this->_getParam('order_by', 'name') == 'name') ? 'company_name' : 'company_type';
       
        $OrderType = 'desc';
        $OrderType = ($this->_getParam('order_type', 'asc')=='desc')?'desc':'asc';
        $UserStatus = ($this->_getParam('status', 'active')=='inactive')?'inactive':'active';
        $OrderBy = $OrderBy . ' ' . $OrderType;

        $where = '';
        if ($UserStatus == 'active')
            $where = "company_is_deleted = 'no'";
        else
            $where = "company_is_deleted = 'yes'";


        // here is where the magic happens
        $where.= "or company_type = 'disabled vendor'";
        $paginator = Zend_Paginator::factory($company->fetchAll($where, $OrderBy));
        $paginator->setItemCountPerPage(60);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(3);

        $this->view->current_order_by = '$OrderField';
        $this->view->current_order_type = $OrderType;
        $this->view->current_user_status = $UserStatus;
        $this->view->current_page = $page;
//        $this->view->mainRightRail .= $this->view->render('whoIsOnline.phtml');
        $this->view->headTitle($this->view->pageTitle);
        $this->view->paginator = $paginator;
    }


    public function loadAction(){
        $company = new Company_Model_Company();
        $ID = $this->_getParam('id', 0);        
        $company->find($ID);

        echo $this->_helper->json(array("status" => "ok", "data" => $company->toArray()));

        return;
    }

    private function _changeStatus($deleted)
    {
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $cl = new Company_Model_Company();
        $cl->find($ID_company);
        if (!$cl->ID_company)
            return $this->_redirect('/company/manage');
        if($cl->company_type=='vendor'){
            $cl->company_type = "disabled vendor";            
        }


        $cl->company_is_deleted = $deleted;
//        echo "<pre>"; var_dump($cl); echo "</pre>"; die();
        $cl->save();

        // Log Activity
        $ID_user = Zend_Auth::getInstance()->getIdentity()->ID_user;
        $action = ($deleted == 'yes')? 'trash':'restore';
        $activity = People_Model_Activity::createNowActivity($ID_user, $ID_company, 'company', $action, 'success');
        $activity->save();
        return $this->_redirect('/company/manage');
    }

    function deleteAction()
    {
        $this->_changeStatus("yes");
    }

    public function saveCompanyAction()
    {
        // Instantiate Company Add form
        $formCompanyAdd = new Company_Form_CompanyAdd();
        
        // Gets Request Vars
        $request = $this->getRequest();

        if (($request->isPost()) && ($formCompanyAdd->isValid($request->getPost())))
        {
            $form_ = $formCompanyAdd->getValues();
            $company = new Company_Model_Company($formCompanyAdd->getValues());
            $company->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $company->setcompany_description($form_['company_description']);
            $company->setCompany_is_deleted('no');
            $company->setCompany_master('no');
            $ID_company = $company->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_company);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('company');
            $activity->setActivity_type(($form_['ID_company'] == '')? 'add' : 'edit');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

             $this->_helper->json(array('status' => 'ok', 'message' => 'Company information saved. Page will be refreshed.'));
        }
        else
        {
            $this->_helper->json(array('status' => 'error', 'message' => 'Error while saving company.', 'errors' => $formCompanyAdd->getMessages() ));
        }    
    }

    public function viewAction()
    {
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0) {
            return $this->_redirect('/company/manage');
        } else{
         return $this->_redirect('/people/user/index/groups/'.$ID_company );
 
        }
/*

        $company = new Company_Model_Company();
        $company->find($ID_company);
        if (!$company->ID_company)
            return $this->_redirect('/company/manage');

        $this->view->companyInfo = $company;

        $userActionsDisplay = array(
            'showAdd'          => true,
            'showEdit'         => true,
            'showChangePass'   => true,
            'showTrash'        => true,
            'showChangeAvatar' => true,
            'showSetMaster'    => true
        );
        $this->view->pageTitle = $company->getcompany_name();
        $this->view->headTitle($company->getcompany_name());

        if ($company->company_master == 'yes')
        {
            $this->view->pageTitle .= ' '.$this->translate->_('(Owner Company)');
            $userActionsDisplay['showSetMaster'] = false;
        }

        $this->view->assign($userActionsDisplay);
        $this->view->mainRightRail .= $this->view->render('CompanyActions.phtml');

        $this->view->listPeople = $this->_listPeopleForCompany($ID_company);
*/
    }

    private function _listPeopleForCompany($ID_company)
    {
        $user = new People_Model_User();

        // Pagination
        $page = $this->_getParam('page', 1);
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

        $where .= " AND company_user.ID_company = '$ID_company'";

        // here is where the magic happens
        $paginator = Zend_Paginator::factory($user->fetchAllCompany($where, $OrderBy));
        $paginator->setItemCountPerPage(10);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(3);

        $this->view->current_order_by = $OrderField;
        $this->view->current_order_type = $OrderType;
        $this->view->current_user_status = $UserStatus;
        $this->view->current_page = $page;

        $this->view->headTitle($this->view->pageTitle);
        $this->view->paginator = $paginator;

        $this->view->url_suffix = 'id/'.$ID_company;

        $this->view->addScriptPath(APPLICATION_PATH . "/modules/people/views/scripts/");
        return $this->view->render('user/index.phtml');

    }

    public function addAction()
    {

        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/extensions/company.add.fx.js', $type = "text/javascript");
        $this->view->domReady = "reset_forms();\n";
        $this->view->domReady.= "init_add_company_form('" . Zend_Auth::getInstance()->getIdentity()->user_level . "');";
        $this->view->pageTitle = $this->translate->_('Add Company');
        $this->view->headTitle($this->translate->_('Add Company'));
        $company = new Company_Model_Company();
        // Instantiate Company Add form
        $formCompanyAdd = new Company_Form_CompanyAdd();
        $types = $company->getTypes();
        $formCompanyAdd->getElement('company_type')->addMultiOptions($types);
        $this->view->form = $formCompanyAdd;
        $this->view->types = $types;
        // Gets Request Vars
        $request = $this->getRequest();

        if (($request->isPost()) && ($formCompanyAdd->isValid($request->getPost())))
        {
            $company = new Company_Model_Company($formCompanyAdd->getValues());
            $company->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $company->setCompany_is_deleted('no');
            $company->setCompany_master('no');
            $ID_company = $company->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_company);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('company');
            $activity->setActivity_type('add');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();
            return $this->_redirect('/company/manage/view/id/' . $ID_company);
        }
    }

    public function editAction()
    {
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $company = new Company_Model_Company();
        $company->find($ID_company);
        if (!$company->ID_company)
            return $this->_redirect('/company/manage');


        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/extensions/company.add.fx.js', $type = "text/javascript");
        $this->view->domReady = "reset_forms();\n";
        $this->view->domReady.= "init_add_company_form('" . Zend_Auth::getInstance()->getIdentity()->user_level . "');";

        $this->view->pageTitle = $this->translate->_('Edit Company');
        $this->view->headTitle($this->translate->_('Edit Company'));

        // Instantiate Company Add form
        $formCompanyAdd = new Company_Form_CompanyAdd();
        $types = $company->getTypes();
        $formCompanyAdd->getElement('company_type')->addMultiOptions($types);
        $this->view->form = $formCompanyAdd;
        $this->view->types = $types;

         // Gets Request Vars
        $request = $this->getRequest();

        if (($request->isPost()) && ($formCompanyAdd->isValid($request->getPost())))
        {
            $company->setOptions($formCompanyAdd->getValues());
            $company->save();

            // Log Activity
            $activity = new People_Model_Activity();
            $activity->setID_object($ID_company);
            $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
            $activity->setActivity_object_type('company');
            $activity->setActivity_type('edit');
            $activity->setActivity_result('success');
            $activity->setActivity_date(date('Y-m-d H:i:s'));
            $activity->save();

            return $this->_redirect('/company/manage/view/id/' . $ID_company);

        }
        else
        {
            $values = $company->exportToArray();
            $formCompanyAdd->populate($values);
        }

        $this->view->mainRightRail .= $this->view->render('CompanyActions.phtml');
        $this->_helper->viewRenderer('manage/add', null, true);

    }


    public function trashAction()
    {
        $disable = $this->_getParam('disable', 'false');        
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $company = new Company_Model_Company();
        $company->find($ID_company);
        if (!$company->ID_company)
            return $this->_redirect('/company/manage');


        if ($disable == 'true' && $company->company_type == 'vendor')
        {
            $company->company_type = "disabled vendor";            
        }

        $company->setCompany_is_deleted('yes');
        $company->save();

        // Log Activity
        $activity = new People_Model_Activity();
        $activity->setID_object($ID_company);
        $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
        $activity->setActivity_object_type('company');
        $activity->setActivity_type('trash');
        $activity->setActivity_result('success');
        $activity->setActivity_date(date('Y-m-d H:i:s'));
        $activity->save();


        $this->_trashUsers($ID_company);

        return $this->_redirect('/company/manage');
    }

    private function _trashUsers($ID_company)
    {
		$this->_db->getConnection()
        ->exec("UPDATE user JOIN company_user on company_user.ID_user = user.ID_user SET user.user_is_deleted = 'yes'
        WHERE company_user.ID_company = ".$ID_company);
	}


    private function _restoreUsers($ID_company)
    {
		$this->_db->getConnection()
        ->exec("UPDATE user JOIN company_user on company_user.ID_user = user.ID_user SET user.user_is_deleted = 'no'
        WHERE company_user.ID_company = ".$ID_company);
	}


    public function restoreAction()
    {
        $disable = $this->_getParam('disable', 'false');                
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $company = new Company_Model_Company();
        $company->find($ID_company);
        if (!$company->ID_company)
            return $this->_redirect('/company/manage');


       if ($disable == 'true' && $company->getcompany_type() == "disabled vendor")
        {
            $company->company_type = "vendor";            
        }

        $company->setCompany_is_deleted("no");
//        echo "<pre>"; var_dump($company); echo "</pre>"; die();
        $company->save();

        // Log Activity
        $activity = new People_Model_Activity();
        $activity->setID_object($ID_company);
        $activity->setID_user(Zend_Auth::getInstance()->getIdentity()->ID_user);
        $activity->setActivity_object_type('company');
        $activity->setActivity_type('restore');
        $activity->setActivity_result('success');
        $activity->setActivity_date(date('Y-m-d H:i:s'));
        $activity->save();


        $this->_restoreUsers($ID_company);

        return $this->_redirect('/company/manage');
    }


    public function setasmasterAction()
    {
        $ID_company = $this->_getParam('id', 0);
        if ($ID_company <= 0)
            return $this->_redirect('/company/manage');

        $company = new Company_Model_Company();
        $company->find($ID_company);
        if ( ! $company->ID_company)
            return $this->_redirect('/company/manage');

        $this->_db->getConnection()
            ->exec("UPDATE company SET company.company_master = 'no'");

        $this->_db->getConnection()
            ->exec("UPDATE company SET company.company_master = 'yes' WHERE company.ID_company = " . $ID_company);

        $fileCache = Zend_Registry::get('fileCache');
        $fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('company'));

        return $this->_redirect('/company/manage/view/id/' . $ID_company);
    }


}
