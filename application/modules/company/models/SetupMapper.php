<?php
// application/modules/company/models/SetupMapper.php

class Company_Model_SetupMapper
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
            $this->setDbTable('Company_Model_DbTable_Setup');
        }
        return $this->_dbTable;
    }

    public function save(Company_Model_Setup $setup)
    {
        $data = array(
                'setup_id'   => $setup->getSetup_id(),
                'disclaimer_text'   => $setup->getDisclaimer_text()
        );

        if (null === ($id = $setup->getSetup_id()))
        {
            $this->getDbTable()->insert($data);
        } 
        else
        {
            $this->getDbTable()->update($data, array('setup_id = ?' => $id));
        }
    }

    public function find($setup_id, Company_Model_Setup $setup)
    {
        $result = $this->getDbTable()->find($setup_id);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $setup->setSetup_id($row->setup_id)
                ->setDisclaimer_text($row->disclaimer_text)
                ->setMapper($this);
        return $setup;
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
      $select = $this->getDbTable()->select()
                ->from('setup')
                ->where($where)
                ->order($order)
                ->limit($count, $offset)
                ->setIntegrityCheck(false);
        $resultSet = $this->getDbTable()->fetchAll($select, $order, $count, $offset);
        $setup = array();
        foreach($resultSet as $row)
        {
            $setup = new Company_Model_Setup();
            $setup->setSetup_id($row->setup_id)
                    ->setDisclaimer_text($row->disclaimer_text)
                    ->setMapper($this);
                    $setups[] = $setup;
        }

        return $setups;
        
    }
 
}
