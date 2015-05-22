<?php
// application/modules/company/models/CompanyMapperUser.php

class Company_Model_CompanyUserMapper
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
            $this->setDbTable('Company_Model_DbTable_CompanyUser');
        }
        return $this->_dbTable;
    }

    public function getCompanyUser($ID_user, Company_Model_CompanyUser $company_user){
    
        $select = $this->getDbTable()->select()
                ->from('company_user')
                ->where('ID_user = ?', $ID_user);
 
       $result = $this->getDbTable()->fetchRow($select);
            $row = $result->toArray();
                $company_user->setID_company_user($row['ID_company_user'])
                        ->setID_company($row['ID_company'])
                        ->setID_user($row['ID_user'])
                        ->setMapper($this);

        return $company_user;
    }

    public function save(Company_Model_CompanyUser $company_user)
    {
        $data = $company_user->toArray();
      
        if (!isset($data['ID_company_user']) || $data['ID_company_user'] == '' || $data['ID_company_user'] == '0' || $data['ID_company_user'] == 0)
        {

            unset($data['ID_company_user']);
            $this->getDbTable()->insert($data);
        }
        else
        {
                
            $this->getDbTable()->update($data, array('ID_company_user = ?' => $data['ID_company_user']));
        }
  
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('company_user'));
    }


    public function find($ID_company_user, Company_Model_CompanyUser $company_user)
    {
        $result = $this->getDbTable()->find($ID_company_user);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $company_user->setID_company_user($row->ID_company_user)
                    ->setID_user($row->ID_user)
                    ->setID_company($row->ID_company)
                    ->setMapper($this);
        return $company_user;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'CompanyUserFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $select = $this->getDbTable()->select()
                    ->from('company_user')
                    ->where($where)
                    ->order($order)
                    ->limit($count, $offset)
                    ->setIntegrityCheck(false);
            $resultSet = $this->getDbTable()->fetchAll($select, $order, $count, $offset);
            $companies = array();
            foreach($resultSet as $row)
            {
                $company_user = new Company_Model_CompanyUser();
                $company_user->setID_company_user($row->ID_company_user)
                        ->setID_company($row->ID_company)
                        ->setID_user($row->ID_user)
                        ->setMapper($this);
                        $companies[] = $company;
            }

            $this->_fileCache->save($companies, $cacheId, array('company_user'));
            return $companies;
        }
        else return $cache;
    }
 
}