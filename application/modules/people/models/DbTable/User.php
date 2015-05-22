<?php
// application/modules/people/models/DbTable/User.php
/**
 * User
 *  
 * @author Ever Daniel
 * @version 
 */

class People_Model_DbTable_User extends Zend_Db_Table_Abstract {
	
	/**
	 * The default table name 
	 */
	protected $_name = 'user';
	protected $_primary = 'ID_user';
	protected $_dependentTables = array(
		'People_Model_DbTable_UserAuth',
		'People_Model_DbTable_UserOptional',
		'Yield_Model_DbTable_Yield'
	);
	protected $_referenceMap = array(
		'Yield_Model_DbTable_Yield' => array(
			'columns' => 'ID_user',
			'refTableClass' => 'Yield_Model_DbTable_Yield',
			'refColumns' => 'ID_user' 
		)
	);

}