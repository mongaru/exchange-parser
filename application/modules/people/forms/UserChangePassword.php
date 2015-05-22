<?php
/**
 * class People_Form_UserChangePasswod
 *
 * @package     People
 * @subpackage  User Change Password
 * @author      Ever Daniel Barreto <e.barreto@amediacreative.com>
 * @link        www.amediacreative.com
 */
class People_Form_UserChangePassword extends Zend_Form
{
	/**
     * Form Init
     *
     * @param void
     * @return void
     */
	public function init()
	{
		$this->clearDecorators();
		
		// Sets the form method to POST
		$this->setMethod('post');
		
		// Sets the form name
		$this->setName('FormUserChangePassword');
				
		/*
		 * Required Fields
		 */
		
		// ID User
		$this->addElement('text', 'ID_user', array(
			'label'      => 'ID User',
			'required'   => true,
			'filters'    => array('StringTrim')
		));
		
		
		// Password
		$this->addElement('password', 'user_password', array(
			'label'      => 'Password',
			'required'   => true,
			'filters'    => array('StringTrim')
		));
		
		// Password Confirmation
		$this->addElement('password', 'password_conf', array(
			'label'      => 'Retype',
			'required'   => true,
			'filters'    => array('StringTrim')
		));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
        	'ignore'     => true,
            'label'      => 'Submit',
        ));
        
        
	}
}