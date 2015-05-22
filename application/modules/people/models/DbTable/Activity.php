<?php
// application/modules/people/models/DbTable/Activity.php
/**
 * Activity
 *  
 * @author Ever Daniel Barreto
 * @version $id$
 */

class People_Model_DbTable_Activity extends Zend_Db_Table_Abstract {
	
	/**
	 * The default table name 
	 */
	protected $_name = 'activity';
	protected $_primary = 'ID_activity';

}