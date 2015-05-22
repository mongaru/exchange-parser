<?php
// application/modules/people/models/DbTable/UserAuth.php
/**
 * User Auth
 *  
 * @author Ever Daniel
 * @version 
 */

class People_Model_DbTable_UserOptional extends Zend_Db_Table_Abstract {
	
	/**
	 * The default table name 
	 */
	protected $_name = 'user_optional';
	protected $_primary = 'ID_optional';
	protected $_referenceMap = array(
		'People_Model_DbTable_User' => array(
			'columns' => 'ID_user',
			'refTableClass' => 'People_Model_DbTable_User',
			'refColumns' => 'ID_user' 
		)
	);

}