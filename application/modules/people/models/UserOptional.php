<?php
// application/modules/people/model/UserOptional.php

class People_Model_UserOptional
{
	// Fields
	protected $_ID_optional;
	protected $_ID_user;
	protected $_user_address1;
	protected $_user_address2;
	protected $_user_city;
	protected $_user_state;
	protected $_user_zip;
	protected $_user_position;
	protected $_user_department;
	protected $_user_phone;
	protected $_user_phone_ext;
	protected $_user_mobile;
	protected $_user_fax;
	protected $_user_aim;
	protected $_user_msn;
	protected $_user_country;
	protected $_user_region;
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
			throw new Exception('Invalid user property');
		}
		return $this->$method($value);
	}
	
	public function __get($name)
	{
		$method = 'get' . $name;
		if (('mapper' == $name) || !method_exists($this, $method)) {
			throw new Exception('Invalid user property');
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
			$this->setMapper(new People_Model_UserOptionalMapper());
		}
		return $this->_mapper;
	}
	
	public function save()
	{
		$this->getMapper()->save($this);
	}
	
	public function find($ID_user)
	{
		$this->getMapper()->find($ID_user, $this);
		return $this;
	}
	
	public function fetchAll($where = null, $order = null, $count = null, $offset = null)
	{
		return $this->getMapper()->fetchAll($where, $order, $count, $offset);
	}
	
	/**
	 * @param $_user_region the $_user_region to set
	 */
	public function setUser_region($_user_region) {
		$this->_user_region = $_user_region;
		return $this;
	}

	/**
	 * @return the $_user_region
	 */
	public function getuser_region() {
		return $this->_user_region;
	}

	/**
	 * @param $_user_country the $_user_country to set
	 */
	public function setUser_country($_user_country) {
		$this->_user_country = $_user_country;
		return $this;
	}

	/**
	 * @return the $_user_country
	 */
	public function getuser_country() {
		return $this->_user_country;
	}

	/**
	 * @param $_user_msn the $_user_msn to set
	 */
	public function setUser_msn($_user_msn) {
		$this->_user_msn = $_user_msn;
		return $this;
	}

	/**
	 * @return the $_user_msn
	 */
	public function getuser_msn() {
		return $this->_user_msn;
	}

	/**
	 * @param $_user_aim the $_user_aim to set
	 */
	public function setUser_aim($_user_aim) {
		$this->_user_aim = $_user_aim;
		return $this;
	}

	/**
	 * @return the $_user_aim
	 */
	public function getuser_aim() {
		return $this->_user_aim;
	}

	/**
	 * @param $_user_fax the $_user_fax to set
	 */
	public function setUser_fax($_user_fax) {
		$this->_user_fax = $_user_fax;
		return $this;
	}

	/**
	 * @return the $_user_fax
	 */
	public function getuser_fax() {
		return $this->_user_fax;
	}

	/**
	 * @param $_user_mobile the $_user_mobile to set
	 */
	public function setUser_mobile($_user_mobile) {
		$this->_user_mobile = $_user_mobile;
		return $this;
	}

	/**
	 * @return the $_user_mobile
	 */
	public function getuser_mobile() {
		return $this->_user_mobile;
	}

	/**
	 * @param $_user_phone the $_user_phone to set
	 */
	public function setUser_phone_ext($_user_phone_ext) {
		$this->_user_phone_ext = $_user_phone_ext;
		return $this;
	}

	/**
	 * @return the $_user_phone
	 */
	public function getuser_phone_ext() {
		return $this->_user_phone_ext;
	}
	
	/**
	 * @param $_user_phone the $_user_phone to set
	 */
	public function setUser_phone($_user_phone) {
		$this->_user_phone = $_user_phone;
		return $this;
	}

	/**
	 * @return the $_user_phone
	 */
	public function getuser_phone() {
		return $this->_user_phone;
	}

	/**
	 * @param $_user_department the $_user_department to set
	 */
	public function setUser_department($_user_department) {
		$this->_user_department = $_user_department;
		return $this;
	}

	/**
	 * @return the $_user_department
	 */
	public function getuser_department() {
		return $this->_user_department;
	}

	/**
	 * @param $_user_position the $_user_position to set
	 */
	public function setUser_position($_user_position) {
		$this->_user_position = $_user_position;
		return $this;
	}

	/**
	 * @return the $_user_position
	 */
	public function getuser_position() {
		return $this->_user_position;
	}

	/**
	 * @param $_user_zip the $_user_zip to set
	 */
	public function setUser_zip($_user_zip) {
		$this->_user_zip = $_user_zip;
		return $this;
	}

	/**
	 * @return the $_user_zip
	 */
	public function getuser_zip() {
		return $this->_user_zip;
	}

	/**
	 * @param $_user_state the $_user_state to set
	 */
	public function setUser_state($_user_state) {
		$this->_user_state = $_user_state;
		return $this;
	}

	/**
	 * @return the $_user_state
	 */
	public function getuser_state() {
		return $this->_user_state;
	}

	/**
	 * @param $_user_city the $_user_city to set
	 */
	public function setUser_city($_user_city) {
		$this->_user_city = $_user_city;
		return $this;
	}

	/**
	 * @return the $_user_city
	 */
	public function getuser_city() {
		return $this->_user_city;
	}

	/**
	 * @param $_user_address2 the $_user_address2 to set
	 */
	public function setUser_address2($_user_address2) {
		$this->_user_address2 = $_user_address2;
		return $this;
	}

	/**
	 * @return the $_user_address2
	 */
	public function getuser_address2() {
		return $this->_user_address2;
	}

	/**
	 * @param $_user_address1 the $_user_address1 to set
	 */
	public function setUser_address1($_user_address1) {
		$this->_user_address1 = $_user_address1;
		return $this;
	}

	/**
	 * @return the $_user_address1
	 */
	public function getuser_address1() {
		return $this->_user_address1;
	}

	/**
	 * @param $_ID_user the $_ID_user to set
	 */
	public function setID_user($_ID_user) {
		$this->_ID_user = $_ID_user;
		return $this;
	}

	/**
	 * @return the $_ID_user
	 */
	public function getID_user() {
		return $this->_ID_user;
	}

/**
	 * @param $_ID_optional the $_ID_optional to set
	 */
	public function setID_optional($_ID_optional) {
		$this->_ID_optional = $_ID_optional;
		return $this;
	}

	/**
	 * @return the $_ID_optional
	 */
	public function getID_optional() {
		return $this->_ID_optional;
	}
	
}