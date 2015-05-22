<?php
/**
 * Amedia Creative
 *
 * @category   Amedia
 * @package    Amedia_Validate
 * @copyright  Copyright (c) 2011 Amedia Creative Inc. (http://www.amediacreative.com)
 * @version    $Id$
 */

/**
 * @see Zend_Validate_Db_Abstract
 */
require_once 'Zend/Validate/Db/Abstract.php';

/**
 * Confirms a record does not exist in a table.
 *
 * @category   Amedia
 * @package    Amedia_Validate
 * @uses       Zend_Validate_Db_Abstract
 */
class Amedia_Validate_Costing_PackExists extends Zend_Validate_Db_Abstract
{
    const PACK_EXISTS    = 'packExists';

    /**
     * @var array Message templates
     */
    protected $_messageTemplates = array(
        self::PACK_EXISTS    => "There's already a Costing Pack with the name <a href=\"/costing/manage/view/id/%ID_pack%\" target=\"_blank\">%pack_name%</a>."
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'ID_pack' => '_ID_pack',
        'pack_name' => '_pack_name'
    );

    /**
     * Pack ID
     *
     * @var mixed
     */
    protected $_ID_pack;

    /**
     * Pack Name
     *
     * @var mixed
     */
    protected $_pack_name;

    public function getID_pack()
    {
        return $this->_ID_pack;
    }

    public function setID_pack($ID_pack)
    {
        $this->_ID_pack = $ID_pack;
        return $this;
    }

    public function getPack_name()
    {
        return $this->_pack_name;
    }

    public function setPack_name($pack_name)
    {
        $this->_pack_name = $pack_name;
        return $this;
    }

    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  String $value
     * @return Array when matches are found.
     */
    protected function _query($value)
    {
        /**
         * Check for an adapter being defined. if not, fetch the default adapter.
         */
        if ($this->_adapter === null) {
            $this->_adapter = Zend_Db_Table_Abstract::getDefaultAdapter();
            if (null === $this->_adapter) {
                require_once 'Zend/Validate/Exception.php';
                throw new Zend_Validate_Exception('No database adapter present');
            }
        }

        /**
         * Build select object
         */
        $select = new Zend_Db_Select($this->_adapter);
        $select->from($this->_table)
               ->where($this->_adapter->quoteIdentifier($this->_field).' = ?', $value);
        if ($this->_exclude !== null) {
            if (is_array($this->_exclude)) {
                $select->where($this->_adapter->quoteIdentifier($this->_exclude['field']).' != ?', $this->_exclude['value']);
            } else {
                $select->where($this->_exclude);
            }
        }
        $select->limit(1);

        /**
         * Run query
         */
        $result = $this->_adapter->fetchRow($select, array(), Zend_Db::FETCH_ASSOC);

        return $result;
    }
    
    public function isValid($value)
    {
        $valid = true;
        $this->_setValue($value);

        $result = $this->_query($value);
        if ($result) {
            $valid = false;
            $this->setID_pack($result['ID_pack']);
            $this->setPack_name($result['pack_name']);
            $this->_error(self::PACK_EXISTS);
        }

        return $valid;
    }
}
