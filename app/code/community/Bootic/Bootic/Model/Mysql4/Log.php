<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Constructor
     */
    public function _construct()
    {
        $this->_init('bootic/log', 'id');
    }
}
