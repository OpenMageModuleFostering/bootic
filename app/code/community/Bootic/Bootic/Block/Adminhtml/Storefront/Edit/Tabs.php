<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tabs extends Mage_Adminhtml_Block_Widget_Tabs
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('storefront_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(Mage::helper('bootic')->__('Storefront Information'));
    }

    protected function _beforeToHtml()
    {
        $helper = Mage::helper('bootic');

        $this->addTab('general_section', array(
            'label'     => $helper->__('General'),
            'title'     => $helper->__('General informations'),
            'content'   => $this->getLayout()->createBlock('bootic/adminhtml_storefront_edit_tab_general')->toHtml()
        ));

        $this->addTab('settings_section', array(
            'label'     => $helper->__('Settings'),
            'title'     => $helper->__('Settings'),
            'content'   => $this->getLayout()->createBlock('bootic/adminhtml_storefront_edit_tab_settings')->toHtml()
        ));

        $this->addTab('social_section', array(
            'label' => $helper->__('Social'),
            'title' => $helper->__('Social'),
            'content' => $this->getLayout()->createBlock('bootic/adminhtml_storefront_edit_tab_social')->toHtml()
        ));

        $this->addTab('Design_section', array(
            'label' => $helper->__('Design'),
            'title' => $helper->__('Design'),
            'content' => $this->getLayout()->createBlock('bootic/adminhtml_storefront_edit_tab_design')->toHtml()
        ));

        return parent::_beforeToHtml();
    }
}