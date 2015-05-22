<?php
// application/modules/company/model/CompanyUser.php

class Company_Model_CompanyUser
{
// Fields
    protected $_ID_company_user;
    protected $_ID_company;
    protected $_ID_user;
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
            $this->setMapper(new Company_Model_CompanyUserMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        $this->getMapper()->save($this);
        return $this->getMapper()->getDbTable()->getAdapter()->lastInsertId();
    }

    public function getCompanyUser($ID_user){

         $this->getMapper()->getCompanyUser($ID_user, $this);
        return $this;
    }

    
    public function find($ID_company)
    {
        $this->getMapper()->find($ID_company, $this);
        return $this;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }

    public function setID_company_user($_ID_company_user)
    {
        $this->_ID_company_user = $_ID_company_user;
        return $this;
    }

    public function getID_company_user()
    {
        return $this->_ID_company_user;
    }

    public function setID_user($_ID_user)
    {
        $this->_ID_user = $_ID_user;
        return $this;
    }

    public function getID_user()
    {
        return $this->_ID_user;
    }

    public function setID_company($_ID_company)
    {
        $this->_ID_company = $_ID_company;
        return $this;
    }

    public function getID_company()
    {
        return $this->_ID_company;
    }

    public function toArray() 
    {
        $array =array(
            'ID_company_user' => $this->_ID_company_user,
            'ID_company' => $this->_ID_company,
            'ID_user' => $this->_ID_user         
        );
        return $array;
    }
}