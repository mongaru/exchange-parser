<?php
/**
 * class People_Form_UserAdd
 *
 * @package     People
 * @subpackage  User Add
 * @author      Alvaro Mercado <alvaro@amediacreative.com>
 * @link        www.amediacreative.com
 */
class People_Form_UserAdd extends Zend_Form
{
	/**
     * Form Init
     *
     * @param void
     * @return void
     */
	public function init()
	{
		//$this->clearDecorators();
		
		// Sets the form method to POST
		$this->setMethod('post');
		
		// Sets the form name
		$this->setName('FormUserAdd');
		$this->setAction('people/user/add');
		// Role
		$this->addElement('text', 'user_level', array(
			'label'      => 'user_level',
			'required'   => true,
			'filters'    => array('StringTrim')
		));	


		$this->addElement('text', 'user_department', array(
			'label'      => 'user_department',
			'required'   => false,
			'filters'    => array('StringTrim')
		));			

		
		// User_auth
		$this->addElement('text', 'ID_optional', array(
			'label'      => 'ID_optional',
			'required'   => false,
			'filters'    => array('StringTrim')
		));	

		// User_auth
		$this->addElement('text', 'ID_auth', array(
			'label'      => 'ID_auth',
			'required'   => false,
			'filters'    => array('StringTrim')
		));	
		// User
		$this->addElement('text', 'ID_user', array(
			'label'      => 'ID_user',
			'required'   => false,
			'filters'    => array('StringTrim')
		));	
        			// Avatar
		$this->addElement('text', 'ID_avatar_file', array(
			'label'      => 'ID_avatar_file',
			'required'   => false,
			'filters'    => array('StringTrim')
		));			

		// Companies
		$this->addElement('text', 'ID_company', array(
			'label'      => 'ID_company',
			'required'   => false,
			'filters'    => array('StringTrim')
		));

		// Email
		$this->addElement('text', 'user_email', array(
			'label'      => 'Email',
			'required'   => true,
			'filters'    => array('StringTrim'),
			'validators' => array('EmailAddress')
		));
		$validatorEmail = new Zend_Validate_Db_NoRecordExists('user', 'user_email');
		$validatorEmail->setMessage("Email %value% already registered", Zend_Validate_Db_NoRecordExists::ERROR_RECORD_FOUND);
		$this->getElement('user_email')->addValidator($validatorEmail);
		// First Name
		$this->addElement('text', 'user_firstname', array(
			'label'      => 'First Name',
			'required'   => true,
			'filters'    => array('StringTrim')
		));

		// Surname
		$this->addElement('text', 'user_surname', array(
			'label'      => 'Surname',
			'required'   => true,
			'filters'    => array('StringTrim')
		));
		
		
		/*
		 * Optional Fields
		 */
		// Password
		$this->addElement('password', 'user_password', array(
			'label'      => 'Password',
			'required'   => true,
			'filters'    => array('StringTrim'),

		));
		
		// Password Confirmation

		$this->addElement('password', 'password_conf', array(
			'label'      => 'Retype',
			'required'   => true,
			'filters'    => array('StringTrim'),

		));
		
		
		// Phone Extension
		$this->addElement('text', 'user_phone', array(
			'label'      => 'Phone Ext.',
			'required'   => false,
			'filters'    => array('StringTrim')
		));
		
		// Mobile
		$this->addElement('text', 'user_mobile', array(
			'label'      => 'Mobile',
			'required'   => false,
			'filters'    => array('StringTrim')
		));
		

		
		// Locale
		$this->addElement('text', 'user_locale', array(
			'label'      => 'Language',
			'required'   => false,
			'filters'    => array('StringTrim')
		));
		
		// Time Zone
		$this->addElement('text', 'user_timezone', array(
			'label'      => 'Time Zone',
			'required'   => false,
			'filters'    => array('StringTrim')
		));
        
	}
}