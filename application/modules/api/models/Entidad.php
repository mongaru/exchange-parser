<?php

class Api_Model_Entidad
{
    const ENTIDADES = array('CCE' => 'Cambios Chaco Encarnacion');
    const TIPO = array('CC' => 'Cambios Chaco');

    protected $_data;
    protected $_mapper;

    public function __construct(array $options = null)
    {
        $this->_data = array(
            'Id' => null,
            'Nombre' => null,
            'URL' => null,
            'Entidad' => null,
            'Tipo' => null,
        );

        if (is_array($options))
        {
            $this->setOptions($options);
        }
    }

    public function __get($attribute)
    {
        if (array_key_exists($attribute, $this->_data))
        {
            return $this->_data[$attribute];
        }

        throw new Exception('Invalid Module Property: ' . $attribute);
        return null;
    }

    public function __set($attribute, $value)
    {
        if (array_key_exists($attribute, $this->_data))
        {
            $this->_data[$attribute] = $value;
            return true;
        }

        throw new Exception('Invalid Module Property: ' . $attribute);
        return false;
    }

    public function setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            if (array_key_exists($key, $this->_data))
                $this->_data[$key] = $value;
        }
    }

    public function getData()
    {
        return $this->_data;
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
            $this->setMapper(new Api_Model_EntidadMapper());
        }
        return $this->_mapper;
    }

    public function save()
    {
        return $this->getMapper()->save($this);
    }

    public function find($id_product)
    {
        $this->getMapper()->find($id_product, $this);
        return $this;
    }

    public function fetchAll($where = null, $order = null, $count = null, $offset = null)
    {
        return $this->getMapper()->fetchAll($where, $order, $count, $offset);
    }
}