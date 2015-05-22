<?php

/**
 * class Company_Form_SetupAdd
 *
 * @package     People
 * @subpackage  Setup Add
 * @author      Adrian Ojeda <a.ojeda@amediacreative.com>
 * @link        www.amediacreative.com
 */
class Company_Form_SetupAdd extends Zend_Form {

    /**
     * Form Init
     *
     * @param void
     * @return void
     */
    public function init() {
        $this->clearDecorators();

        // Sets the form method to POST
        $this->setMethod('post');

        // Sets the form name
        $this->setName('FormSetupAdd');

        /*
         * Required Fields
         */


      // Disclamier
        $this->addElement('textarea', 'disclaimer_text', array(
            'label' => 'Disclaimer Text',
            'required' => false,
            'filters' => array('StringTrim', 'StripTags')
        ));

        // Add the submit button
        $this->addElement('submit', 'submit', array(
            'ignore' => true,
            'label' => 'Submit',
        ));
    }

}