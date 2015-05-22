<?php
// application/modules/people/model/Relationship.php

class People_Model_Relationship
{
	// Fields
	protected $_ID_rel;
	protected $_parent_ID_object;
	protected $_parent_object_type;
	protected $_child_ID_object;
	protected $_child_object_type;
	protected $_mapper;

	public function __construct(array $options = null)
	{
		if (is_array($options)) {
			$this->setOptions($options);
		}
	}

	public function __set($name, $value)
	{
		$method = 'set' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid relationship property');
		}
		return $this->$method($value);
	}

	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid relationship property');
		}
		return $this->$method();
	}

	public function setOptions(array $options)
	{
		$methods = get_class_methods($this);
		foreach($options as $key => $value) {
			$method = 'set' . ucfirst($key);
			if (in_array($method, $methods)) {
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

	public function getMapper()
	{
		if (null === $this->_mapper) {
			$this->setMapper(new People_Model_RelationshipMapper());
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
		$this->getMapper()->delete($this->_ID_rel);
	}

	public function find($ID_rel)
	{
		$this->getMapper()->find($ID_rel, $this);
		return $this;
	}

	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->getMapper()->fetchAll($where, $order, $count, $offset);
	}

	/**
	 * @return the $_ID_rel
	 */
	public function getID_rel() {
		return $this->_ID_rel;
	}

	/**
	 * @param $_ID_rel the $_ID_rel to set
	 */
	public function setID_rel($_ID_rel) {
		$this->_ID_rel = $_ID_rel;
		return $this;
	}

	/**
	 * @return the $_parent_ID_object
	 */
	public function getParent_ID_object() {
		return $this->_parent_ID_object;
	}

	/**
	 * @param $_parent_ID_object the $_parent_ID_object to set
	 */
	public function setParent_ID_object($_parent_ID_object) {
		$this->_parent_ID_object = $_parent_ID_object;
		return $this;
	}

	/**
	 * @return the $_parent_object_type
	 */
	public function getParent_object_type() {
		return $this->_parent_object_type;
	}

	/**
	 * @param $_parent_object_type the $_parent_object_type to set
	 */
	public function setParent_object_type($_parent_object_type) {
		$this->_parent_object_type = $_parent_object_type;
		return $this;
	}

	/**
	 * @return the $_child_ID_object
	 */
	public function getChild_ID_object() {
		return $this->_child_ID_object;
	}

	/**
	 * @param $_child_ID_object the $_child_ID_object to set
	 */
	public function setChild_ID_object($_child_ID_object) {
		$this->_child_ID_object = $_child_ID_object;
		return $this;
	}

	/**
	 * @return the $_child_object_type
	 */
	public function getChild_object_type() {
		return $this->_child_object_type;
	}

	/**
	 * @param $_child_object_type the $_child_object_type to set
	 */
	public function setChild_object_type($_child_object_type) {
		$this->_child_object_type = $_child_object_type;
		return $this;
	}




}