<?php
class IndexController extends Zend_Controller_Action
{
    /**
     * @var integer
     */
    protected $activityCountMax = 20;
    /**
     * @var Amedia_Acl
     */
    protected $amediaAcl;
    /**
     * @var Zend_Auth
     */
    protected $amediaAuth;

    public function init()
    {
        $this->amediaAcl = $this->getRequest()->getParam('amediaAcl');
        $this->amediaAuth = $this->getRequest()->getParam('amediaAuth');
       

        /* Initialize action controller here */
        if ( ! Zend_Auth::getInstance()->hasIdentity())
            $this->_redirect('/login');

        $this->translate = Zend_Registry::get('translate');

        $this->view->jsCode .= "
            var _todayOffset = 10;
            var _yesterdayOffset = 10;
            var _moreOffset = 10;
            var _recentActivityIsLoaded = false;
            var _userActivityIsLoaded = false;
    	";
    }

    public function indexAction()
    {
        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/addcosting.js', $type = "text/javascript");

        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/addyields.js', $type = "text/javascript");

        $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/user.js', $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl('/assets/js/tiny_mce/jquery-ui-1.8.16.custom.min.js'), $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl('/assets/js/tiny_mce/jquery.tinymce.js'), $type = "text/javascript");
        $this->view->headScript()->appendFile($this->view->serverUrl('/assets/js/tiny_mce/tiny_mce.js'), $type = "text/javascript");

        $permissionsModel = new Amedia_Model_Privileges();
        $this->view->permissionsdata = $permissionsModel->find(Zend_Auth::getInstance()->getIdentity()->user_level);


        if($this->amediaAcl->isAllowed(Zend_Auth::getInstance()->getIdentity()->user_level, 'yield_manage', 'index')){
            $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/yields.js', $type = "text/javascript");
        }

        if($this->view->permissionsdata->department != "yields" && $this->view->permissionsdata->department != "costing" && $this->view->permissionsdata->department != "vendors") {
                $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/user_activity.js', $type = "text/javascript");
        }
        if($this->amediaAcl->isAllowed(Zend_Auth::getInstance()->getIdentity()->user_level, 'costing_manage', 'index')){
            $this->view->headScript()->appendFile($this->view->serverUrl() . '/assets/js/modules/costingChart.js', $type = "text/javascript");
        }
        /**
         * Append "static" Javascript Code
         */
        $this->view->jsCode .= "
            var _lang_default_error = '" . $this->translate->_('Error processing your request, please try again later.') . "';
            var _lang_enter_date = '" . $this->translate->_('Please select a date to Refine the Activity Summary.') . "';
            var _lang_select_user = '" . $this->translate->_('Please select the Refinement User.') . "';
            var _lang_remove = '" . $this->translate->_('Remove') . "';";

        /**
         * Get Role/User level from the current user
         */
        $userLevel = Zend_Auth::getInstance()->getIdentity()->user_level;
        /*
        * Hide charts for vendors
        */
        $amedia = new Amedia_Acl();
        $this->view->isVendor = $amedia->isVendor($userLevel);

        /**
         * Append Javasccript code that needs to be run on DOM Ready
         */
        $this->view->domReady .= "initDashboard();\n";


        /**
         * Filter Dashboard if Superadmin
         */
        $filterBy = 'all';
        $filteredDashboard = false;
        // if the user is a superadmin let them choose using the menu
        if ($userLevel == 'superadmin' )
        {
            // then allow the user to filter
            $filterBy = $this->_getParam('filterby', 'all');
            // Make sure there is "something" in the filterBy var and that the value
            // is either sales or yields
            $filteredDashboard = (in_array(strtolower($filterBy), array('sales', 'yields')));
        }
        $this->view->jsCode .= "
            var _filterBy = '$filterBy';";

        // disable right rail
        $this->view->mainRightRail = '';

        /**
         * Filter the Dashboard Box Stats (located on the right rail
         */
        $this->filterDashboardStats($userLevel, $filterBy, $filteredDashboard);

        // set the page title here
        $pageTitle = ($filterBy == 'all') ? $this->translate->_('Dashboard')
                : sprintf($this->translate->_("%1\$s Dashboard"), ucwords($filterBy));

        /**
         * Page CSS ID and Class
         */
        $this->view->pageID = 'dashboard';
        $this->view->pageClass = '';

        /**
         * Page Title
         */
        $this->view->pageTitle = $this->view->translate->_($pageTitle);
        $this->view->headTitle($this->view->pageTitle);



        /**
         * Get data for the Activity Summary Tab

        $stats = new Default_Model_Stats();
        $ActivityData = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company,"2009-12");
        $this->view->activitySummaryDataset = $ActivityData;
        $count_Activity_for_rol = array();
                var_dump($count_Activity_for_rol);        
         */
    }


