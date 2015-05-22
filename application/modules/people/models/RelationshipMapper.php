<?php
// application/modules/people/models/RelationshipMapper.php

class People_Model_RelationshipMapper
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
            $this->setDbTable('People_Model_DbTable_Relationship');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_Relationship $relationship)
    {
        $data = array(
                'ID_rel'             => $relationship->ID_rel,
                'parent_ID_object'   => $relationship->parent_ID_object,
                'parent_object_type' => $relationship->parent_object_type,
                'child_ID_object'    => $relationship->child_ID_object,
                'child_object_type'  => $relationship->child_object_type
        );

        if (null === ($id = $relationship->ID_rel))
        {
            unset($data['ID_rel']);
            $this->getDbTable()->insert($data);
        } else
        {
            $this->getDbTable()->update($data, array('ID_rel = ?' => $id));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('relationship'));
    }

    public function delete($ID_rel)
    {
        $this->getDbTable()->delete(array('ID_rel = ?' => $ID_rel));
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('relationship'));
    }

    public function find($ID_rel, People_Model_Relationship $relationship)
    {
        $result = $this->getDbTable()->find($ID_rel);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $relationship->setID_rel($row->ID_rel)
                ->setParent_ID_object($row->parent_ID_object)
                ->setParent_object_type($row->parent_object_type)
                ->setChild_ID_object($row->child_ID_object)
                ->setChild_object_type($row->child_object_type)
                ->setMapper($this);
        return $relationship;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'RelationshipFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $cache = $this->_fileCache->load($cacheId))
        {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
            $relationships = array();
            foreach($resultSet as $row)
            {
                $relationship = new People_Model_Relationship();
                $relationship->setID_rel($row->ID_rel)
                        ->setParent_ID_object($row->parent_ID_object)
                        ->setParent_object_type($row->parent_object_type)
                        ->setChild_ID_object($row->child_ID_object)
                        ->setChild_object_type($row->child_object_type)
                        ->setMapper($this);
                $relationships[] = $relationship;
            }
            $this->_fileCache->save($relationships, $cacheId, array('relationship', 'relationshipfetchall'));
            return $relationships;
        }
        else return $cache;
    }

}