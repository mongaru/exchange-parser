<?php
/**
 * Default_Model_Stats
 */
class Default_Model_Stats
{
    protected $_fileCache;

    /**
     * DB Object
     *
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * User Auth Data
     *
     * @var object
     */
    protected $_userData;

    /**
     * User Current Time
     * @var Zend_Date
     */
    protected $_userTime;

    /**
     * Amedia ACL List
     *
     * @var Amedia_Acl
     */
    protected $_amediaAcl;

    /**
     * Today date based on user timezone
     * @var string
     */
    protected $_todayDate;

    /**
     * Yesterday date based on user timezone
     *
     * @var string
     */
    protected $_yesterdayDate;

    /**
     * Timezone offset from current user
     *
     * @var string
     */
    protected $_userTimezoneOffset;

    /**
     * Filter By Department, value used if current user is superadmin
     *
     * @var string;
     */
    protected $_filterBy;

    public function __construct()
    {
        $this->_fileCache = Zend_Registry::get('fileCache');        
        $this->_db = Zend_Registry::get('db');
        $this->_userData = Zend_Auth::getInstance()->getIdentity();
        $this->_userTime = new Zend_Date();
        $this->_amediaAcl = Zend_Registry::get('amediaAcl');
        if (trim($this->_userData->user_timezone) == '')
            $this->_userData->user_timezone = 'America/Los Angeles';
        $this->_userTime->setTimezone($this->_userData->user_timezone);
        $this->_todayDate = $this->_userTime->toString('YYYY-MM-dd');
        $this->_yesterdayDate = $this->_userTime->sub(1, Zend_Date::DAY)->toString('YYYY-MM-dd');
        $this->_userTimezoneOffset = $this->_userTime->get(Zend_Date::GMT_DIFF_SEP);
    }

    /**
     * Get Today and Yesterday Stats
     *
     * @return array
     */
    public function getTodayAndYesterdayStats($filterBy = 'all')
    {
        $stats = array();
        $this->_filterBy = $filterBy;

        /**
         * Sales Kit Stats
         */
        if (($this->_filterBy == 'all') || ($this->_filterBy == 'sales'))
        {
            /**
             * Get Sales Kit stats if filter is all or sales
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'saleskit_manage', 'index'))
            {
                /**
                 * Sales Kits Posted
                 */
                $stats['saleskitPostedToday'] = $this->countSaleskitPosted($this->_todayDate);
                $stats['saleskitPostedYesterday'] = $this->countSaleskitPosted($this->_yesterdayDate);
                /**
                 *  Sales Kit Viewed
                 */
                $stats['saleskitViewedToday'] = $this->countSaleskitViewedOn($this->_todayDate);
                $stats['saleskitViewedYesterday'] = $this->countSaleskitViewedOn($this->_yesterdayDate);
            }
        }

