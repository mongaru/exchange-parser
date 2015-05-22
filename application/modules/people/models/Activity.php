<?php
// application/modules/people/model/Activity.php
class People_Model_Activity
{
    protected $_ID_activity;
    protected $_ID_object;
    protected $_ID_user;
    protected $_activity_object_type;
    protected $_activity_type;
    protected $_activity_result;
    protected $_activity_date;

    /**
     * DB Object
     *
     * @var Zend_Db_Adapter_Pdo_Mysql
     */
    protected $_db;

    /**
     * @var People_Model_ActivityMapper|null
     */
    protected $_mapper;
    protected $_fileCache;

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

    public function __construct(array $options = null)
    {
        $db_config = Zend_Registry::get('db_config');
        $this->db = Zend_Db::factory($db_config['db']['adapter'], $db_config['db']['params']);
        Zend_Db_Table::setDefaultAdapter($this->db);

        if (is_array($options))
        {
            $this->setOptions($options);
        }

        $this->_fileCache = Zend_Registry::get('fileCache');
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

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid activity property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid activity property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value)
        {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods))
            {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setDb($db)
    {
        $this->_db = $db;
        return $this;
    }

    public function getDb()
    {
        return $this->_db;
    }

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     *
     * @return People_Model_ActivityMapper
     */
    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new People_Model_ActivityMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function delete()
    {
        $this->getMapper()->delete($this->_ID_activity);
    }

    public function find($ID_subscriber)
    {
        $this->getMapper()->find($ID_subscriber, $this);
        return $this;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }


    public function fetchTodayActivities($count, $offset, $filterBy)
    {
        $this->_filterBy = $filterBy;
        $moduleQueries = $this->getActivitiesFrom('today');
        $activities = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $activities->union($moduleQueries)
                ->order('activity_date DESC')
                ->limit($count, $offset);
        $resultSet = $activities->query();
        $records = array();
        foreach($resultSet as $row)
        {
            $activity = new People_Model_Activity($row);
            $records[] = $activity;
        }
        return $records;
    }

    public function fetchYesterdayActivities($count, $offset, $filterBy)
    {
        $this->_filterBy = $filterBy;
        $moduleQueries = $this->getActivitiesFrom('yesterday');
        $activities = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $activities->union($moduleQueries)
                ->order('activity_date DESC')
                ->limit($count, $offset);
        $resultSet = $activities->query();
        $records = array();
        foreach($resultSet as $row)
        {
            $activity = new People_Model_Activity($row);
            $records[] = $activity;
        }
        return $records;
    }

    public function fetchMoreActivities($count, $offset, $filterBy)
    {
        $this->_filterBy = $filterBy;
        $moduleQueries = $this->getActivitiesFrom('earlier');
        $activities = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $activities->union($moduleQueries)
                ->order('activity_date DESC')
                ->limit($count, $offset);
        $resultSet = $activities->query();
        $records = array();
        foreach($resultSet as $row)
        {
            $activity = new People_Model_Activity($row);
            $records[] = $activity;
        }
        return $records;
    }

    public function fetchActivitiesIn($count, $offset,$Indate)
    {

        $moduleQueries = $this->getActivitiesFrom($Indate);
        $activities = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
        $activities->union($moduleQueries)
                ->order('activity_date DESC')
                ->limit($count, $offset);
        $resultSet = $activities->query();
        $records = array();
        foreach($resultSet as $row)
        {
            $activity = new People_Model_Activity($row);
            $records[] = $activity;
        }
        return $records;
    }    

    public function getID_activity()
    {
        return $this->_ID_activity;
    }

    public function setID_activity($_ID_activity)
    {
        $this->_ID_activity = $_ID_activity;
        return $this;
    }

    public function getID_object()
    {
        return $this->_ID_object;
    }

    public function setID_object($_ID_object)
    {
        $this->_ID_object = $_ID_object;
        return $this;
    }

    public function getID_user()
    {
        return $this->_ID_user;
    }

    public function setID_user($_ID_user)
    {
        $this->_ID_user = $_ID_user;
        return $this;
    }

    public function getActivity_object_type()
    {
        return $this->_activity_object_type;
    }

    public function setActivity_object_type($_activity_object_type)
    {
        $this->_activity_object_type = $_activity_object_type;
        return $this;
    }

    public function getActivity_type()
    {
        return $this->_activity_type;
    }

    public function setActivity_type($_activity_type)
    {
        $this->_activity_type = $_activity_type;
        return $this;
    }

    public function getActivity_result()
    {
        return $this->_activity_result;
    }

    public function setActivity_result($_activity_result)
    {
        $this->_activity_result = $_activity_result;
        return $this;
    }

    public function getActivity_date()
    {
        return $this->_activity_date;
    }

    public function setActivity_date($_activity_date)
    {
        $this->_activity_date = $_activity_date;
        return $this;
    }
    
    public static function createNowActivity($ID_user, $ID_object, $object_type, $type, $result) {

        $activity = new People_Model_Activity();
        $activity->setID_object($ID_object);
        $activity->setID_user($ID_user);
        $activity->setActivity_object_type($object_type);
        $activity->setActivity_type($type);
        $activity->setActivity_result($result);
        $activity->setActivity_date(date('Y-m-d H:i:s'));
        
        return $activity;
    }

    /**
     * Returns an array with all of the queries needed for the Recent Activity
     *
     * @param string $date
     * @return array
     */
    private function getActivitiesFrom($date)
    {
        /**
         * Array for each Module's query
         */
        $moduleQueries = array();

        /**
         * 
         */

        if(strtotime($date) ){
                $date  = "(activity_date  like '%$date%')";            
        }

        switch($date)
        {
            case 'today':
                $date = "CAST(CONVERT_TZ(activity_date, '+00:00', '$this->_userTimezoneOffset') AS date) = '$this->_todayDate'";
                break;

            case 'yesterday':
                $date = "CAST(CONVERT_TZ(activity_date, '+00:00', '$this->_userTimezoneOffset') AS date) = '$this->_yesterdayDate'";
                break;

            case 'earlier';
                $date = "CAST(CONVERT_TZ(activity_date, '+00:00', '$this->_userTimezoneOffset') AS date) <= CAST(DATE_SUB(CONVERT_TZ(CURRENT_DATE, '+00:00', '$this->_userTimezoneOffset'), INTERVAL 2 DAY) AS date)";
                break;

            default;
                $date = "CAST(CONVERT_TZ(activity_date, '+00:00', '$this->_userTimezoneOffset') AS date) = '$this->_todayDate'";
        }

        /**
         * Include Yields
         */
        if (($this->_filterBy == 'all') || ($this->_filterBy == 'yields'))
        {
            /**
             * Yields
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'yield_manage', 'index'))
            {
                $yields = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
                $yields->from('activity')
                        ->joinLeft('yield', 'activity.ID_object = yield.ID_yield', array())
                        ->where($date)
                        ->where('activity_object_type = ?', 'yield');
                if ($this->_userData->user_level == 'pattern_maker')
                    $yields->where("yield.ID_user = ? OR ID_object IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'yield' AND ID_user = ?)", $this->_userData->ID_user);
                $moduleQueries[] = $yields;
            }
        }

        /**
         * Include Sales Kit
         */
        if (($this->_filterBy == 'all') || ($this->_filterBy == 'sales'))
        {
            /**
             * Sales Kit
             */
            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'saleskit_manage', 'index'))
            {
                $saleskits = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
                $saleskits->from('activity')
                        ->joinLeft('sales_kit', 'activity.ID_object = sales_kit.ID_sales_kit', array())
                        ->where($date)
                        ->where('activity_object_type = ?', 'saleskit');
                if (strtolower($this->_userData->user_department) == 'clients')
                {
                    if ($this->_userData->user_level == 'client_company_manager')
                        $saleskits->where("sales_kit.ID_sales_kit IN (SELECT DISTINCT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON sales_kit_subscriber.ID_user = company_user.ID_user WHERE company_user.ID_company = ?)", $this->_userData->ID_company);
                    else
                        $saleskits->where("sales_kit.ID_sales_kit IN (SELECT DISTINCT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON sales_kit_subscriber.ID_user = ?)", $this->_userData->ID_user);
                }
                $moduleQueries[] = $saleskits;
            }
        }

        if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'discussion_manage', 'index'))
        {
            /**
             * Discussions
             */
            $discussions = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
            $discussions->from('activity')
                    ->joinLeft('discussion', 'activity.ID_object = discussion.ID_discussion', array())
                    ->where($date)
                    ->where('activity_object_type = ?', 'discussion');
            /**
             * Discussion Comments
             */
            $commentDiscussions = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
            $commentDiscussions->from('activity')
                    ->joinLeft('comment', 'activity.ID_object = comment.ID_comment', array())
                    ->joinLeft('discussion', 'discussion.ID_discussion = comment.ID_discussion', array())
                    ->where($date)
                    ->where('activity_object_type = ?', 'comment');

            /**
             * Filter Discussions and Discussion Comments based on superadmin choice
             */
            if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
            {
                /**
                 * Current User is Superadmin and wants the discussions filtered by Department
                 * We don't use discussion_visibility here since user is superadmin and can see both
                 */
                if ($this->_filterBy == 'yields')
                {
                    $discussions->where("(ID_yield > 0) OR (discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                    $commentDiscussions->where("(ID_yield > 0) OR (discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                }

                if ($this->_filterBy == 'sales')
                {
                    $discussions->where("discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                    $commentDiscussions->where("discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                }
            }
            else
            {
                switch(strtolower($this->_userData->user_department))
                {
                    case 'yields':
                        if ($this->_userData->user_level == 'yield_admin')
                        {
                            /**
                             * User is Yield Admin, he can see normal AND private discussions
                             */
                            $discussions->where("(ID_yield > 0) OR (Idiscussion.D_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                            $commentDiscussions->where("(ID_yield > 0) OR (discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'yield' AND child_object_type = 'discussion'))");
                        }
                        elseif ($this->_userData->user_level == 'pattern_maker')
                        {
                            /**
                             * User is Pattern Maker, he can see only HIS discussions
                             * and discussions where he's subscribed to
                             */
                            $discussions->where('ID_user = ?', $this->_userData->ID_user)
                                    ->orWhere("discussion.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)", $this->_userData->ID_user);
                            $commentDiscussions->where('ID_user = ?', $this->_userData->ID_user)
                                    ->orWhere("discussion.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)", $this->_userData->ID_user);
                        }
                        else
                        {
                            /**
                             * User is from Yields department, he can see normal AND private discussion BUT only if he's subscribed to the private discussion
                             */
                            $discussions->where("(discussion_visibility = 'normal' OR (discussion_visibility = 'private' AND discussion.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)))", $this->_userData->ID_user);
                            $commentDiscussions->where("(discussion_visibility = 'normal' OR (discussion_visibility = 'private' AND discussion.ID_discussion IN (SELECT ID_object FROM subscriber WHERE subscriber_object_type = 'discussion' AND ID_user = ?)))", $this->_userData->ID_user);
                        }
                        break;

                    case 'sales':
                    /**
                     * Filter discussions attached to sales kits
                     */
                        $discussions->where("discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                        $commentDiscussions->where("discussion.ID_discussion IN (SELECT DISTINCT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion')");
                        break;

                    case 'clients':
                    /**
                     * Filter discussions that belongs to the user company only
                     */
                        if ($this->_userData->user_level == 'client_company_manager')
                        {
                            $discussions->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (discussion.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON company_user.ID_user = sales_kit_subscriber.ID_user WHERE company_user.ID_company = ?))))", $this->_userData->ID_company);
                            $commentDiscussions->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (discussion.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber LEFT JOIN company_user ON company_user.ID_user = sales_kit_subscriber.ID_user WHERE company_user.ID_company = ?))))", $this->_userData->ID_company);
                        }
                        else
                        {
                            $discussions->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (discussion.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber WHERE ID_user = ?))))", $this->_userData->ID_user);
                            $commentDiscussions->where("((discussion_visibility = 'normal' OR discussion_visibility = 'private') AND (discussion.ID_discussion IN (SELECT child_ID_object FROM relationship WHERE parent_object_type = 'saleskit' AND child_object_type = 'discussion' AND parent_ID_object IN (SELECT ID_sales_kit FROM sales_kit_subscriber WHERE ID_user = ?))))", $this->_userData->ID_user);
                        }
                        break;

                    case 'admins':
                    /**
                     * Admins can see everything
                     * As of August 2010, only superadmin belongs to this group
                     */
                        $discussions->where("(discussion_visibility = 'normal' OR discussion_visibility = 'private')");
                        $commentDiscussions->where("(discussion_visibility = 'normal' OR discussion_visibility = 'private')");
                        break;
                }
            }
            $moduleQueries[] = $discussions;
            $moduleQueries[] = $commentDiscussions;

            if ($this->_amediaAcl->isAllowed($this->_userData->user_level, 'company_manage', 'view'))
            {
                /**
                 * Users (includes all user actions
                 */
                $activity = new Zend_Db_Select(Zend_Db_Table::getDefaultAdapter());
                $activity->from('activity')
                        ->joinLeft('user', 'activity.ID_object = user.ID_user', array())
                        ->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user', array())
                        ->where($date)
                        ->where('activity_object_type = ?', 'user');
                /**
                 * Filter Users based on superadmin choice
                 */
                if (($this->_userData->user_level == 'superadmin') && ($this->_filterBy != 'all'))
                {
                    /**
                     * Current User is Superadmin and wants the users filtered by Department
                     */
                    $activity->where('LOWER(user_department) = ?', $this->_filterBy);
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
                    $activity->where('activity.ID_object IN (?)', $filterUsers);
                }
                $moduleQueries[] = $activity;
            }

            return $moduleQueries;
        }
    }
}
