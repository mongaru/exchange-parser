<?php
// application/modules/company/model/Setup.php

class Company_Model_Setup
{
// Fields
    protected $_setup_id;
    protected $_disclaimer_text;
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
            throw new Exception('Invalid user property');
        }
        return $this->$method($value);
    }

    public function __get($name)
    {
        $method = 'get' . $name;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid user property');
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
    
    public function exportToArray()
    {
        $vars = get_class_vars(get_class($this));
        $a = array();
        foreach($vars  as $k => $v)
            $a[substr($k,1)] = $this->$k;
        
        array_pop($a); //unsets mapper
        return $a;       
    }

    public function setMapper($mapper)
    {
        $this->_mapper = $mapper;
        return $this;
    }

    public function getMapper()
    {
        if (null === $this->_mapper)
        {
            $this->setMapper(new Company_Model_SetupMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function find($ID_company)
    {
        $this->getMapper()->find($ID_company, $this);
        return $this;
    }

    /**
     * Fetch all records
     *
     * @param string $where
     * @param string $order
     * @param integer $count
     * @param integer $offset
     * @return array
     */
    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }


     /**
     * @param $_disclaimer_text the $_disclaimer_text to set
     */
    public function setSetup_id($_setup_id)
    {
        $this->_setup_id = $_setup_id;
        return $this;
    }

    /**
     * @return the $_disclaimer_text
     */
    public function getSetup_id()
    {
        return $this->_setup_id;
    }                                                                                         
    /**
     * @param $_disclaimer_text the $_disclaimer_text to set
     */
    public function setDisclaimer_text($_disclaimer_text)
    {
        $this->_disclaimer_text = $_disclaimer_text;
        return $this;
    }

    /**
     * @return the $_disclaimer_text
     */
    public function getDisclaimer_text()
    {
        return $this->_disclaimer_text;
    }

}
