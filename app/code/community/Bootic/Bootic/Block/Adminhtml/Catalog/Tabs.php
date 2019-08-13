<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Catalog_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct() {

        parent::__construct();
        $this->setId('bootic_catalog_tab');
        $this->setDestElementId('bootic_catalog_tab_content');
        $this->setTemplate('widget/tabshoriz.phtml');

    }

    protected function _beforeToHtml(){

        $this->addTab('general', array(
            'label' => Mage::helper('bootic')->__('General'),
            'content' => $this->getLayout()->createBlock('bootic/adminhtml_catalog_tab_general')->toHtml(),
            'active' => true
        ));

        $this->addTab('log', array(
            'label' => Mage::helper('bootic')->__('Log'),
            'content' => $this->getLayout()->createBlock('bootic/adminhtml_log_grid')->toHtml()
        ));

        return parent::_beforeToHtml();
    }
}
