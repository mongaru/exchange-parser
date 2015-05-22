<?php
// application/modules/company/model/Company.php

class Company_Model_Company
{
    // Fields
    protected $_ID_company;
    protected $_ID_user;
    protected $_company_name;
    protected $_company_description;        
    protected $_company_type;    
    protected $_company_is_deleted;
    protected $_company_master;
    protected $_data;
    protected $_mapper;

    public function __construct(array $options = null)
    {
        $this->_data = $options;
        if (is_array($options))
        {
            $this->setOptions($options);
        }
    }
    public function setData()
    {
        return $this;
    }

    public function __set($name, $value)
    {
        $method = 'set' . $name;
        $this->_data[$name] = $value;
        if (('mapper' == $name) || !method_exists($this, $method))
        {
            throw new Exception('Invalid user property');
        }
        return $this->$method($value);
    }

    public function toArray() 
    {
        return $this->_data;
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
            $this->setMapper(new Company_Model_CompanyMapper());
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

    public function fetchActiveCompaniesOptions()
    {
        return $this->getMapper()->fetchActiveCompaniesOptions();
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
     * Get all Companies listed as Vendors
     * @return array
     */
    public function getAllVendors()
    {
        return $this->fetchAll("company_type = 'vendor' AND company_is_deleted = 'no'", 'company_name ASC');
    }

    /**
     * Return a list of Company Types
     * @return array
     */
    public function getTypes()
    {
        $tmp = array();
        $userDepartment = strtolower(Zend_Auth::getInstance()->getIdentity()->user_department);
        $currentUserIsClient = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'clients');
        $currentUserIsVendor = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'vendors');
        $currentUserIsCostingStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'costing');
        $currentUserIsSalesStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'sales');
        $currentUserIsYieldsStaff = (strtolower(Zend_Auth::getInstance()->getIdentity()->user_department) == 'yields');
        
        switch($userDepartment)
        {
            case 'admins':
                $tmp = array(
                    'owner' => 'Owner',
                    'client' => 'Client',
                    'vendor' => 'Vendor'
                );
                break;
            
            case 'clients':
                $tmp = array(
                );
                break;
            
            case 'vendors':
                $tmp = array(
                );
                break;
            
            case 'costing':
                $tmp = array(
                    'vendor' => 'Vendor'
                );
                break;
            
            case 'sales':
                $tmp = array(
                    'client' => 'Client',
                );
                break;
            
            case 'yields':
                $tmp = array(
                );
                break;
            
            default:
                $tmp = array();
        }
        
        return $tmp;
        
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

    public function setcompany_description($description)
    {
        $this->_data['company_description'] = $description;
        $this->_company_description = $description;
        return $this;
    }
    public function getcompany_description()
    {
        return $this->_company_description;
    }
    public function setID_company($_ID_company)
    {
        $this->_data['ID_company'] = $_ID_company;
        $this->_ID_company = $_ID_company;
        return $this;
    }

    public function getID_company()
    {
        return $this->_ID_company;
    }
    public function setCompany_type($type)
    {
        $this->_data['company_type'] = $type;
        $this->_company_type = $type;
        return $this;
    }

    public function getcompany_type()
    {
        return $this->_company_type;
    }
    public function setCompany_name($name)
    {
        $this->_data['company_name'] = $name;
        $this->_company_name = $name;
        return $this;
    }

    public function getcompany_name()
    {
        return $this->_company_name;
    }

    public function setCompany_homepage($www)
    {
        $this->_company_homepage = $www;
        return $this;
    }

    public function getcompany_homepage()
    {
        return $this->_company_homepage;
    }
    public function setType($type){
         $this->_data['company_type'] = $type;        
         $this->_company_type = $type;
         return $this;
    }   
    public function setCompany_phone($n)
    {
        $this->_company_phone = $n;
        return $this;
    }

    public function getcompany_phone()
    {
        return $this->_company_phone;
    }
   
    public function setCompany_fax($n)
    {
        $this->_company_fax = $n;
        return $this;
    }

    public function getcompany_fax()
    {
        return $this->_company_fax;
    }
   
    public function setCompany_address($address)
    {
        $this->_company_address = $address;
        return $this;
    }

    public function getcompany_address()
    {
        return $this->_company_address;
    }
   
    public function setcompany_master($yn)
    {
        $this->_company_master = $yn;
        return $this;
    }

    public function getcompany_master()
    {
        return $this;
    } 
      
    public function setCompany_is_deleted($yn)
    {
        $this->_data['company_is_deleted'] = $yn;
        $this->_company_is_deleted = $yn;
        return $this;
    }

    public function getcompany_is_deleted()
    {
        return $this->_company_is_deleted;
    }
}
