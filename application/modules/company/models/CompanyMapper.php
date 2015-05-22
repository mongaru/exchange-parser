<?php
// application/modules/company/models/CompanyMapper.php

class Company_Model_CompanyMapper
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
            $this->setDbTable('Company_Model_DbTable_Company');
        }
        return $this->_dbTable;
    }

    public function save(Company_Model_Company $company)
    {
        $data = $company->toArray();

         if (!isset($data['ID_company']) || $data['ID_company'] == '' || $data['ID_company'] == '0' || $data['ID_company'] == 0)
        {
            unset($data['ID_company']);
            $this->getDbTable()->insert($data);
        } 
        else
        {
            $this->getDbTable()->update($data, array('ID_company = ?' => $data['ID_company']));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('company'));
    }

    public function find($ID_company, Company_Model_Company $company)
    {
        $result = $this->getDbTable()->find($ID_company);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $company->setID_company($row->ID_company)
                ->setID_user($row->ID_user)
                ->setType($row->company_type)
                ->setcompany_name($row->company_name)
                ->setcompany_description($row->company_description)
                ->setcompany_master($row->company_master)
                ->setcompany_is_deleted($row->company_is_deleted)
                ->setData()
                ->setMapper($this);
        //echo "<pre>"; var_dump($company); echo "</pre>"; die();
        return $company;
    }

    /**
     * Fetch all records
     * 
     * @param <type> $where
     * @param <type> $order
     * @param <type> $count
     * @param <type> $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'CompanyFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $select = $this->getDbTable()->select()
                    ->from('company')
                    ->where($where)
                    ->order($order)
                    ->limit($count, $offset)
                    ->setIntegrityCheck(false);
            $resultSet = $this->getDbTable()->fetchAll($select, $order, $count, $offset);
            $companies = array();
            foreach($resultSet as $row)
            {
                $company = new Company_Model_Company();
                $company->setID_company($row->ID_company)
                        ->setID_user($row->ID_user)
                        ->setcompany_name($row->company_name)
                        ->setcompany_type($row->company_type)
                        ->setcompany_is_deleted($row->company_is_deleted)                        
                        ->setcompany_master($row->company_master)
                        ->setMapper($this);
                        $companies[] = $company;
            }

            $this->_fileCache->save($companies, $cacheId, array('company'));
            return $companies;
        }
        else return $cache;
    }

    public function fetchActiveCompaniesOptions()
    {
        return $this->fetchAll("company_is_deleted = 'no'");
        /*$companies = $this->fetchAll("company_is_deleted = 'no'");
        $result = array();

        foreach($companies as $c)
        {
            $result[$c->ID_company] = $c->company_name;
        }

        return $result;*/
    }
}
