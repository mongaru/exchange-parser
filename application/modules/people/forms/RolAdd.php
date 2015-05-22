<?php
/**
 * @package    CostSheet
 * @subpackage Form
 * @author     Alvaro Mercado 
 * @copyright  Copyright (c) 2005-2010 Amedia Creative Inc. (http://www.amediacreative.com)
 * @link       www.amediacreative.com    
 */

class People_Form_RolAdd extends Zend_Form {
	public function init() {
		$this->setMethod('post');
		$this->setName('RoleForm');
 

		// RolName #
        $this->addElement('text', 'iRoleName', array(
            'label' => 'rolName',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags')
        ));
        // RolName #
        $this->addElement('text', 'department', array(
            'label' => 'department',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags')
        ));        


        $this->addElement('text', 'checks_user[]', array(
            'label' => 'checks_user',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));

        $this->addElement('text', 'checks_sales[]', array(
            'label' => 'checks_sales',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));

        $this->addElement('text', 'checks_discussions[]', array(
            'label' => 'checks_discussions',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));        

        $this->addElement('text', 'checks_costing[]', array(
            'label' => 'checks_costing',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));
        $this->addElement('text', 'checks_setup[]', array(
            'label' => 'checks_setup',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));

        $this->addElement('text', 'checks_costsheet[]', array(
            'label' => 'checks_costsheet',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));
        	}
}
?>