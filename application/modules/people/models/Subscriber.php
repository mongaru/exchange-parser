<?php
// application/modules/people/model/Subscriber.php

class People_Model_Subscriber
{
// Fields
    protected $_ID_subscriber;
    protected $_ID_user;
    protected $_ID_object;
    protected $_subscriber_object_type;
    protected $_mapper;

    public function __construct(array $options = null)
    {
        if (is_array($options))
        {
            $this->setOptions($options);
        }
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid subscriber property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid subscriber property');
        }
        return $this->$method();
    }

    public function setOptions(array $options)
    {
        $methods = get_class_methods($this);
        foreach($options as $key => $value)
        {
            $method = 'set' . ucfirst($key);
            if (in_array($method, $methods))
            {
                $this->$method($value);
            }
        }
        return $this;
    }

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    /**
     *
     * @return People_Model_SubscriberMapper
     */
    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new People_Model_SubscriberMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function delete()
    {
        $this->getMapper()->delete($this->_ID_subscriber);
    }

    public function find($ID_subscriber)
    {
        $this->getMapper()->find($ID_subscriber, $this);
        return $this;
    }

    /**
     *
     * @param integer $userId
     * @param integer $salesKitId
     * @return People_Model_Subscriber
     */
    public function findBySaleskit($userId, $salesKitId)
    {
        $this->getMapper()->findBySaleskit($userId, $salesKitId, $this);
        return $this;
    }

    public function findYieldSubscriber($ID_subscriber, $ID_yield)
    {
        $this->getMapper()->findYieldSubscriber($ID_subscriber, $ID_yield, $this);
        return $this;
    }

    public function isCurrentUserSubscribed($objectId, $objectType)
    {
        return $this->getMapper()->isCurrentUserSubscribed($objectId, $objectType);
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }

    /**
     *
     * @param integer $yieldId
     * @param integer $yieldResponsible
     * @return array
     */
    public function fetchSubscribers($yieldId, $yieldResponsible = NULL)
    {
        return $this->getMapper()->fetchSubscribers($yieldId, $yieldResponsible);
    }

    /**
     *
     * @param integer $salesKitId
     * @return array
     */
    public function fetchSalesKitSubscribers($salesKitId)
    {
        return $this->getMapper()->fetchSalesKitSubscribers($salesKitId);
    }

    /**
     * @param $_subscriber_object_type the $_subscriber_object_type to set
     */
    public function setsubscriber_object_type($_subscriber_object_type)
    {
        $this->_subscriber_object_type = $_subscriber_object_type;
        return $this;
    }

    /**
     * @return the $_subscriber_object_type
     */
    public function getSubscriber_object_type()
    {
        return $this->_subscriber_object_type;
    }

    /**
     * @param $_ID_object the $_ID_object to set
     */
    public function setID_object($_ID_object)
    {
        $this->_ID_object = $_ID_object;
        return $this;
    }

    /**
     * @return the $_ID_object
     */
    public function getID_object()
    {
        return $this->_ID_object;
    }

    /**
     * @param $_ID_user the $_ID_user to set
     */
    public function setID_user($_ID_user)
    {
        $this->_ID_user = $_ID_user;
        return $this;
    }

    /**
     * @return the $_ID_user
     */
    public function getID_user()
    {
        return $this->_ID_user;
    }

    /**
     * @param $_ID_subscriber the $_ID_subscriber to set
     */
    public function setID_subscriber($_ID_subscriber)
    {
        $this->_ID_subscriber = $_ID_subscriber;
        return $this;
    }

    /**
     * @return the $_ID_subscriber
     */
    public function getID_subscriber()
    {
        return $this->_ID_subscriber;
    }


}