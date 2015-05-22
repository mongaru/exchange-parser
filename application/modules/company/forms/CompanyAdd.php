<?php

/**
 * class Company_Form_CompanyAdd
 *
 * @package     People
 * @subpackage  User Add
 * @author      Bart Jedi <b.jedi@amediacreative.com>
 * @link        www.amediacreative.com
 */
class Company_Form_CompanyAdd extends Zend_Form {

    /**
     * Form Init
     *
     * @param void
     * @return void
     */
    public function init() {
        $this->clearDecorators();

        // Sets the form method to POST
        $this->setMethod('POST');

        // Sets the form name
        $this->setName('company_form');

        $this->addElement('text', 'ID_company', array(
            'label' => 'ID_company',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));

        $this->addElement('textarea', 'company_name', array(
            'label' => 'CompanyName',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags')
        ));
        $this->addElement('text', 'company_type', array(
            'label' => 'company_type',
            'required' => true,
            'filters' => array('StringTrim', 'StripTags')
        ));

        
        $this->addElement('textarea', 'company_description', array(
            'label' => 'CompanyDescription',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));


    }

}