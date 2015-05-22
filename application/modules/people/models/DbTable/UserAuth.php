<?php
// application/modules/people/models/DbTable/UserAuth.php
/**
 * User Auth
 *  
 * @author Ever Daniel
 * @version 
 */

class People_Model_DbTable_UserAuth extends Zend_Db_Table_Abstract {
	
	/**
	 * The default table name 
	 */
	protected $_name = 'user_auth';
	protected $_primary = 'ID_auth';
	protected $_referenceMap = array(
		'People_Model_DbTable_User' => array(
			'columns' => 'ID_user',
			'refTableClass' => 'People_Model_DbTable_User',
			'refColumns' => 'ID_user' 
		)
	);

}