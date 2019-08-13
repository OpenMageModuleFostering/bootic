<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Mysql4_Product_Data extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_isPkAutoIncrement    = false;

    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
        $this->_init('bootic/product_data', 'magento_product_id');
    }
}
