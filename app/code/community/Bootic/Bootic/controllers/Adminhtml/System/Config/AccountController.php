<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Adminhtml_System_Config_AccountController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Displays the creation form
     *
     * @return void
     */
    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Configuration'));

        $current = 'bootic';
        $website = $this->getRequest()->getParam('website');
        $store   = $this->getRequest()->getParam('store');

        Mage::getSingleton('adminhtml/config_data')
            ->setSection($current)
            ->setWebsite($website)
            ->setStore($store);

        $this->loadLayout();

        $this->_setActiveMenu('system/config');
        $this->getLayout()->getBlock('menu')->setAdditionalCacheKeyInfo(array($current));

        $this->_addBreadcrumb(Mage::helper('adminhtml')->__('System'), Mage::helper('adminhtml')->__('System'),
            $this->getUrl('*/system'));

        $this->getLayout()->getBlock('left')
            ->append($this->getLayout()->createBlock('bootic/adminhtml_system_config_tabs')->initTabs());

            $this->renderLayout();
    }

    public function createAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $email                  = $data['email'];
            $password               = $data['password'];
            $passwordConfirmation   = $data['password_confirmation'];
            $sellingAgreement       = $data['selling_agreement'];

            $validate               = true;
            $validationErrors       = array();
            $session                = Mage::getSingleton('adminhtml/session');

            $validator = new Zend_Validate_EmailAddress();
            if (!$validator->isValid($email)) {
                $validate = false;
                foreach ($validator->getMessages() as $message) {
                    $validationErrors[] = $message;
                }
            }

            if (strlen($password) < 4) {
                $validate = false;
                $validationErrors[] = Mage::helper('bootic')->__('Password is too short.');
            }

            if ($password !== $passwordConfirmation) {
                $validate = false;
                $validationErrors[] = Mage::helper('bootic')->__('Passwords don\'t match.');
            }

            if (!isset($sellingAgreement)) {
                $validate = false;
                $validationErrors[] = Mage::helper('bootic')->__('Please accept our terms and conditions.');
            }

            if ($validate === true) {
                try {
                    /** @var Bootic_Api_Result $result  */
                    $result = Mage::helper('bootic/account')->createAccount($email, $password);

                    if (!$result->isSuccess()) {
                        $session->setFormData(array('email' => $email));
                        $errorMessage = $result->getErrorMessage();

                        foreach ((array) $errorMessage as $message) {
                            $session->addError($message);
                        }
                    } else {
                        $userId = $result->getData('new_user_id');

                        // We create a new API Key
                        // TODO Rollback user creation if this step fails?
                        $apiKey = Mage::helper('bootic/api')->createKey($email, $password);

                        $mageConfig = new Mage_Core_Model_Config();
                        $mageConfig->saveConfig('bootic/account/email', $email, 'default', 0);
                        $mageConfig->saveConfig('bootic/account/password', $password, 'default', 0);
                        $mageConfig->saveConfig('bootic/account/user_id', $userId, 'default', 0);
                        $mageConfig->saveConfig('bootic/account/api_key', $apiKey, 'default', 0);
                        $mageConfig->saveConfig('bootic/account/connect', 'new', 'default', 0);
                        $mageConfig->saveConfig('bootic/account/profile_updated', false, 'default', 0);
                        $mageConfig->saveConfig('bootic/account/storefront_created', false, 'default', 0);

                        // Here we have to reinit configuration
                        Mage::getConfig()->reinit();
                        Mage::app()->reinitStores();

                        $session->addSuccess(Mage::helper('bootic')->__('Your account was successfully created on Bootic. Please update your profile now.'));
                        $session->addData(array('account_creation' => true));
                        $this->_redirect('bootic/adminhtml_connect/index');

                        return;
                    }
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirect('*/*/');

                    return;
                }
            } else {
                $session->setFormData(array('email' => $email));
                if (is_array($validationErrors)) {
                    foreach ($validationErrors as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                } else {
                    $session->addError($this->__('Unable to create your account.'));
                }
            }
        }

        $this->_redirect('*/*/');
    }
}
