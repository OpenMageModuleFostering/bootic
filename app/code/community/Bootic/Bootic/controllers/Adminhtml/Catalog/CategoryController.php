<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Adminhtml_Catalog_CategoryController extends Mage_Adminhtml_Controller_action
{
    public function indexAction()
    {
        try {
            Mage::helper('bootic/category')->getRootCategory();
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')
                ->addError(Mage::helper('bootic')->__('Please select a Root Category and save the configuration.'))
                ->addData(array('bootic_category_redirect' => 'bootic/adminhtml_catalog_category/index'))
            ;

            $this->_redirect('adminhtml/system_config/edit', array('section' => 'bootic'));

            return;
        }

        $categoryCollection = Mage::getResourceModel('bootic/category_collection')->load();
        // We only fetch categories from Bootic if necessary - if they are already in place,
        // we let cron maintain then in sync
        if ($categoryCollection->count() == 0) {
            Mage::helper('bootic/category')->pullBooticCategories();
        }

        $collection = Mage::getResourceModel('bootic/category_mapping_collection')->load();

        $mapping = array();
        foreach ($collection as $match) {
            $mapping[$match->getId()] = $match->getBooticCategoryId();
        }

        Mage::register('category_mapping', $mapping);

        if ($email = Mage::getStoreConfig('bootic/account/email')) {
	       	$this->_title($this->__('Bootic Category Mapping (email: '.$email. ')'));
        } else {
        	$this->_title($this->__('Bootic Category Mapping'));
        }

        $this
            ->loadLayout()
            ->_setActiveMenu('bootic/bootic')
            ->renderLayout()
        ;
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $session = Mage::getSingleton('adminhtml/session');

            $mapping = Mage::getModel('bootic/category_mapping');
            foreach ($data['category'] as $magentoId => $booticId) {
                $mapping->setId($magentoId);
                $mapping->setBooticCategoryId($booticId);
                $mapping->save();
            }

            $session->addSuccess(Mage::helper('bootic')->__('Categories mapping was successfully saved'));
        }

        $this->_redirect('*/*/');
    }
}
