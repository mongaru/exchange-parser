<?php

// application/modules/people/models/SubscriberMapper.php

class People_Model_SubscriberMapper
{

    protected $_dbTable;
    protected $_fileCache;

    public function __construct()
    {
        $this->_fileCache = Zend_Registry::get('fileCache');
    }

    /**
     *
     * @param string $dbTable
     * @return Zend_Db_Table_Abstract
     */
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
     * @return People_Model_DbTable_Subscriber
     */
    public function getDbTable()
    {
        if (null === $this->_dbTable)
        {
            $this->setDbTable('People_Model_DbTable_Subscriber');
        }
        return $this->_dbTable;
    }

    /**
     *
     * @param People_Model_Subscriber $subscriber 
     */
    public function save(People_Model_Subscriber $subscriber)
    {
        $data = array(
            'ID_subscriber' => $subscriber->getID_subscriber(),
            'ID_user' => $subscriber->getID_user(),
            'ID_object' => $subscriber->getID_object(),
            'subscriber_object_type' => $subscriber->getsubscriber_object_type()
        );

        if (null === ($id = $subscriber->getID_subscriber()))
        {
            unset($data['ID_subscriber']);
            $this->getDbTable()->insert($data);
        }
        else
        {
            $this->getDbTable()->update($data, array('ID_subscriber = ?' => $id));
        }
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('subscriber'));
    }

    /**
     *
     * @param integer $ID_subscriber
     */
    public function delete($ID_subscriber)
    {
        $this->getDbTable()->delete(array('ID_subscriber = ?' => $ID_subscriber));
        $this->_fileCache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array('subscriber'));
    }

    /**
     *
     * @param integer $ID_subscriber
     * @param People_Model_Subscriber $subscriber
     * @return array
     */
    public function find($ID_subscriber, People_Model_Subscriber $subscriber)
    {
        $result = $this->getDbTable()->find($ID_subscriber);
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $subscriber->setID_subscriber($row->ID_subscriber)
            ->setID_user($row->ID_user)
            ->setID_object($row->ID_object)
            ->setsubscriber_object_type($row->subscriber_object_type)
            ->setMapper($this);
        return $subscriber;
    }

    /**
     *
     * @param integer $userId
     * @param integer $salesKitId
     * @param People_Model_Subscriber $subscriber
     * @return People_Model_Subscriber
     */
    public function findBySaleskit($userId, $salesKitId, People_Model_Subscriber $subscriber)
    {
        $row = $this->getDbTable()->fetchRow($this->getDbTable()->select()
                    ->where('ID_user = ?', $userId)
                    ->where('ID_object = ?', $salesKitId)
                    ->where('subscriber_object_type = ?', 'saleskit'));
        if (0 == count($row))
        {
            return;
        }
        $subscriber->setID_subscriber($row->ID_subscriber)
            ->setID_user($row->ID_user)
            ->setID_object($row->ID_object)
            ->setsubscriber_object_type($row->subscriber_object_type)
            ->setMapper($this);
        return $subscriber;
    }

    public function findPhoto($fileId, $salesKitId, Saleskit_Model_SaleskitPhoto $record)
    {
        $row = $this->getDbTable()->fetchRow($this->getDbTable()->select()
                    ->where('ID_file = ?', $fileId)
                    ->where('ID_sales_kit = ?', $salesKitId));
        if (0 == count($row))
        {
            return;
        }
        $record->setID_sales_kit_photo($row->ID_sales_kit_photo)
            ->setID_sales_kit($row->ID_sales_kit)
            ->setID_file($row->ID_file)
            ->setphoto_title($row->photo_title)
            ->setMapper($this);
        return $record;
    }

    /**
     *
     * @param integer $ID_user
     * @param integer $ID_yield
     * @param People_Model_Subscriber $subscriber
     * @return array
     */
    public function findYieldSubscriber($ID_user, $ID_yield, People_Model_Subscriber $subscriber)
    {
        $result = $this->getDbTable()->fetchAll(array("ID_user = $ID_user AND ID_object = $ID_yield AND subscriber_object_type = 'yield'"));
        if (0 == count($result))
        {
            return;
        }
        $row = $result->current();
        $subscriber->setID_subscriber($row->ID_subscriber)
            ->setID_user($row->ID_user)
            ->setID_object($row->ID_object)
            ->setsubscriber_object_type($row->subscriber_object_type)
            ->setMapper($this);
        return $subscriber;
    }

    /**
     *
     * @param integer $objectId
     * @param string $objectType
     * @return boolean
     */
    public function isCurrentUserSubscribed($objectId, $objectType)
    {
        $userId = Zend_Auth::getInstance()->getIdentity()->ID_user;
        $result = $this->getDbTable()->fetchAll("ID_user = $userId AND ID_object = $objectId AND subscriber_object_type = '$objectType'");
        if (0 == count($result))
            return false;
        else
            return true;
    }

    /**
     *
     * @param string|array $where
     * @param string|array $order
     * @param integer $count
     * @param integer $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {

        $resultSet = $this->getDbTable()->fetchAll($where, $order, $count, $offset);
        $subscribers = array();
        foreach ($resultSet as $row)
        {
            $subscriber = new People_Model_Subscriber();
            $subscriber->setID_subscriber($row->ID_subscriber)
                ->setID_user($row->ID_user)
                ->setID_object($row->ID_object)
                ->setsubscriber_object_type($row->subscriber_object_type)
                ->setMapper($this);
            $subscribers[] = $subscriber;
        }

        return $subscribers;
    }

    /**
     *
     * @param integer $yieldId
     * @param integer $yieldResponsible
     * @return array
     */
    public function fetchSubscribers($yieldId, $yieldResponsible = NULL)
    {
        $cacheId = 'SubscriberFetchSubscribers_' . sha1((string) $yieldId . (string) $yieldResponsible);
        if (!$cache = $this->_fileCache->load($cacheId))
        {
            $subscribers = array();
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('subscriber')
                ->where('ID_object = ?', $yieldId)
                ->where('subscriber_object_type = ?', 'yield');
            if ($yieldResponsible != NULL)
                $select->where('ID_user <> ?', $yieldResponsible);
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();
            foreach ($resultSet as $row)
            {
                $subscriber = new People_Model_Subscriber();
                $subscriber->setID_subscriber($row['ID_subscriber'])
                    ->setID_user($row['ID_user'])
                    ->setID_object($row['ID_object'])
                    ->setsubscriber_object_type($row['subscriber_object_type'])
                    ->setMapper($this);
                $subscribers[] = $subscriber;
            }
            $this->_fileCache->save($subscribers, $cacheId, array('subscriber', 'user', 'subscriberfetchall'));
            return $subscribers;
        }
        else
            return $cache;
    }

    /**
     *
     * @param integer $salesKitId
     * @return array
     */
    public function fetchSalesKitSubscribers($salesKitId)
    {
        $cacheId = 'SalesKitSubscribers_' . (string) $salesKitId;
        if (!$cache = $this->_fileCache->load($cacheId))
        {
            $select = $this->getDbTable()->getAdapter()->select()
                ->from('subscriber', array('ID_user'))
                ->where('ID_object = ?', $salesKitId)
                ->where('subscriber_object_type = ?', 'saleskit');
            $stmt = $this->getDbTable()->getAdapter()->query($select);
            $resultSet = $stmt->fetchAll();

            $subscribers = array();
            foreach ($resultSet as $row)
                $subscribers[$row['ID_user']] = 'yes';

            $this->_fileCache->save($subscribers, $cacheId, array('subscriber', 'user', 'saleskit'));
            return $subscribers;
        }
        else
            return $cache;
    }

}