<?php
// application/modules/people/models/UserOptionalMapper.php

class People_Model_UserOptionalMapper
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
        if ( ! $dbTable instanceof Zend_Db_Table_Abstract)
        {
            throw new Exception('Invalid table data gateway provided');
        }
        $this->_dbTable = $dbTable;
        return $this;
    }

    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('People_Model_DbTable_UserOptional');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_UserOptional $user)
    {
        $data = array(
                'ID_optional'   => $user->getID_optional(),
                'ID_user'   => $user->getID_user(),
                'user_address1' => $user->getuser_address1(),
                'user_address2' => $user->getuser_address2(),
                'user_city' => $user->getuser_city(),
                'user_state' => $user->getuser_state(),
                'user_zip' => $user->getuser_zip(),
                'user_position' => $user->getuser_position(),
                'user_department' => $user->getuser_department(),
                'user_phone' => $user->getuser_phone(),
                'user_phone_ext' => $user->getuser_phone_ext(),
                'user_mobile' => $user->getuser_mobile(),
                'user_fax' => $user->getuser_fax(),
                'user_aim' => $user->getuser_aim(),
                'user_msn' => $user->getuser_msn(),
                'user_country' => $user->getuser_country(),
                'user_region' => $user->getuser_region()
        );

        if (null === ($id = $user->getID_optional()))
        {
            unset($data['ID_optional']);
            $this->getDbTable()->insert($data);
        } else
        {
            $this->getDbTable()->update($data, array('ID_optional = ?' => $id));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('user'));
    }

    public function find($ID_user, People_Model_UserOptional $user)
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
                ->setuser_auth($user_auth)
                ->setuser_optional($user_optional)
                ->setMapper($this);
        return $user;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'UserOptionalFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
            $users = array();
            foreach($resultSet as $row)
            {
                $user = new People_Model_UserOptional();
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
            $this->_fileCache->save($users, $cacheId, array('user'));
            return $users;
        }
        else return $cache;
    }
}