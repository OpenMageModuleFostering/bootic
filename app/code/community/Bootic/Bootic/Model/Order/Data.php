<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Order_Data extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('bootic/order_data');
    }
    /**
     * get transactions
     * @return array
     */
    public function getTransactions()
    {
        return explode(',', $this->_getData('transactions'));
    }
    /**
     * set order transactions
     * @param array $transactions
     * @return Bootic_Bootic_Model_Order_Data
     */
    public function setTransactions(array $transactions)
    {
        $this->setData('transactions', implode(',', $transactions));
        return $this;
    }
    /**
     * before save
     * @see Mage_Core_Model_Abstract::_beforeSave
     */
    protected function _beforeSave()
    {
        $this->setUpdatedAt(date('Y-m-d H:i:s', Mage::getModel('Core/Date')->timestamp()));
    }
}