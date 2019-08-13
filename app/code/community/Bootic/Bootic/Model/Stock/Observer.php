<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Stock_Observer extends Mage_Core_Model_Abstract
{
    public function updateBooticStock($observer)
    {
        $productId = $observer->getItem()->getProductId();
        $this->_setProductToUnsynced($productId);
    }

    public function updateSoldProductBooticStock($observer)
    {
        $items = $observer->getOrder()->getAllItems();
        foreach ($items as $item) {
            // Configurable products have no stockID so no need to flag them as out of sync - ever
            $product = Mage::getModel('catalog/product')->load($item->getProductId());
            if (!$product->isConfigurable()) {
                $this->_setProductToUnsynced($item->getProductId());
            }
        }
    }

    protected function _setProductToUnsynced($id)
    {
        $productData = Mage::getModel('bootic/product_data')->load($id);

        if (
            (
                $productData->getBooticStatus() == Mage::getModel('bootic/product_data')->getStatusCreated() ||
                $productData->getBooticStatus() == Mage::getModel('bootic/product_data')->getStatusPendingApproval()
            )
            && $productData->getIsStockSynced() == true
        ) {
            $productData->setIsStockSynced(false);
            $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
            $productData->save();
        }
    }
}