        /**
         * Yields Stats
         */
        if (($this->_filterBy == 'all') || ($this->_filterBy == 'yields'))
        {
            /**
             * Get Yields stats if filter is all or yields
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'yield_manage', 'index'))
            {
                /**
                 * Yields Posted
                 */
                $stats['yieldsPostedToday'] = $this->countYieldsPosted($this->_todayDate);
                $stats['yieldsPostedYesterday'] = $this->countYieldsPosted($this->_yesterdayDate);

                /**
                 * Yields Completed
                 */
                $stats['yieldsCompletedToday'] = $this->countYieldsCompleted($this->_todayDate);
                $stats['yieldsCompletedYesterday'] = $this->countYieldsCompleted($this->_yesterdayDate);
            }
        }

        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'discussion_manage', 'index'))
        {
            /**
             * Discussions Posted
             */
            $stats['discussionsPostedToday'] = $this->countDiscussionsPosted($this->_todayDate);
            $stats['discussionsPostedYesterday'] = $this->countDiscussionsPosted($this->_yesterdayDate);

            /**
             * Discussions Commented On
             */
            $stats['discussionsCommentedToday'] = $this->countDiscussionsCommentedOn($this->_todayDate);
            $stats['discussionsCommentedYesterday'] = $this->countDiscussionsCommentedOn($this->_yesterdayDate);

            /**
             * Discussion Comments
             */
            $stats['commentsPostedToday'] = $this->countCommentsPosted($this->_todayDate);
            $stats['commentsPostedYesterday'] = $this->countCommentsPosted($this->_yesterdayDate);
        }


        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'company_manage', 'view'))
        {
            /**
             * Users
             */
            $stats['usersAddedToday'] = $this->countUsersAdded($this->_todayDate);
            $stats['usersAddedYesterday'] = $this->countUsersAdded($this->_yesterdayDate);

            /**
             * Get Login Counts
             */
            $stats['loginsToday'] = $this->countLogins($this->_todayDate, $this->_filterBy);
            $stats['loginsYesterday'] = $this->countLogins($this->_yesterdayDate);
        }

        return $stats;
    }

    /**
     * Get Stats for a Selected Date
     *
     * @param string $date
     * @return array
     */
    public function getStatsForDate($date, $filterBy)
    {
        $stats = array();
        $this->_filterBy = $filterBy;

        if (($this->_filterBy == 'all') || ($this->_filterBy == 'sales'))
        {
            /**
             * Sales Kits
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'saleskit_manage', 'index'))
            {
                // Sales Kits Posted Today
                $stats['saleskitPosted'] = $this->countSaleskitPosted($date);

                // Sales Kits Viewed Today
                $stats['saleskitViewed'] = $this->countSaleskitViewedOn($date);

            }
        }

        if (($this->_filterBy == 'all') || ($this->_filterBy == 'yields'))
        {
            /**
             * Yields Stats
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'yield_manage', 'index'))
            {
                // Yields Posted Today

                $stats['yieldsPosted'] = $this->countYieldsPosted($date);

                // Yields Completed Today
                $stats['yieldsCompleted'] = $this->countYieldsCompleted($date);
                //Yields Pendings 
                $stats['yieldsPending'] = $this->countYieldsPending($date);
                //Yields Archived
                $stats['yieldsArchived'] = $this->countYieldsArchived($date);                
            }
        }

        /**
         * Discussion Stats
         */
        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'discussion_manage', 'index'))
        {
            // Discussions Posted Today
            $stats['discussionsPosted'] = $this->countDiscussionsPosted($date);

            // Discussions Commented On Today
            $stats['discussionsCommented'] = $this->countDiscussionsCommentedOn($date);

            // Comments Posted Today
            $stats['commentsPosted'] = $this->countCommentsPosted($date);
        }

        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'company_manage', 'view'))
        {
            // Users added Today
            $stats['usersAdded'] = $this->countUsersAdded($date);

            // Logins Today
            $stats['logins'] = $this->countLogins($date);
        }

        return $stats;
    }

    /**
     * Get Today and Yesterday Stats for User
     *
     * @param integer $ID_user
     * @param string $filterBy
     * @return array
     */
    public function getStatsForUser($ID_user, $filterBy,$date)
    {
        $stats = array();
        $this->_filterBy = strtolower($filterBy);

        if (($this->_filterBy == 'all') || ($this->_filterBy == 'sales'))
        {
            /**
             * Sales Kits
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'saleskit_manage', 'index'))
            {
                // Sales Kits Posted

                $stats['saleskit.Posted'] = $this->countSaleskitPosted($date, $ID_user);
  //              $stats['saleskitPostedYesterday'] = $this->countSaleskitPosted($this->_yesterdayDate, $ID_user);

                // Sales Kit Viewed
                $stats['saleskit.Viewed'] = $this->countSaleskitViewedOn($date, $ID_user);
  //              $stats['saleskitViewedYesterday'] = $this->countSaleskitViewedOn($this->_yesterdayDate, $ID_user);
            }
        }

        if (($this->_filterBy == 'all') || ($this->_filterBy == 'Yields'))
        {
            /**
             * yields Stats
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'yield_manage', 'index'))
            {
                // Yields Posted
                $stats['yields.Posted'] = $this->countYieldsPosted($date, $ID_user);
//                $stats['yieldsPostedYesterday'] = $this->countYieldsPosted($this->_yesterdayDate, $ID_user);

                // Yields Completed On
                $stats['yields.Completed'] = $this->countYieldsCompleted($date, $ID_user);
//                $stats['yieldsCompletedYesterday'] = $this->countYieldsCompleted($this->_yesterdayDate, $ID_user);
            }
        }

        /**
         * Discussion Stats
         */
        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'discussion_manage', 'index'))
        {
            // Discussions Posted
            $stats['discussions.Posted'] = $this->countDiscussionsPosted($date, $ID_user);
 //           $stats['discussionsPostedYesterday'] = $this->countDiscussionsPosted($this->_yesterdayDate, $ID_user);

            // Discussions Commented On
            $stats['discussions.Commented'] = $this->countDiscussionsCommentedOn($date, $ID_user);
     //       $stats['discussionsCommentedYesterday'] = $this->countDiscussionsCommentedOn($this->_yesterdayDate, $ID_user);

            // Discussion Comments
            $stats['comments.Posted'] = $this->countCommentsPosted($date, $ID_user);
     //       $stats['commentsPostedYesterday'] = $this->countCommentsPosted($this->_yesterdayDate, $ID_user);
        }


        /**
         * USer Stats
         */
        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'people_user', 'index'))
        {
            // Discussions Posted
        //    $stats['user.Modifications'] = $this->countUsedModif($date, $ID_user);
 //           $stats['discussionsPostedYesterday'] = $this->countDiscussionsPosted($this->_yesterdayDate, $ID_user);
        }




        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'company_manage', 'view'))
        {

            // Logins
            $stats['logins'] = $this->countLogins($date, $ID_user);
 //           $stats['loginsYesterday'] = $this->countLogins($this->_yesterdayDate, $ID_user);
        }

        return $stats;
    }





    /**************************************************************************
     *
     * Generate Query Methods
     *
     *************************************************************************/

    /**
     * Returns the Sales Kit query count
     *
     * @return Zend_Db_Select
     */
    private function getCountSalesKitQuery()
    {
        $select = $this->_db->select()
                ->from('sales_kit', array('COUNT(*) AS counter'))
                ->where('sales_kit_is_deleted = ?', 'no')
                ->where('sales_kit_is_archived = ?', 'no');

        if (strtolower($this->_userData->user_department) == 'clients')
        {
            /*
             * Filter discussions that belongs to the user company only
            */
            if ($this->_userData->user_level == 'client_company_manager')
                $select->where("ID_sales_kit IN (SELECT DISTINCT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON company_user.ID_user = sales_kit_subscriber.ID_user WHERE ID_company = ?)", $this->_userData->ID_company);
            else
                $select->where("ID_sales_kit IN (SELECT ID_sales_kit FROM sales_kit_subscriber WHERE ID_user = ?)", $this->_userData->ID_user);
        }
        return $select;
    }

    /**
     * Returns the Yields query count
     *
     * @return Zend_Db_Select
     */
    private function getCountYieldQuery($findarchived = false)
    {
        $lookinarchived = 'no';
        if($findarchived){
            $lookinarchived = 'yes';
        }

        $select = $this->_db->select()
                ->from('yield', array('COUNT(*) AS counter'))
                ->where('yield_is_deleted = ?', 'no')
                ->where('yield_is_archived = ?', $lookinarchived);


        switch(strtolower($this->_userData->user_department))
        {
            case 'yields':
                if ($this->_userData->user_level == 'pattern_maker')
                /**
                 * If Current user is pattern maker, only show his yields and
                 * yields he's subscribed to
                 */
                    $select->where("ID_user = ? OR ID_yield IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'yield' AND ID_user = ?)", $this->_userData->ID_user);
                break;
        }
        return $select;
    }

    /**
     * Returns the Discussions query count
     *
     * @return Zend_Db_Select
     */
    private function getCountDiscussionQuery()
    {
        $select = $this->_db->select()
                ->from('discussion', array('COUNT(*) AS counter'))
                ->where('discussion_is_deleted = ?', 'no')
                ->where('discussion_is_archived = ?', 'no');
        /**
         * Filter Discussions based on superadmin choice
         */
        if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
        {
            /**
             * Current User is Superadmin and wants the discussions filtered by Department
             * We don't use discussion_visibility here since user is superadmin and can see both
             */
            if ($this->_filterBy == 'yields')
                $select->where("(ID_yield > 0) OR (ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");

            if ($this->_filterBy == 'sales')
                $select->where("ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
        }
        else
        {
            switch(strtolower($this->_userData->user_department))
            {
                case 'yields':
                    if ($this->_userData->user_level == 'yield_admin')
                    /**
                     * User is Yield Admin, he can see normal AND private discussions
                     */
                        $select->where("(ID_yield > 0) OR (ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                    elseif ($this->_userData->user_level == 'pattern_maker')
                    /**
                     * User is Pattern Maker, he can see only HIS discussions
                     * and discussions where he's subscribed to
                     */
                        $select->where('ID_user = ?', $this->_userData->ID_user)
                                ->orWhere("ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)", $this->_userData->ID_user);
                    else
                    /**
                     * User is from Yields department, he can see normal AND private discussion BUT only if he's subscribed to the private discussion
                     */
                        $select->where("(discussion_visibility = 'normal' OR (discussion_visibility = 'private' AND ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)))", $this->_userData->ID_user);
                    break;

                case 'sales':
                /**
                 * Filter discussions attached to sales kits
                 */
                    $select->where("ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                    break;

                case 'clients':
                /**
                 * Filter discussions that belongs to the user company only
                 */
                    if ($this->_userData->user_level == 'client_company_manager')
                        $select->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON company_user.ID_user = sales_kit_subscriber.ID_user WHERE company_user.ID_company = ?))))", $this->_userData->ID_company);
                    else
                        $select->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber WHERE ID_user = ?))))", $this->_userData->ID_user);
                    break;

                case 'admins':
                /**
                 * Admins can see everything
                 * As of August 2010, only superadmin belongs to this group
                 */
                    $select->where("(discussion_visibility = 'normal' OR discussion_visibility = 'private')");
                    break;
            }
        }

        return $select;
    }



    private function getStatsInMonthFor($month,$user_level){
                $select = $this->_db->select()
                ->from('activity',array('activity_object_type'))
                ->join('user_auth', 'user_auth.ID_user = activity.ID_user', array('user_level'))
                ->joinLeft('company_user', 'company_user.ID_user = activity.ID_user', NULL)
                ->where('company_user.ID_company = ?', $ID_group)
                ->where("(activity.activity_date  like '%$date%')");
                return $select->query()->fetchAll();
    }


    /**
     * Returns the Comments query count
     *
     * @return Zend_Db_Select
     */
    private function getCountCommentQuery()
    {
        $select = $this->_db->select()
                ->from('comment', array('COUNT(*) AS counter'))
                ->joinLeft('discussion', 'comment.ID_discussion = discussion.ID_discussion', array())
                ->where('comment_is_deleted = ?', 'no');
        /**
         * Filter Comments based on superadmin choice
         */
        if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
        {
            /**
             * Current User is Superadmin and wants the comments filtered by Department
             * We don't use discussion_visibility here since user is superadmin and can see both
             */
            if ($this->_filterBy == 'yields')
                $select->where("comment.ID_discussion IN (SELECT discussion.ID_discussion FROM discussion WHERE (ID_yield > 0) OR (ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion')))");

            if ($this->_filterBy == 'sales')
                $select->where("comment.ID_discussion IN (SELECT discussion.ID_discussion FROM discussion WHERE discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion'))");
        }
        else
        {
            switch(strtolower($this->_userData->user_department))
            {
                case 'yields':
                    if ($this->_userData->user_level == 'yield_admin')
                    /**
                     * User is Yield Admin, he can see normal AND private discussions
                     */
                        $select->where("(ID_yield > 0) OR (comment.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                    elseif ($this->_userData->user_level == 'pattern_maker')
                    /**
                     * User is Pattern Maker, he can see only HIS discussions
                     * and discussions where he's subscribed to
                     */
                        $select->where("comment.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)", $this->_userData->ID_user);
                    else
                    /**
                     * User is from Yields department, he can see normal AND private discussion BUT only if he's subscribed to the private discussion
                     */
                        $select->where("(discussion_visibility = 'normal' OR (discussion_visibility = 'private' AND comment.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)))", $this->_userData->ID_user);
                    break;

                case 'sales':
                /**
                 * Filter discussions attached to sales kits
                 */
                    $select->where("comment.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                    break;

                case 'clients':
                /**
                 * Filter discussions that belongs to the user company only
                 */
                    if ($this->_userData->user_level == 'client_company_manager')
                        $select->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (comment.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON company_user.ID_user = sales_kit_subscriber.ID_user WHERE company_user.ID_company = ?))))", $this->_userData->ID_company);
                    else
                        $select->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (comment.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber WHERE ID_user = ?))))", $this->_userData->ID_user);
                    break;
                case 'admins':
                /**
                 * Admins can see everything
                 * As of August 2010, only superadmin belongs to this group
                 */
                    $select->where("(discussion_visibility = 'normal' OR discussion_visibility = 'private')");
                    break;
            }
        }
        return $select;
    }

    public function refineMonth($month){
                if((int)$month < 9){
                    return '0'.($month+1) ;
                } else {
                    return (int)($month+1);
                }
    }

    public function getDataIn($refinementforDate){
        $actualdate  = date("Y-m");
        $today  = date("Y-m-d");        

        if($refinementforDate == $actualdate ||$refinementforDate == $today ) {

            $levels = $this->getprivilegesOfcompany(Zend_Auth::getInstance()->getIdentity()->ID_company);

            $result = array();
            foreach ($levels as $key => $value) {

                if(!isset($result[ucwords(str_replace("_"," ",$value['user_level']))])) {
                    $name_level = ucwords(str_replace("_"," ",$value['user_level']));   
                    $ForRolName = new Amedia_Model_Privileges();
                    //get Correct $user_level name 
                    if(is_numeric($value['user_level'])){
                        $ForRolName = $ForRolName->find($value['user_level']);            
                        $name_level = ucwords(str_replace("_"," ", $ForRolName->privileges_rol_name));
                    }
                    $result[$name_level] = $this->useracts($name_level,$refinementforDate);                
                }
            }

            return $result;

        } else {
            $cachePreffix = 'UserActivityIn'.str_replace('-','',$refinementforDate);
            $cacheSuffix = str_replace('-','',$refinementforDate);
            $cacheId = $cachePreffix . sha1($cacheSuffix);
             if ( ! $cache = $this->_fileCache->load($cacheId)){
                $levels = $this->getprivilegesOfcompany(Zend_Auth::getInstance()->getIdentity()->ID_company);
                    //   var_dump($levels) ; die();
                $result = array();
                foreach ($levels as $key => $value) {

                    if(!isset($result[$value['user_level']])) {
                        $name_level = strtolower($value['user_level']);   
                        $ForRolName = new Amedia_Model_Privileges();
   
                        //get Correct $user_level name 
                        if(is_numeric($value['user_level'])){
                            $ForRolName = $ForRolName->find($value['user_level']);            
                            $name_level = strtolower($ForRolName->privileges_rol_name);
                        }
                        $result[$name_level] = $this->useracts($name_level,$refinementforDate);                
                    }
                }
                $this->_fileCache->save($result, $cacheId, array('user_activity'.str_replace('-','',$refinementforDate)));        
                return $result;

            } else {
                return $cache;
            }
        } 

    }



    public function useracts($userlevel,$refinementforDate = null){

        $Activity = array(); 
        $ListActivities = array();         
        $stats = new Default_Model_Stats();
        $ForRolName = new Amedia_Model_Privileges();
        $Nameuserlevel = $userlevel;
        $total  = 0;        
        //get Correct $user_level name 
        if(!is_numeric($userlevel)){
            $ForRolName = $ForRolName->fetchByRollname($userlevel);            
            if(is_numeric($ForRolName['id_privileges'])){
                $userlevel = $ForRolName['id_privileges'];
            }
        }

        if ($refinementforDate != ''){

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
                $users2 = $users2->fetchAllByUserLevel($Nameuserlevel);

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
//                var_dump($Activity[$value['ID_user']]);

                foreach ($Activity[$value['ID_user']] as $key => $value) {
                    if(isset($ListActivities[$key] ) ) {
                        $ListActivities[$key] += $value;
                    } else {
                        $ListActivities[$key] = $value;
                    }
                }
             }
        }
                 //count data
                 foreach ($ListActivities as $key => $value) {
                    $total += $value;
                  }         
                  return $total;
    }

    public function getprivilegesOfcompany($ID_company){

        $select = $this->_db->select()
                ->from('user_auth',array('user_level'));
                //->join('company_user', 'user_auth.ID_user = company_user.ID_user')
                //->where('company_user.ID_company = ?', $ID_company);
                return $select->query()->fetchAll();
    }

    public function getUserActivityOfgroupforDate($ID_group,$date){
        $select = $this->_db->select()
                ->from('activity',array('activity_object_type'))
                ->join('user_auth', 'user_auth.ID_user = activity.ID_user', array('user_level'))
                ->joinLeft('company_user', 'company_user.ID_user = activity.ID_user', NULL)
                ->where('company_user.ID_company = ?', $ID_group)
                ->where("(activity.activity_date  like '$date%')");

                return $select->query()->fetchAll();


    }

    /**
     * Returns the Users query count
     *
     * @return Zend_Db_Select
     */
    private function getCountUserQuery()
    {
        $select = $this->_db->select()
                ->from('user', array('COUNT(*) AS counter'))
                ->joinLeft('user_auth', 'user.ID_user = user_auth.ID_user', NULL)
                ->where('user.user_is_deleted = ?', 'no');
        /**
         * Filter Users based on superadmin choice
         */
        if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
        {
            /**
             * Current User is Superadmin and wants the users filtered by Department
             */
            $select->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user', NULL);
            $select->where('LOWER(user_department) = ?', $this->_filterBy);
        }
        else
        {
            /**
             * We are going to cheat here, we can get a list of users based
             * on the current user role (meaning, the resulting list will be
             * filtered based on both user role AND group) so we can safely
             * assume that this user can only see the given users activities
             */
            $user = new People_Model_User();
            $users = $user->fetchAllFilteredByGroupAndRole();
            foreach($users as $user) $filterUsers[] = $user['ID_user'];

            /**
             * Now we filter the query using the users we got
             */
            $select->where('user.ID_user IN (?)', $filterUsers);
        }
        return $select;
    }

    /**
     * Returns the Logins query count
     *
     * @return Zend_Db_Select
     */
    private function getCountLoginQuery()
    {
        $select = $this->_db->select()
                ->from('activity', array('COUNT(*) AS counter'))
                ->joinLeft('user', 'activity.ID_user = user.ID_user', array())
                ->where('activity.activity_object_type = ?', 'user')
                ->where('activity.activity_type = ?', 'login')
                ->where('activity.activity_result = ?', 'success')
                ->where('user.user_is_deleted = ?', 'no');
        /**
         * Filter Logins based on superadmin choice
         */
        if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
        {
            /**
             * Current User is Superadmin and wants the logins filtered by Department
             */
            $select->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user', array());
            $select->where('LOWER(user_department) = ?', $this->_filterBy);
        }
        else
        {
            /**
             * We are going to cheat here, we can get a list of users based
             * on the current user role (meaning, the resulting list will be
             * filtered based on both user role AND group) so we can safely
             * assume that this user can only see the given users activities
             */
            $user = new People_Model_User();
            $users = $user->fetchAllFilteredByGroupAndRole();
            foreach($users as $user) $filterUsers[] = $user['ID_user'];

            /**
             * Now we filter the query using the users we got
             */
            $select->where('activity.ID_object IN (?)', $filterUsers);
        }
        return $select;
    }





    /**************************************************************************
     *
     * Actual Fetch Count Methods
     *
     *************************************************************************/

    /**
     * Count the number of sales kit posted on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countSaleskitPosted($date, $ID_user = null)
    {
        $select = $this->getCountSalesKitQuery()
                ->where("CAST(CONVERT_TZ(sales_kit_created, '+00:00', '$this->_userTimezoneOffset') AS date) like '%$date%'");
        if ( ! is_null($ID_user))
            $select->where('sales_kit_responsible = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }

    /**
     * Count the number of sales kit viewed on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countSaleskitViewedOn($date, $ID_user = null)
    {
        $select = $this->getCountSalesKitQuery()
                ->where("ID_sales_kit IN (SELECT DISTINCT ID_sales_kit FROM sales_kit_subscriber WHERE CAST(CONVERT_TZ(subscriber_viewed_on, '+00:00', '$this->_userTimezoneOffset') AS date) like '%$date%')");
        if ( ! is_null($ID_user))
            $select->where('sales_kit_responsible = ?', $ID_user);

     
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }

    /**
     * Count the number of yields posted on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countYieldsPosted($date, $ID_user = null)
    {
        $select = $this->getCountYieldQuery()
                ->where("(yield_created  like '%$date%')");
        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user); 
        //echo $select->__toString();
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }

    /**
     * Count the number of yields completed on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countYieldsCompleted($date, $ID_user = null)
    {
        $select = $this->getCountYieldQuery()
//                ->where("(yield_status_changed  like '%$date%')")
                ->where("(CONVERT_TZ(yield_status_changed, '+00:00', '$this->_userTimezoneOffset')  like '%$date%')")
                ->where('yield_status = ?', 1);

//    echo $select->__toString()."\n";           
        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }


    /**
     * Count the number of yields pending on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countYieldsPending($date, $ID_user = null)
    {
        $select = $this->getCountYieldQuery()
        //        ->where("(yield_created  like '%$date%')")
                ->where("(CONVERT_TZ(yield_created, '+00:00', '$this->_userTimezoneOffset')   like '%$date%' )")
                ->where('yield_status = ?', 0);
        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }

    /**
     * Count the number of yields archived on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countYieldsArchived($date, $ID_user = null)
    {
        $select = $this->getCountYieldQuery(true)
                ->where("(CONVERT_TZ(yield_archived_date, '+00:00', '$this->_userTimezoneOffset')  like '%$date%')");


        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user);

        $results = $select->query()->fetchAll();

        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }


    /**
     * Count the number of discussions posted on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countDiscussionsPosted($date, $ID_user = null)
    {
        $select = $this->getCountDiscussionQuery()
                ->where("CONVERT_TZ(discussion_created, '+00:00', '$this->_userTimezoneOffset') like '%$date%'");
        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }



    /**
     * Count the number of discussions commented on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countDiscussionsCommentedOn($date, $ID_user = null)
    {
        $select = $this->getCountDiscussionQuery()
                ->where('discussion_last_reply IS NOT NULL')
                ->where("CONVERT_TZ(discussion_last_reply, '+00:00', '$this->_userTimezoneOffset')  like '%$date%'");
        if ( ! is_null($ID_user))
            $select->where('ID_user = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }

    /**
     * Count the number of comments posted on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countCommentsPosted($date, $ID_user = null)
    {
        $select = $this->getCountCommentQuery()
                ->where("CONVERT_TZ(comment_posted, '+00:00', '$this->_userTimezoneOffset') like '%$date%'");
        if ( ! is_null($ID_user))
            $select->where('comment.ID_user = ?', $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }


    /**
     * Count the number of users added on a given date
     *
     * @param string $date
     * @return integer
     */
    private function countUsersAdded($date)
    {
        $select = $this->getCountUserQuery()
                ->where("CONVERT_TZ(user_auth.user_created, '+00:00', '$this->_userTimezoneOffset')  like '%$date%'");
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }


    /**
     * Count the number of logins on a given date
     *
     * @param string $date
     * @param integer|null $ID_user
     * @return integer
     */
    private function countLogins($date, $ID_user = null)
    {
        $select = $this->getCountLoginQuery()
                ->where("CONVERT_TZ(activity.activity_date, '+00:00', '$this->_userTimezoneOffset')  like '%$date%'");
        if ( ! is_null($ID_user))
            $select->where("activity.ID_object = ?", $ID_user);
        $results = $select->query()->fetchAll();
        return (isset($results[0]['counter']) ? $results[0]['counter'] : 0);
    }
}