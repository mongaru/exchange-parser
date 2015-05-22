<?php
// application/modules/people/models/UserAuthMapper.php

class People_Model_UserAuthMapper
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
            $this->setDbTable('People_Model_DbTable_UserAuth');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_UserAuth $user)
    {
        $data = array(
                'ID_auth'   => $user->getID_auth(),
                'ID_user'   => $user->getID_user(),
                'user_salt' => '',
                'user_status' => $user->getuser_status(),
                'user_level' => $user->getuser_level(),
                'user_created' => date('Y-m-d H:i:s'),
                'user_updated' => date('Y-m-d H:i:s'),
                'user_invalid_logins' => 0,
                'user_banned_until' => null,
                'user_secret_question' => '',
                'user_secret_answer' => '',
                'user_last_login' => null,
                'user_avatar_file' => $user->getuser_avatar_file()
        );



        if ($user->getuser_password() != '')
            $data['user_password'] = $user->getuser_password();
        
  if (null === ($id = $user->getID_auth()))
        {
            unset($data['ID_auth']);
            $this->getDbTable()->insert($data);
        } else
        {
            unset($data['user_created']);
            $this->getDbTable()->update($data, array('ID_auth = ?' => $id));
        
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('user', 'userauth'));
    }

    }

    public function find($ID_user, People_Model_UserAuth $user)
    {
        $row = $this->getDbTable()->fetchRow('ID_user = ' . $ID_user);
        if (0 == count($row))
        {
            return;
        }
        $user->setID_auth($row->ID_auth)
                ->setID_user($row->ID_user)
                ->setuser_salt($row->user_salt)
                ->setuser_password($row->user_password)
                ->setuser_status($row->user_status)
                ->setuser_level($row->user_level)
                ->setuser_created($row->user_created)
                ->setuser_updated($row->user_updated)
                ->setuser_invalid_logins($row->user_invalid_logins)
                ->setuser_banned_until($row->user_banned_until)
                ->setuser_secret_question($row->user_secret_question)
                ->setuser_secret_answer($row->user_secret_answer)
                ->setuser_last_login($row->user_last_login)
                ->setuser_avatar_file($row->user_avatar_file)
                ->setMapper($this);
        return $user;
    }

    public function fetchAllByUserLevel($userLevel)
    {
            $users = array();
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('user_auth', 'ID_user')
                ->where("user_level = ?", $userLevel);

            return $select->query()->fetchAll();

    }


    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'UserAuthFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
            $users = array();
            foreach($resultSet as $row)
            {
                $user = new People_Model_UserAuth();
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
            $this->_fileCache->save($users, $cacheId, array('user', 'userauth'));
            return $users;
        }
        else return $cache;
    }
}