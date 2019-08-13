<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Message extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement    = false;

    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('bootic/message', 'bootic_message_id');
    }
}
