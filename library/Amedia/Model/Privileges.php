<?php


class Amedia_Model_Privileges {
    
    protected $_data;
    protected $_mapper;

    //Constructor
    public function __construct(array $options = null) {

        $this->_data = array(
            'id_privileges' => null,
            'privileges_is_deleted' => null,
            'privileges_rol_data' => null,
            'privileges_rol_name' => null,
            'department' => null,         
            'priority' => 0
        );

        if (is_array($options)) {
            $this->setOptions($options);
        }
    }

    public function __get($attribute) {
        if (array_key_exists($attribute, $this->_data)) {
            return $this->_data[$attribute];
        }
        throw new Exception('Invalid Module Property: ' . $attribute);
        return null;
    }

    public function __set($attribute, $value) {
        if (array_key_exists($attribute, $this->_data)) {
            $this->_data[$attribute] = $value;
            return true;
        }
        throw new Exception('Invalid Module Property: ' . $attribute);
        return false;
    }

    public function setOptions(array $options) {
        foreach ($options as $key => $value) {
            if (array_key_exists($key, $this->_data))
                $this->_data[$key] = $value;
        }
    }

    public function getData() {
        return $this->_data;
    }

    public function setMapper($mapper) {
        $this->_mapper = $mapper;
        return $this;
    }


    public function getMapper() {
        if (null === $this->_mapper) {
            $this->setMapper(new Amedia_Model_PrivilegesMapper());
        }
        return $this->_mapper;
    }

    public function save() {
        return $this->getMapper()->save($this);
    }

    public function find($ID_cl) {
        $this->getMapper()->find($ID_cl, $this);
        return $this;
    }

    public function fetchAll() {
        return $this->getMapper()->fetchAll();
    }

    public function fetchByRollname($rolname)
    {
        return $this->getMapper()->fetchByRollname($rolname);
    }
        
    public function toArray() {
        return $this->_data;
    }

}