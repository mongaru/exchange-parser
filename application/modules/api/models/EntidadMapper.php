<?php

class Api_Model_EntidadMapper
{

    protected $_dbTable;
    protected $_fileCache;

    public function __construct()
    {

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
     * @return Api_Model_DbTable_Entidad
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('Api_Model_DbTable_Entidad');
        }
        return $this->_dbTable;
    }

    public function save(Api_Model_Entidad $obj)
    {
        $data = $obj->getData();
        //date_default_timezone_set(Zend_Auth::getInstance()->getIdentity()->user_timezone );
        if (!is_numeric($id = $obj->Id))
        {
            unset($data['Id']);
            $this->getDbTable()->insert($data);
            $id = $this->getDbTable()->getAdapter()->lastInsertId();
        }
        else
        {
            $this->getDbTable()->update($data, array('Id = ?' => $data['Id']));
        }

        return $id;
    }

    public function find($Id, Api_Model_Entidad $obj)
    {
        $result = $this->getDbTable()->find($Id);
        if($result->current()===null){
            return null;
        }

        $row = $result->current()->toArray();

        $obj->setOptions($row);
        return $obj;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $select = $this->getDbTable()->select()
            ->from('entidad')
            ->where($where)
            ->order($order)
            ->limit($count, $offset)
            ->setIntegrityCheck(false);
        $objs = $this->getDbTable()->fetchAll($select, $order, $count, $offset);

        $result = array();

        foreach ($objs as $record)
        {
            $obj = new Api_Model_Entidad($record->toArray());
            $result[] = $obj;
        }

        return $result;
    }
}