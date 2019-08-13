<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Category_Mapping_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Initialization
     */
    public function _construct()
    {
        $this->_init('bootic/category_mapping');
    }
}
