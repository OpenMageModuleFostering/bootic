<?php
/**
 * (c) Newcode <contact@newcodestudio.com>
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Preview extends Mage_Core_Block_Template
{
    public function getOptions()
    {
        $options = Mage::helper('bootic/storefront')->getStoreFrontOptions(Mage::getStoreConfig('bootic/account/storefront_id'));

        return $options->getData();
    }
}
