<?php

/**
 * @package    CostSheet
 * @subpackage Model_DbTable
 * @author     Teddy Limousin <t.limousin@amediacreative.com>
 * @copyright  Copyright (c) 2005-2010 Amedia Creative Inc. (http://www.amediacreative.com)
 * @link       www.amediacreative.com    
 */
class Amedia_Model_DbTable_Privileges extends Zend_Db_Table_Abstract {

	/**
	 * The default table name
	 */
	protected $_name = 'privileges';
	protected $_primary = 'id_privileges';

}