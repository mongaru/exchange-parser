<?php
// application/modules/people/models/DbTable/Subscriber.php
/**
 * Subscriber
 *  
 * @author Ever Daniel
 * @version 
 */

class People_Model_DbTable_Subscriber extends Zend_Db_Table_Abstract {
	
	/**
	 * The default table name 
	 */
	protected $_name = 'subscriber';
	protected $_primary = 'ID_subscriber';
	/*protected $_referenceMap = array(
		'People_Model_DbTable_User' => array(
			'columns' => 'ID_user',
			'refTableClass' => 'People_Model_DbTable_User',
			'refColumns' => 'ID_user' 
		)
	);*/

}