    public function getjsonStatsForYearAction()
    {
        //date_default_timezone_set(Zend_Auth::getInstance()->getIdentity()->user_timezone); 
        $userDate = new Zend_Date();
        if (trim($this->view->userData->user_timezone) != '')
            $userDate->setTimezone($this->view->userData->user_timezone);

        $currentMonth = $userDate->toString('MM');

        $year = date ("Y");
        $filterBy = 'yields';
        $stats = new Default_Model_Stats();
        $months = array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AUG','SEP','OCT','NOV','DEC'); 

        foreach ($months as $key => $value) 
        {
            if ($key >= (int) $currentMonth) break; // only load until current month

            if(strlen($key) > 1 && $key < 9) 
            {
                $values = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company,$year.'-'.($key + 1));
                $values['month'] = $key + 1;
                $data[$value] = $values;
            } 
            else 
            {
                $values = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company,$year.'-0'.($key + 1));
                $values['month'] = '0'.($key + 1);
                $data[$value] = $values;
            }
        }

        $count_Activity_for_rol  =  array();
        foreach ($data as $key => $value) {
            foreach ($value as $key2 => $value2) {
                if(isset($value2['user_level'])){
                    if(is_numeric($value2['user_level'])) {
                        $privilege = new Amedia_Model_Privileges();
                        $privilege->find($value2['user_level']);
                        $data[$key][$key2] = $value2['user_level']  = $privilege->privileges_rol_name; 
                    }
                    if( isset($count_Activity_for_rol[$value2['user_level']] ) ) {
                        $count_Activity_for_rol[$value2['user_level']]++;
                    } else {
                        $count_Activity_for_rol[$value2['user_level']]  = 1 ;                    
                    }
                }
            }
                    $data[$key] = $count_Activity_for_rol;
        }
        echo $this->_helper->json(array("status" => "ok","description" => "anual",'message' => 'success', "data" => $data, 'currentYear' => date('Y')));                    
    }

    public function getUserActivityForDateAction(){        
        $date = $this->_getParam('param', 'today'); 
        
    switch ($date) {
//uso user activity cambiar por las funciones viejas
            case 'thismonth':
                $date = date ("Y-m");
                $stats = new Default_Model_Stats();
                $data = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company, $date);
                $message = 'this month '.$date;
            break;

            case 'lastmonth':
                $year = date ("Y");
                $month = date ("m");
                $date = $year."-".($month-1);
                $stats = new Default_Model_Stats();
                $data = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company, $date);
                $message = 'last month '.$date;
            break;            
            
            case 'thisyear':
                $year = date ("Y");
                $stats = new Default_Model_Stats();
                $data = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company, $year);
                $message = 'this year '.$year;
            break;


            default:
                $day = date ("Y-m-d");
                $stats = new Default_Model_Stats();
                $data = $stats->getUserActivityOfgroupforDate(Zend_Auth::getInstance()->getIdentity()->ID_company, $day);
                $message = 'today '.$day;
            break;
        }

        $count_Activity_for_rol  =  array();
        foreach ($data as $key => $value) {
            //    var_dump($data);
                if(is_numeric($value['user_level'])) {
                    $privilege = new Amedia_Model_Privileges();
                    $privilege->find($value['user_level']);
                    $value['user_level']  = $privilege->privileges_rol_name; 
                }

                if( isset($count_Activity_for_rol[$value['user_level']] ) ) {
                   // var_dump($value) ;
                    $count_Activity_for_rol[$value['user_level']]++;
                    
                } else {
                    $count_Activity_for_rol[$value['user_level']]  = 1 ;                    

                }


        }
        echo $this->_helper->json(array("status" => "ok", "data" => $count_Activity_for_rol, 'currentdate' => $date));                    
    }


    /**
     * Get Recent Activity JSON Call
     */
    public function getrecentactivityAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');
        $data['message'] = $this->view->translate->_('Error processing your request.');

        $filterBy = $this->_getParam('filterBy', 'all');

        // Fetch Activities
        $activity = new People_Model_Activity();
        $this->view->todayActivities = $activity->fetchTodayActivities(11, 0, $filterBy);
        $this->view->yesterdayActivities = $activity->fetchYesterdayActivities(11, 0, $filterBy);
        $data['status'] = 'success';
        $data['payload']['html'] = $this->view->render('partials/recentActivity.phtml');
        $json = json_encode($data);
        echo $json;
    }



     public function getChartDataAction(){ //get data for the pie charts for user Activity
        $refinementforDate = $this->_getParam('date', '');
        $stats = new Default_Model_Stats();   
        $result = $stats->getDataIn($refinementforDate);
        echo $this->_helper->json(array('status' => 'ok', 'data' =>$result));
    }
    public function refineMonth($month){
                if((int)$month < 9){
                    return '0'.($month+1) ;
                } else {
                    return (int)($month+1);
                }
    }
    public function getChartforYearFormatAction(){
        $months = array('JAN','FEB','MAR','APR','MAY','JUN','JUL','AGU'); //'FEB','MAR','APR','MAY' 
        $result = array();
        $year = date('Y');
        $stats = new Default_Model_Stats();       
        $levels = $stats->getprivilegesOfcompany(Zend_Auth::getInstance()->getIdentity()->ID_company);

        $final_aux = array();
        $final;  
        $final_aux[] = "Months";              

        foreach ($levels as $key => $value) { //set the user level in the group for head of chart

            if(is_numeric($value['user_level'])) {
                $privilege = new Amedia_Model_Privileges();
                $privilege->find($value['user_level']);
                $value['user_level']  = strtolower($privilege->privileges_rol_name ); 
            }            

            if(!in_array(strtolower(str_replace("_"," ",$value['user_level'])), $final_aux) ) {

                $final_aux[] = strtolower(str_replace("_"," ",$value['user_level']));
            }

        }
        $final[] = $final_aux;
        $ul = array();
        foreach ($months as $key => $value) {
                $result[$value] = $stats->getDataIn($year.'-'.$this->refineMonth(($key)));
           
        }   


        foreach ($months as $key => $value) {
                $final_aux = array();
                $final_aux[] =  $value; 
            foreach ($result[$value] as $user_level => $activity) {
                $final_aux[] = $activity;
     
            }
            $final[] = $final_aux;
           
        }


    echo $this->_helper->json(array('status' => 'ok', 'month'=> '' , 'data' =>$final));
}



    public function useractstatsListAction(){
        // PaginationuseractstatsListAction

        $page = $this->_getParam('page', 1);
        $refinementId = $this->_getParam('refinement', '');
        $statsFilter = $this->_getParam('stats_filter', 'no');
        $orderField = $this->_getParam('order_by', 'updated');
        $orderType = $this->_getParam('order_type', 'desc');
        $recordStatus = $this->_getParam('status', 'active');
        

        $page = $this->_getParam('page', 1);
        $userlevel = $this->_getParam('user_level', '');
        $refinementforDate = $this->_getParam('date', '');


        $Activity = array(); 
        $ListActivities = array();         
        $stats = new Default_Model_Stats();
        $ForRolName = new Amedia_Model_Privileges();
        $this->view->userlevel = $userlevel;
        //get Correct $user_level name 
        if(!is_numeric($userlevel)){
            $ForRolName = $ForRolName->fetchByRollname($userlevel);            
            if(is_numeric($ForRolName['id_privileges'])){
                $userlevel = $ForRolName['id_privileges'];
            }
        }

        if ($refinementforDate != '')
        {
            if( strtotime($refinementforDate) ) {   
                $users = new People_Model_UserAuth(); 
                $users = $users->fetchAllByUserLevel($userlevel);
                foreach ($users as $key => $value) {
                    $Activity[$value['ID_user']] = $stats->getStatsForUser($value['ID_user'], 'all' , $refinementforDate);
                    foreach ($Activity[$value['ID_user']] as $key => $value) {
                        if(isset($ListActivities[$key] ) ) {
                            $ListActivities[$key] += $value;
                        } else {
                            $ListActivities[$key] = $value;
                        }
                    }
                 }
                 /*for old data*/
                $users2 = new People_Model_UserAuth(); 
                $users2 = $users2->fetchAllByUserLevel($this->view->userlevel);

                foreach ($users2 as $key => $value) {
                    $Activity[$value['ID_user']] = $stats->getStatsForUser($value['ID_user'], 'all' , $refinementforDate);
                    foreach ($Activity[$value['ID_user']] as $key => $value) {
                        if(isset($ListActivities[$key] ) ) {
                            $ListActivities[$key] += $value;
                        } else {
                            $ListActivities[$key] = $value;
                        }
                    }
                 }


            }

        } else {
        
            $users = new People_Model_UserAuth();
            $users = $users->fetchAllByUserLevel($userlevel);
            foreach ($users as $key => $value) {

                $Activity[$value['ID_user']] = $stats->getStatsForUser($value['ID_user'], 'all' , '');

                foreach ($Activity[$value['ID_user']] as $key => $value) {
                    if(isset($ListActivities[$key] ) ) {
                        $ListActivities[$key] += $value;
                    } else {
                        $ListActivities[$key] = $value;
                    }
                }
             }
        }

        $this->view->ListActivities = $ListActivities;

        $this->view->pageTitle = sprintf($this->translate->_("User Activity "));
        $this->view->headTitle($this->view->pageTitle);

        $output = $this->view->render('index/stats-list-user-activity.phtml');
        echo $this->_helper->json(array('status' => 'ok', 'content' => $output));
    }

    /**
     * Get Activities JSON Call
     */
    public function getactivitiesAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');

        $from = $this->_request->getParam('from', '');
        $offset = $this->_request->getParam('offset', 0);
        $filterBy = $this->_getParam('filterBy', 'all');
        
        $data['status'] = 'error';
        $data['message'] = $this->view->translate->_('Error processing your request.');
        if ($from != '')
        {
            $activity = new People_Model_Activity();
            
            if ($from == 'today')
                $activities = $activity->fetchTodayActivities(11, $offset + 10, $filterBy);
            
            elseif ($from == 'yesterday')
                $activities = $activity->fetchYesterdayActivities(11, $offset + 10, $filterBy);

            else
                $activities = $activity->fetchMoreActivities(11, $offset + 10, $filterBy);

            $data['status'] = 'success';
            $data['message'] = '';
            $data['payload']['recordCount'] = count($activities) - 1;
            $data['payload']['records'] = '';
            $i = 0;
            $total = count($activities);
            foreach($activities as $activity)
            {
                $i++;
                if ($i == 11) break;
                $classFirst = ($i == 1) ? ' first' : ' ';
                $classLast = '';
                if ($i == 10)
                {
                    $classLast.= ' last';
                    $classLast.= ($total > 10) ? ' has_more' : ' ';
                }
                $data['payload']['records'] .= $this->view->activity($activity, $classFirst . $classLast);
            }
        }
        $json = json_encode($data);
        echo $json;
    }

    /**
     * Get Activity Summary JSON Call
     */
    public function getactivitysummaryAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');

        $this->view->amediaAcl = $this->amediaAcl;

        $userData = Zend_Auth::getInstance()->getIdentity();
        if (trim($userData->user_timezone) == '')
            $userData->user_timezone = 'America/Los_Angeles';
        if (trim($userData->user_locale) == '')
            $userData->user_locale = 'en_US';

        $selectedDate = new Zend_Date();
        $selectedDate->setTimezone($userData->user_timezone);
        $selectedDate->setLocale($userData->user_locale);
        $splittedDate = explode('-', $this->_getParam('refineDate'));
        $selectedDate->set($splittedDate[0], Zend_Date::YEAR);
        $selectedDate->set($splittedDate[1], Zend_Date::MONTH);
        $selectedDate->set($splittedDate[2], Zend_Date::DAY);

        $filterBy = $this->_getParam('filterBy', 'all');

        $stats = new Default_Model_Stats();
        $this->view->activityRefineDate = $selectedDate->toString('YYYYMMdd');
        $this->view->activitySummaryType = 'bydate';
        $this->view->activitySummaryDataset = $stats->getStatsForDate($selectedDate->toString('YYYY-MM-dd'), $filterBy);

        $data['message'] = '';
        $data['status'] = 'success';
        $data['payload']['selectedDate'] = sprintf($this->translate->_("For %1\$s"), $selectedDate->get(Zend_Date::DATE_LONG));
        $data['payload']['html'] = $this->view->render('partials/activitySummary.phtml');
        $json = json_encode($data);
        echo $json;
    }

    /**
     * Get User Activity JSON Call
     */
    public function getuseractivityAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');
        $data['message'] = '';

        $this->view->amediaAcl = $this->amediaAcl;

        $selectedUser = Zend_Auth::getInstance()->getIdentity()->ID_user;
        $this->view->selectedUser = $selectedUser;
        $this->view->selectedUserName = Zend_Auth::getInstance()->getIdentity()->user_firstname
                . ' ' . Zend_Auth::getInstance()->getIdentity()->user_surname;
        $this->view->activitySummaryType = 'byuser';

        $filterBy = $this->_getParam('filterBy', 'all');
        
        $stats = new Default_Model_Stats();
        $this->view->activitySummaryDataset = $stats->getStatsForUser($selectedUser, $filterBy);

        $user = new People_Model_User();
        $this->view->activeUsers = $user->fetchAllFilteredByGroupAndRole();

        $data['status'] = 'success';
        $data['payload']['html'] = $this->view->render('partials/userActivity.phtml');
        $json = json_encode($data);
        echo $json;
    }

    /**
     * Get Refined User Activity JSON Call
     */
    public function getuseractivityrefinedAction()
    {
        $this->_helper->viewRenderer->setNoRender(true);
        $this->_helper->layout()->disableLayout();
        header('Content-Type: application/json');

        $this->view->amediaAcl = $this->amediaAcl;

        $selectedUser = $this->_getParam('selectedUser');
        $this->view->selectedUser = $selectedUser;
        $this->view->activitySummaryType = 'byuser';

        $filterBy = $this->_getParam('filterBy', 'all');

        $stats = new Default_Model_Stats();
        $this->view->activitySummaryDataset = $stats->getStatsForUser($selectedUser, $filterBy);

        $data['message'] = '';
        $data['status'] = 'success';
        $data['payload']['html'] = $this->view->render('partials/activitySummary.phtml');
        $json = json_encode($data);
        echo $json;
    }

    /**
     * Filter the Dashboard Box Stats based on User level and Superadmin filter choice
     *
     * @param string $userLevel
     * @param string $filterBy
     * @param boolean $filteredDashboard
     * @return void
     */
    private function filterDashboardStats($userLevel, $filterBy, $filteredDashboard)
    {
        /**
         * Declare Stats Boxes
         */
        $statBoxes = array();

        /**
         * If current user is superadmin AND we need to filter the dashboard
         */
        if (($userLevel == 'superadmin') && $filteredDashboard)
        {
            /**
             * Superadmin wants to see the yields dashboard
             */
            if ($filterBy == 'yields')
            {
                /**
                 * Yields Stats Box
                 */
                if ($this->amediaAcl->isAllowed($userLevel, 'yield_manage', 'stats'))
                    $statBoxes[] = array(
                            'module' => 'yield',
                            'controller' => 'manage',
                            'method' => 'stats'
                    );

                /**
                 * Yields - Pending Yields Box
                 */
                if ($this->amediaAcl->isAllowed($userLevel, 'yield_manage', 'pending'))
                    $statBoxes[] = array(
                            'module' => 'yield',
                            'controller' => 'manage',
                            'method' => 'pending'
                    );
            }

            /**
             * Superadmin wants to see the sales dashboard
             */
            if ($filterBy == 'sales')
            {
                /**
                 * Sales Kit Stats Box
                 */
                if ($this->amediaAcl->isAllowed($userLevel, 'saleskit_manage', 'stats'))
                    $statBoxes[] = array(
                            'module' => 'saleskit',
                            'controller' => 'manage',
                            'method' => 'stats'
                    );
            }

            /**
             * Discussions Stats Box
             */
            if ($this->amediaAcl->isAllowed($userLevel, 'discussion_manage', 'stats'))
                $statBoxes[] = array(
                        'module' => 'discussion',
                        'controller' => 'manage',
                        'method' => 'stats'
                );
        }
        else
        {
            /**
             * We don't need to filter the dashboard, so we add the "calls" to
             * the stats method on each module
             */

            /**
             * Sales Kit Stats Box
             */
            if ($this->amediaAcl->isAllowed($userLevel, 'saleskit_manage', 'stats'))
                $statBoxes[] = array(
                        'module' => 'saleskit',
                        'controller' => 'manage',
                        'method' => 'stats'
                );

            /**
             * Yields Stats Box
             */
            if ($this->amediaAcl->isAllowed($userLevel, 'yield_manage', 'stats'))
                $statBoxes[] = array(
                        'module' => 'yield',
                        'controller' => 'manage',
                        'method' => 'stats'
                );

            /**
             * Yields - Pending Yields Box
             */
            if ($this->amediaAcl->isAllowed($userLevel, 'yield_manage', 'pending'))
                $statBoxes[] = array(
                        'module' => 'yield',
                        'controller' => 'manage',
                        'method' => 'pending'
                );

            /**
             * Discussions Stats Box
             */
            if ($this->amediaAcl->isAllowed($userLevel, 'discussion_manage', 'stats'))
                $statBoxes[] = array(
                        'module' => 'discussion',
                        'controller' => 'manage',
                        'method' => 'stats'
                );
        }

        /**
         * Make sure we have stats we need to display
         */
        if (count($statBoxes) > 0)
        {
            /**
             * Call each "stats" method on every module
             */
            foreach($statBoxes as $moduleName => $statBox)
                $this->_helper->actionStack(
                        $statBox['method'],
                        $statBox['controller'],
                        $statBox['module'],
                        array('filterBy' => $filterBy));
        }
    }
}