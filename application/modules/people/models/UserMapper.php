<?php

// application/modules/people/models/UserMapper.php

class People_Model_UserMapper
{

    protected $_dbTable;
    protected $_fileCache;

    public function __construct()
    {
        $this->_fileCache = Zend_Registry::get('fileCache');
    }

    public function setDbTable($dbTable)
    {
        if (is_string($dbTable))
        {
            $dbTable = new $dbTable();
        }
        if (!$dbTable instanceof Zend_Db_Table_Abstract)
        {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    /**
     * 
     * @return People_Model_DbTable_User
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('People_Model_DbTable_User');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_User $user)
    {
        $data = $user->toArray();
        $data['user_name'] = strtolower($user->user_firstname) . '_' . strtolower($user->user_surname); // set the value for the user_name field

        if (!isset($data['ID_user']) || $data['ID_user'] == '' || $data['ID_user'] == '0' || $data['ID_user'] == 0)
        {
            unset($data['ID_user']);
            $this->getDbTable()->insert($data);
        }
        else
        {
            $this->getDbTable()->update($data, array('ID_user = ?' => $data['ID_user']));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array('user', 'companyUsers'));
    }

    public function find($ID_user, People_Model_User $user)
    {
        $result = $this->getDbTable()->find($ID_user);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $user_auth = $row->findDependentRowset('People_Model_DbTable_UserAuth')->current();
        $user_optional = $row->findDependentRowset('People_Model_DbTable_UserOptional')->current();
        $user->setID_user($row->ID_user)
            ->setuser_name($row->user_name)
            ->setuser_firstname($row->user_firstname)
            ->setuser_middlename($row->user_middlename)
            ->setuser_surname($row->user_surname)
            ->setuser_email($row->user_email)
            ->setuser_timezone($row->user_timezone)
            ->setuser_locale($row->user_locale)
            ->setuser_is_deleted($row->user_is_deleted)
            ->setuser_permanently_deleted($row->user_permanently_deleted)
            ->setuser_auth($user_auth)
            ->setuser_optional($user_optional)
            ->setMapper($this);
        return $user;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {

            $select = $this->getDbTable()->select()
                ->from('user')
                ->join('company_user', 'user.ID_user = company_user.ID_user')
                ->joinLeft('user_auth', 'user_auth.ID_user = user.ID_user')
                ->where($where)
                ->order($order)
                ->limit($count, $offset)
                ->setIntegrityCheck(false);
            $resultSet = $this->getDbTable()->fetchAll($select, $order, $count, $offset);
            $users = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user_auth = $row->findDependentRowset('People_Model_DbTable_UserAuth')->current();
                $user_optional = $row->findDependentRowset('People_Model_DbTable_UserOptional')->current();
                $user->setID_user($row->ID_user)
                    ->setuser_name($row->user_name)
                    ->setuser_firstname($row->user_firstname)
                    ->setuser_middlename($row->user_middlename)
                    ->setuser_surname($row->user_surname)
                    ->setuser_email($row->user_email)
                    ->setuser_timezone($row->user_timezone)
                    ->setuser_locale($row->user_locale)
                    ->setuser_auth($user_auth)
                    ->setuser_optional($user_optional)
                    ->setMapper($this);
                $users[] = $user;
            }
            //$this->_fileCache->save($users, $cacheId, array('user'));
            return $users;
}


    public function fetchAllCompany($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'CompanyUsersFetchAll_' . sha1((string) $where . (string) $order . (string) $count . (string) $offset);
        if (!$cache = $this->_fileCache->load($cacheId))
        {
            $select = $this->getDbTable()->select()
                ->from('user')
                ->joinLeft('user_auth', 'user_auth.ID_user = user.ID_user')
                ->join('company_user', 'company_user.ID_user = user.ID_user')
                ->where($where)
                ->order($order)
                ->limit($count, $offset)
                ->setIntegrityCheck(false);
            $resultSet = $this->getDbTable()->fetchAll($select, $order, $count, $offset);
            $users = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user_auth = $row->findDependentRowset('People_Model_DbTable_UserAuth')->current();
                $user_optional = $row->findDependentRowset('People_Model_DbTable_UserOptional')->current();
                $user->setID_user($row->ID_user)
                    ->setuser_name($row->user_name)
                    ->setuser_firstname($row->user_firstname)
                    ->setuser_middlename($row->user_middlename)
                    ->setuser_surname($row->user_surname)
                    ->setuser_email($row->user_email)
                    ->setuser_timezone($row->user_timezone)
                    ->setuser_locale($row->user_locale)
                    ->setuser_auth($user_auth)
                    ->setuser_optional($user_optional)
                    ->setMapper($this);
                $users[] = $user;
            }

            $this->_fileCache->save($users, $cacheId, array('companyUsers'));
            return $users;
        }
        else
            return $cache;
    }

    public function fetchLastLoggedUsers()
    {
        $select = $this->getDbTable()->getAdapter()->select()
            ->from('user')
            ->joinLeft('user_auth', 'user.ID_user = user_auth.ID_user')
            ->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user')
            ->where("user_auth.user_last_login <> ?", "''")
            ->where("user_auth.user_last_login > ?", date('Y-m-d H:i:s', strtotime('-15 minutes')))
            ->order("");
        $stmt = $this->getDbTable()->getAdapter()->query($select);
        $resultSet = $stmt->fetchAll();
        $users = array();
        foreach ($resultSet as $row)
        {
            $user = new People_Model_User();
            $user->find($row['ID_user']);
            $users[] = $user;
        }
        return $users;
    }

    public function fetchNotifyList()
    {

        $users = array();
        $orderFields = array("CONCAT(user_firstname, ' ', user_surname) ASC");
            // Get My Company Members
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", Zend_Auth::getInstance()->getIdentity()->ID_company)
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $colleagues = array();

            $privileges = new Amedia_Model_Privileges();
            $allprivileges = $privileges->fetchAll();        
            $privilegname = array();
                                         
            foreach ($allprivileges as $key => $value) {
                   $privileg = array();

                   foreach ($resultSet as  $row) {
                        if($row['user_level'] == $value->id_privileges || $row['user_level'] == $value->privileges_rol_name ){
                            $userModel = new People_Model_User();
                            $privileg[] =  $userModel->find($row['ID_user']);
                        }
                    }

                    if(!empty($privileg)){
                        $users[$value->id_privileges] = $privileg;
                        $privilegname[] = $value->privileges_rol_name;
                     }

            }
            $users['privileges_name'] = $privilegname;

            return $users;
    }

    public function fetchDiscussionNotificationList()
    {
        $users = array();
        $orderFields = array("CONCAT(user_firstname, ' ', user_surname) ASC");
        $currentUserLevel = Zend_Auth::getInstance()->getIdentity()->user_level;
        $currentUserCompanyID = Zend_Auth::getInstance()->getIdentity()->ID_company;
        $currentUserCompanyName = Zend_Auth::getInstance()->getIdentity()->company_name;
        $currentUserIsAdmin = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'admins');
        $currentUserIsClient = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'clients');
        $currentUserIsVendor = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'vendors');
        $currentUserIsCostingStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'costing');
        $currentUserIsSalesStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'sales');
        $currentUserIsYieldsStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'yields');

        // Get Company Master
        $companyMasterID = 0;
        $company = new Company_Model_Company();
        $company = $company->fetchAll("company_master = 'yes' AND company_is_deleted = 'no'");
        if (count($company) > 0)
            $companyMasterID = $company[0]->ID_company;
        
        /**
         * Administrators
         */
        if ($currentUserIsAdmin)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
            
            // Get all other companies
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company <> ?", $companyMasterID)
                ->order(array("company_name ASC, CONCAT(user_firstname, ' ', user_surname) ASC"));
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $users[$row['ID_company']]['group_label'] = $row['company_name'];
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users[$row['ID_company']]['users'][] = $user;
            }
        }
        
