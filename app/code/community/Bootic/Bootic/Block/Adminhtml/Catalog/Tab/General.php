<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Catalog_Tab_General extends Mage_Adminhtml_Block_Widget
{
    public function __construct(){

        parent::__construct();
        $this->setHtmlId('general');
        $this->setTemplate('bootic/catalog/tab/general.phtml');

    }

    protected function _prepareLayout()
    {
        // Main Grid
        $block = $this->getLayout()->createBlock('bootic/adminhtml_product');
        $block->setTemplate('bootic/products.phtml');
        $this->setChild('bootic_products', $block);

        // last logs table
        $lastErrorsBlock = $this->getLayout()->createBlock('bootic/adminhtml_log_error');
        $lastErrorsBlock->setTemplate('bootic/log/error.phtml');
        $this->setChild('bootic_last_errors', $lastErrorsBlock);

    }
}
