<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Product_Data extends Mage_Core_Helper_Abstract
{
    /**
    * Sets a collection of products to status: 'Not Created'
    *
    * @param Mage_Core_Controller_Request_Http $request
    */
    public function resetProducts(Mage_Core_Controller_Request_Http $request)
    {
        $ids = $request->__get('ids');

        if (!is_array($ids)) {
            $ids = array($ids);
        }

        foreach ($ids as $id) {
            if($id == '' || $id === null) {
                continue;
            }

            $this->resetProduct($id);
        }
    }

    public function resetProduct($id)
    {
        $notCreated     = Mage::getModel('bootic/product_data')->getStatusNotCreated();
        $productData    = Mage::getModel('bootic/product_data')->load($id);

        $productData->setMagentoProductId($id);
        $productData->setBooticStatus($notCreated);
        $productData->setBooticProductId(0);
        $productData->setBooticStockId(0);
        $productData->setIsInfoSynced(0);
        $productData->setIsStockSynced(0);
        $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
        $productData->save();
    }

    public function updateProductsStatus(array $ids, $status, $options = array())
    {
        foreach ($ids as $id) {
            if($id == '' || $id === null) {
                continue;
            }

            $productData = Mage::getModel('bootic/product_data')->load((int) $id);
            $productData->setMagentoProductId($id);
            $productData->setBooticStatus($status);
            $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
            $productData->addData($options);
            $productData->save();
        }
    }

    public function updateProductStatus(Mage_Catalog_Model_Product $product, $status, $options = array())
    {
        $productData = Mage::getModel('bootic/product_data')->load($product->getId());

        $productData->setMagentoProductId($product->getId());
        $productData->setBooticStatus($status);
        $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
        $productData->addData($options);
        $productData->save();

        if ($product->isConfigurable()) {
            $childrenIds = $product->getTypeInstance(true)->getChildrenIds($product->getId());
            $this->updateProductsStatus($childrenIds[0], $status, $options);
        }

        return $product;
    }

    public function isNotCreated(Mage_Catalog_Model_Product $product)
    {
        $productData = Mage::getModel('bootic/product_data')->load($product->getId());

        if ($productData->getData('bootic_status') == Mage::getModel('bootic/product_data')->getStatusNotCreated()) {
            return true;
        }

        return false;
    }

    public function setInfoSync(Mage_Catalog_Model_Product $product, $inSync = true)
    {
        $productData = Mage::getModel('bootic/product_data')->load($product->getId());

        $productData->setIsInfoSynced($inSync);
        $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
        $productData->save();

        return $product;
    }

    public function setStockSync(Mage_Catalog_Model_Product $product, $inSync = true)
    {
        $productData = Mage::getModel('bootic/product_data')->load($product->getId());

        $productData->setIsStockSynced($inSync);
        $productData->setUpdateTime(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));
        $productData->save();

        return $product;
    }

    public function isQueueable(Mage_Catalog_Model_Product $product)
    {
        $productData = Mage::getModel('bootic/product_data')->load($product->getId());

        $unvalidStatus = array(
            Mage::getModel('bootic/product_data')->getStatusProcessing(),
            Mage::getModel('bootic/product_data')->getStatusPendingApproval(),
            Mage::getModel('bootic/product_data')->getStatusNotApproved(),
            Mage::getModel('bootic/product_data')->getStatusCreated(),
        );

        if (!in_array($productData->getData('bootic_status'), $unvalidStatus)) {
            return true;
        }

        return false;
    }
}