        /**
         * Sales Staff
         */
        if ($currentUserIsSalesStaff)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->where("user_department = 'Sales'")
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
            
            // Get all other companies
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->joinLeft('company', 'vw_users_active.ID_company = company.ID_company')
                ->where("vw_users_active.ID_company <> ?", $companyMasterID)
                ->where("company_type = 'client'")
                ->order(array("vw_users_active.company_name ASC, CONCAT(user_firstname, ' ', user_surname) ASC"));
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $users[$row['ID_company']]['group_label'] = $row['company_name'];
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users[$row['ID_company']]['users'][] = $user;
            }
        }
        
        /**
         * Costing Staff
         */
        if ($currentUserIsCostingStaff)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->where("user_department = 'Costing'")
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
            
            // Get all other companies
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->joinLeft('company', 'vw_users_active.ID_company = company.ID_company')
                ->where("vw_users_active.ID_company <> ?", $companyMasterID)
                ->where("company_type = 'vendor'")
                ->order(array("vw_users_active.company_name ASC, CONCAT(user_firstname, ' ', user_surname) ASC"));
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $users[$row['ID_company']]['group_label'] = $row['company_name'];
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users[$row['ID_company']]['users'][] = $user;
            }
        }
        
        /**
         * Yields Staff
         */
        if ($currentUserIsYieldsStaff)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->where("user_department = 'Yields'")
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
        }
        
        /**
         * Vendors
         */
        if ($currentUserIsVendor)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->where("user_department = 'Costing'")
                ->order($orderFields);
            
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
            
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $currentUserCompanyID)
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['my_company'] = array(
                'group_label' => $currentUserCompanyName,
                'users' => $users_temp
                );
        }
        
        /**
         * Clients
         */
        if ($currentUserIsClient)
        {
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $companyMasterID)
                ->where("user_department = 'Sales'")
                ->order($orderFields);
            
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['kandy_kiss'] = array(
                'group_label' => 'Kandy Kiss',
                'users' => $users_temp
                );
            
            // Get all Kandy Kiss
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('vw_users_active')
                ->where("ID_company = ?", $currentUserCompanyID)
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $users_temp = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users_temp[] = $user;
            }
            $users['my_company'] = array(
                'group_label' => $currentUserCompanyName,
                'users' => $users_temp
                );
        }
        
        return $users;
    }

    public function fetchAllByDeparment($department)
    {
        $cacheId = 'UserFetchAllDepartment_' . strtolower($department);
        if (!$cache = $this->_fileCache->load($cacheId))
        {
            $users = array();
            $orderFields = array('user.user_firstname asc', 'user.user_surname asc');

            $select = $this->getDbTable()->getAdapter()->select()
                ->from('user')
                ->joinLeft('user_auth', 'user.ID_user = user_auth.ID_user')
                ->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user')
                ->where("user.user_is_deleted = ?", "no")
                ->where("user.user_permanently_deleted = ?", "no")
                ->where("LOWER(user_optional.user_department) = ?", strtolower($department))
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            $admins = array();
            foreach ($resultSet as $row)
            {
                $user = new People_Model_User();
                $user->find($row['ID_user']);
                $users[] = $user;
            }

            $this->_fileCache->save($users, $cacheId, array('user'));
            return $users;
        }
        else
            return $cache;
    }

    public function fetchAllByClient()
    {
        $cacheId = 'UserFetchAllByClient';
        if (!$cache = $this->_fileCache->load($cacheId))
        {
            $orderFields = array('company.company_name asc', 'user.user_firstname asc', 'user.user_surname asc');

            $select = $this->getDbTable()->getAdapter()->select()
                ->from('user', array('ID_user', 'user_firstname', 'user_surname'))
                ->joinLeft('user_auth', 'user.ID_user = user_auth.ID_user', array())
                ->joinLeft('user_optional', 'user.ID_user = user_optional.ID_user', array())
                ->joinLeft('company_user', 'company_user.ID_user = user.ID_user', array())
                ->joinLeft('company', 'company.ID_company = company_user.ID_company', array('ID_company', 'company_name'))
                ->where("user.user_is_deleted = ?", "no")
                ->where("user.user_permanently_deleted = ?", "no")
                ->where('company.company_master = ?', 'no')
                ->where("LOWER(user_optional.user_department) = ?", 'clients')
                ->order($orderFields);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();

            $results = array();
            foreach ($resultSet as $row)
            {
                $results[$row['ID_company']][] = $row;
            }
            $this->_fileCache->save($results, $cacheId, array('user'));
            return $results;
        }
        else
            return $cache;
    }

    /**
     *
     * @param integer $ID_user
     * @return <type>
     */
    public function getUserCompany($ID_user)
    {
        $select = $this->getDbTable()->select()
            ->from('company_user', array())
            ->joinLeft('company', 'company_user.ID_company = company.ID_company')
            ->where('company_user.ID_user = ' . $ID_user)
            ->setIntegrityCheck(false);
        $results = $select->query();
        return $results->fetch();
    }

    /**
     * Returns all users filtered by group and role/user level
     *
     * @param string|null $group
     * @param string|null $role
     * @return array
     */
    public function fetchAllFilteredByGroupAndRole($group, $role)
    {
        $group = strtolower($group);
        $role = strtolower($role);
        $select = $this->getDbTable()->getAdapter()->select()
            ->from('vw_users_active', array('ID_user', new Zend_Db_Expr("CONCAT(user_firstname, ' ', user_surname) AS full_name")));

        if ($group == 'clients')
        {
            if ($role == 'client_company_manager')
                $select->where('ID_company = ?', Zend_Auth::getInstance()->getIdentity()->ID_company);
            else
                $select->where('ID_user = ?', Zend_Auth::getInstance()->getIdentity()->ID_user);
        }

        if ($group == 'yields')
        {
            if ($role == 'yield_admin')
                $select->where('LOWER(user_department) = ?', $group);
            else
                $select->where('ID_user = ?', Zend_Auth::getInstance()->getIdentity()->ID_user);
        }

        if ($group == 'sales')
        {
            if ($role == 'sales_admin')
                $select->where('LOWER(user_department) IN (?)', array('sales', 'clients'));

            if ($role == 'sales_manager')
                $select->where('LOWER(user_level) IN (?)', array('sales_staff', 'client_company_manager', 'client_company_member'));

            if ($role == 'sales_staff')
                $select->where('ID_user = ?', Zend_Auth::getInstance()->getIdentity()->ID_user);
        }

        $select->order(array('user_firstname asc', 'user_surname asc'));

        return $select->query()->fetchAll();
    }

}