<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Cron extends Mage_Core_Model_Abstract
{
    public function uploadProducts()
    {
        Mage::log('uploading batch of products');
        Mage::helper('bootic/product')->uploadProducts();
    }

    public function editProducts()
    {
        Mage::log('editing batch of products');
        Mage::helper('bootic/product')->editProducts();
    }

    public function checkProductsStatus()
    {
        Mage::log('checking products status');
        Mage::helper('bootic/product')->checkProductsStatus();
    }

    public function syncProductsStocks()
    {
        Mage::log('syncing products stocks');
        Mage::helper('bootic/product')->syncProductsStocks();
    }
    
    public function fetchOrders()
    {
        Mage::log('processing pending orders');
        Mage::helper('bootic/orders')->processPendingOrders();
    }

    public function syncOrders()
    {
        Mage::log('syncing orders');
        Mage::helper('bootic/orders')->syncOrders();
    }
}
