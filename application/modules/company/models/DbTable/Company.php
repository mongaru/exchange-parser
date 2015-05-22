<?php
// application/modules/company/models/DbTable/Company.php


class Company_Model_DbTable_Company extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'company';
	protected $_primary = 'ID_company';
//	protected $_dependentTables = array();
//	protected $_referenceMap = array();

}