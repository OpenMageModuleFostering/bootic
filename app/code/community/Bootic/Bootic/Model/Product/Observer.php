<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Product_Observer extends Mage_Core_Model_Abstract
{
    public function updateBooticProductInfo($observer)
    {
        $product = $observer->getProduct();
        $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        // if product is configurable or does not have changes
        if (count($parents) > 0 || !$product->hasDataChanges()) {
            return;
        }

        $productData = Mage::getModel('bootic/product_data')->load($product->getId());
        if (
            (
                $productData->getBooticStatus() == Mage::getModel('bootic/product_data')->getStatusCreated() ||
                $productData->getBooticStatus() == Mage::getModel('bootic/product_data')->getStatusPendingApproval()
            )
            && $productData->getIsInfoSynced() == true
        ) {
            Mage::helper('bootic/product_data')->setInfoSync($product, false);
        }
    }
}
