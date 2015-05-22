<?php


 class Amedia_Model_PrivilegesMapper
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
            $this->setDbTable('Amedia_Model_DbTable_Privileges');
        }
        return $this->_dbTable;
    }

    public function save(Amedia_Model_Privileges $pr)
    {
        if (!is_numeric($id = $pr->id_privileges))
        {
            $data = $pr->toArray();
            $data['privileges_rol_name'] =strtolower(str_replace(" ", "_", $data['privileges_rol_name']));
            $data['privileges_rol_data'] = json_encode($data['privileges_rol_data']);
            unset($data['id_privileges']);

            $this->getDbTable()->insert($data);
            $id = $this->getDbTable()->getAdapter()->lastInsertId();
        } 
        else
        {

            $data = $pr->toArray();
            $data['privileges_rol_data'] = json_encode($data['privileges_rol_data']);
            $data['privileges_rol_name'] =strtolower(str_replace(" ", "_", $data['privileges_rol_name']));
            $this->getDbTable()->update($data, array('id_privileges = ?' => $pr->id_privileges));
        }

        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('privileges'));
        
        return $id;
    }


    public function fetchByRollname($Rolname)
    {
            $users = array();
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('privileges', 'id_privileges')
                ->where("privileges_rol_name = ?", $Rolname);

            return $select->query()->fetch();

    }


    
    public function find($ID, Amedia_Model_Privileges $obj)
    {

        if(is_numeric($ID)){
            $result = $this->getDbTable()->find($ID);
            
            if (0 == count($result))
            {
                return;
            }

            $row = $result->current()->toArray();
            $row['privileges_rol_data'] = json_decode($row['privileges_rol_data']);
            $obj->setOptions($row);
        } else {
          
             $result = $this->getDbTable()->find($obj->fetchByRollname($ID));
            
            if (0 == count($result))
            {
                return;
            }

            $row = $result->current()->toArray();
            $row['privileges_rol_data'] = json_decode($row['privileges_rol_data']);
            $obj->setOptions($row);
        }
   

        return $obj;        
    }
  


    public function privileges_fetchAll()
    {
        $select = $this->getDbTable()->select();
        $resultSet = $this->getDbTable()->fetchAll($select);
        return $resultSet;
    }

    public function fetchAll()
    {
        $select = $this->getDbTable()->select()
                ->from('privileges')
                ->where("privileges_is_deleted = 'no'");
        $resultSet = $this->getDbTable()->fetchAll($select);
        
        $privileges = array();
        foreach($resultSet as $row)
        {
            $row = $row->toArray();
            $row['privileges_rol_data'] = json_decode($row['privileges_rol_data']);

            $priv = new Amedia_Model_Privileges($row);
            $privileges[] = $priv;
        }

        return $privileges;
    }

}

