<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Adminhtml_AbstractController extends Mage_Adminhtml_Controller_action
{
    public function preDispatch()
    {
        parent::preDispatch();

        $request = $this->getRequest();
        $controller = $request->getControllerName();

        if (!$this->isBooticAccountConfigured() || !$this->isBooticAccountValid())
        {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            $this->_getSession()->addError(Mage::helper('bootic')->__('You must create an account or provide your email and password before using the Bootic extension.'));
            $this->_redirect('adminhtml/system_config/edit', array('section' => 'bootic'));

            return;
        } elseif (!$this->isBooticProfileConfigured() && $controller != 'adminhtml_connect') {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            if ($controller != 'adminhtml_storefront') {
                $this->_getSession()->addError(Mage::helper('bootic')->__('You must fill in your profile before being able to sell products on Bootic.'));
            } else {
                $this->_getSession()->addError(Mage::helper('bootic')->__('You must fill in your profile before creating your storefront.'));
            }

            $this->_redirect('bootic/adminhtml_connect/index');
        // If storefront has not been created yet, we take user to the storefront page
        } elseif (!$this->isBooticStorefrontCreated() && ($controller != 'adminhtml_connect' && $controller != 'adminhtml_storefront')) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);

            $this->_getSession()->addError(Mage::helper('bootic')->__('You must create a storefront to sell products on Bootic.'));
            $this->_redirect('bootic/adminhtml_storefront/new');
        }
    }

    protected function isBooticAccountConfigured()
    {
        $_accountEmail = Mage::getStoreConfig('bootic/account/email');
        $_accountPassword = Mage::getStoreConfig('bootic/account/password');

        if (
            !isset($_accountEmail)
            || $_accountEmail == null
            || !isset($_accountPassword)
            || $_accountPassword == null
        ) {
            return false;
        }

        return true;
    }

    protected function isBooticAccountValid()
    {
        $_accountEmail = Mage::getStoreConfig('bootic/account/email');
        $_accountPassword = Mage::getStoreConfig('bootic/account/password');

        //TODO Change this, no need to query API?
        try {
            $_bootic = new Bootic_Api_Client(new Zend_Rest_Client());
            $_bootic->authenticateByEmailAndPassword($_accountEmail, $_accountPassword);

            return true;
        } catch (Bootic_Api_Exception $e) {
            return false;
        }
    }

    protected function isBooticProfileConfigured()
    {
        $_profileUpdated = Mage::getStoreConfig('bootic/account/profile_updated');

        if ($_profileUpdated === null) {
            // Check if user profile has been filled in
            $profile = Mage::helper('bootic/connect')->getProfile();

            $mageConfig = new Mage_Core_Model_Config();

            // Profile has never be filled in on Bootic, user has to do it
            if ($profile->getData('demo') == true) {
                $_profileUpdated = false;

                $mageConfig->saveConfig('bootic/account/profile_updated', 0, 'default', 0);

            // Profile has been filled in bootic
            } else {
                $_profileUpdated = false;

                $mageConfig->saveConfig('bootic/account/profile_updated', true, 'default', 0);
            }

            // Here we have to reinit configuration
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
        }

        return $_profileUpdated;
    }

    protected function isBooticStorefrontCreated()
    {
        $_storefrontCreated = Mage::getStoreConfig('bootic/account/storefront_created');

        if ($_storefrontCreated === null) {
            $result = Mage::helper('bootic/storefront')->getStoreFrontList();
            $stores = $result->getData();

            $mageConfig = new Mage_Core_Model_Config();

            if (!is_array($stores) || count($stores) == 0) {
                $_storefrontCreated = false;

                $mageConfig->saveConfig('bootic/account/storefront_created', 0, 'default', 0);

            } else {
                $_storefrontCreated = true;

                $mageConfig->saveConfig('bootic/account/storefront_created', true, 'default', 0);
            }

            // Here we have to reinit configuration
            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();
        }

        return $_storefrontCreated;
    }
}
