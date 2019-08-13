<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Adminhtml_Catalog_ProductController extends Mage_Adminhtml_Controller_action
{
    public function massAddProductsAction()
    {
        try {
            Mage::helper('bootic/product')->massCreateProducts($this->getRequest());
            $this->_redirectReferer();
        } catch(Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage().' : '.$e->getTraceAsString());
            $this->_redirectReferer();
        }
    }

    public function massResetProductsAction()
    {
        Mage::helper('bootic/product_data')->resetProducts($this->getRequest());
        Mage::getSingleton('adminhtml/session')->addSuccess('Products set to Not Created.');
        $this->_redirectReferer();
    }
}
