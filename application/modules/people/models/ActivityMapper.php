<?php
// application/modules/people/models/ActivityMapper.php

class People_Model_ActivityMapper
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

    /**
     * @return People_Model_DbTable_Activity
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('People_Model_DbTable_Activity');
        }
        return $this->_dbTable;
    }

    public function save(People_Model_Activity $activity)
    {
        $data = array(
                'ID_activity'          => $activity->ID_activity,
                'ID_object'            => $activity->ID_object,
                'ID_user'              => $activity->ID_user,
                'activity_object_type' => $activity->activity_object_type,
                'activity_type'        => $activity->activity_type,
                'activity_result'      => $activity->activity_result,
                'activity_date'        => $activity->activity_date
        );

        if (null === ($id = $activity->ID_activity))
        {
            unset($data['ID_activity']);
            $this->getDbTable()->insert($data);
        } else
        {
            $this->getDbTable()->update($data, array('ID_activity = ?' => $id));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('activity'));
    }

    public function delete($ID_activity)
    {
        $this->getDbTable()->delete(array('ID_activity = ?' => $ID_activity));
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('activity'));
    }

    public function find($ID_activity, People_Model_Activity $activity)
    {
        $result = $this->getDbTable()->find($ID_activity);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $activity->setID_activity($row->ID_activity)
                ->setID_object($row->ID_object)
                ->setID_user($row->ID_user)
                ->setActivity_object_type($row->activity_object_type)
                ->setActivity_type($row->activity_type)
                ->setActivity_result($row->activity_result)
                ->setActivity_date($row->activity_date)
                ->setMapper($this);
        return $activity;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        $cacheId = 'ActivityFetchAll_' . sha1((string)$where . (string)$order . (string)$count . (string)$offset);
        if ( ! $activities = $this->_fileCache->load($cacheId))
        {
            $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
            $activities = array();
            foreach($resultSet as $row)
            {
                $activity = new People_Model_Activity();
                $activity->setID_activity($row->ID_activity)
                        ->setID_object($row->ID_object)
                        ->setID_user($row->ID_user)
                        ->setActivity_object_type($row->activity_object_type)
                        ->setActivity_type($row->activity_type)
                        ->setActivity_result($row->activity_result)
                        ->setActivity_date($row->activity_date)
                        ->setMapper($this);
                $activities[] = $activity;
            }
            $this->_fileCache->save($activities, $cacheId, array('activitylog', 'activity', 'activityfetchall'));
        }

        return $activities;
    }
}