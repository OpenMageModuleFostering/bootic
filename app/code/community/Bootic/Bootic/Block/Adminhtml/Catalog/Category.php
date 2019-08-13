<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Catalog_Category extends Mage_Adminhtml_Block_Widget_Container
{
    public function __construct(){

        parent::__construct();

        if (!$this->hasData('template')) {
            $this->setTemplate('bootic/catalog/category.phtml');
        }

        $this->_addButton('reset', array(
            'label'     => Mage::helper('adminhtml')->__('Reset'),
            'onclick'   => 'setLocation(window.location.href)',
        ), -1);

        $this->_addButton('save', array(
            'label'     => Mage::helper('adminhtml')->__('Save'),
            'onclick'   => 'editForm.submit();',
            'class'     => 'save',
        ), 1);

    }

    public function getHeaderText()
    {
        $email = Mage::getStoreConfig('bootic/account/email');
        if ($email) {
        	return Mage::helper('bootic')->__('Match Magento categories with Bootic categories (email: '.$email.')');
	} else {
        	return Mage::helper('bootic')->__('Match Magento categories with Bootic categories');
	}
    }

    /**
   	 * Return Magento categories
   	 */
   	public function getMagentoCategories()
   	{
   		return Mage::helper('bootic/category')->getMagentoCategories();
   	}

    public function getJsonFormattedBooticCategories()
    {
        $json =  Mage::helper('bootic/category')->getJsonFormattedBooticCategories();

        return $json;
    }

    public function getCategoryMapping()
    {
        return Mage::registry('category_mapping');
    }
}
