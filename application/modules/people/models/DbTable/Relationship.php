<?php
// application/modules/people/models/DbTable/Relationship.php
/**
 * Relationship
 *
 * @author Ever Daniel
 * @version
 */

class People_Model_DbTable_Relationship extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'relationship';
	protected $_primary = 'ID_rel';

}