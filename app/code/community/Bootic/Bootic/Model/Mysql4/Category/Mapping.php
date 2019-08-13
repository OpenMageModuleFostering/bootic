<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Category_Mapping extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement    = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('bootic/category_mapping', 'magento_category_id');
    }
}
