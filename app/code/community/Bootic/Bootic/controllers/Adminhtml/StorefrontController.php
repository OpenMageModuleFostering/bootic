<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Bootic
 * @package     Bootic_Bootic
 * @copyright   Copyright (c) 2012 Bootic (http://www.bootic.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

require_once('Bootic/Bootic/controllers/Adminhtml/AbstractController.php');

class Bootic_Bootic_Adminhtml_StorefrontController extends Bootic_Bootic_Adminhtml_AbstractController
{
    protected function _init()
    {
        $this->_title($this->__('Bootic Storefront'));

        $this
            ->loadLayout()
            ->_setActiveMenu('bootic/bootic')
            ->_addBreadcrumb(Mage::helper('bootic')->__('Storefront'), Mage::helper('bootic')->__('Storefront'));

        $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);

        return $this;
    }

    public function indexAction()
    {
        $result = Mage::helper('bootic/storefront')->getStoreFrontList();

        $shops = $result->getData();
        if (count($shops) > 0) {
            $shop = reset($shops);
            $options = Mage::helper('bootic/storefront')->getStoreFrontOptions($shop['shop_id']);
            $shop['banners'] = $options->getData('banners');
            Mage::getSingleton('bootic/storefront')->addData($shop);
        } else {
            $this->_redirect('*/*/new');
            return;
        }

        $this->_init();
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_init();
        $this->renderLayout();
    }

    public function createAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $session = Mage::getSingleton('adminhtml/session');

            try {
                $result =  Mage::helper('bootic/storefront')->createStorefront($data);

                if (!$result->isSuccess()) {
                    $session->addError($result->getErrorMessage());
                    $this->_redirect('*/*/');
                    return;
                }

                $mageConfig = new Mage_Core_Model_Config();
                $mageConfig->saveConfig('bootic/account/storefront_created', true, 'default', 0);
                $mageConfig->saveConfig('bootic/account/storefront_id', $result->getData('shop_id'), 'default', 0);

                // Here we have to reinit configuration
                Mage::getConfig()->reinit();
                Mage::app()->reinitStores();
                $session->addSuccess(Mage::helper('bootic')->__('Storefront was successfully created'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
                $this->_redirect('*/*/');
                return;
            }
        }

        $this->_redirect('*/*/');
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $session = Mage::getSingleton('adminhtml/session');
            $storefront = Mage::getSingleton('bootic/storefront')->addData($data);

            if (empty($data['shop_id'])) {

                try {
                    $result =  Mage::helper('bootic/storefront')->createStorefront($storefront->toArray());

                    if (!$result->isSuccess()) {
                        $session->addError($result->getErrorMessage());
                        $this->_redirect('*/*/');
                        return;
                    }

                    $session->addSuccess(Mage::helper('bootic')->__('Storefront was successfully saved'));
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirect('*/*/');
                    return;
                }
            } else {

// @TODO: add the delete functionality, reset banner to a default one
//            if (isset($data['banner']['delete']) && $data['banner']['delete'] == 1) {
//                $bootic->editProfilePicture(array('use_default_avatar' => true));
//                unset($data['picture']);
//            }

                if (isset($_FILES['banner_url']['name']) && (file_exists($_FILES['banner_url']['tmp_name']))) {
                    try {
                        $im = file_get_contents($_FILES['banner_url']['tmp_name']);
                        $imdata = base64_encode($im);

                        $result =  Mage::helper('bootic/storefront')->addStorefrontBanner(array(
                            'image_base64' => $imdata,
                            'shop_id' => $data['shop_id']
                        ));

                        if (!$result->isSuccess()) {
                            $session->addError($result->getErrorMessage());
                            $this->_redirect('*/*/');
                            return;
                        }

                        $storefront->setData('banner', $result->getData('banner'));

                    } catch (Exception $e) {
                        // TODO: better error than that
                        $session->addError(sprintf('An error occured during picture processing with error : %s', $e->getMessage()));
                    }
                }

                $validate = $storefront->validate();
                if ($validate === true) {
                    try {
                        $result = Mage::helper('bootic/storefront')->updateStorefront($storefront->toArray());

                        if (!$result->isSuccess()) {
                            $session->addError($result->getErrorMessage());
                            $this->_redirect('*/*/');
                            return;
                        }

                        $session->addSuccess(Mage::helper('bootic')->__('Storefront was successfully saved'));

                        // check if 'Save and Continue'
                        if ($this->getRequest()->getParam('back')) {
                            $this->_redirect('*/*/', array('_current' => true));
                            return;
                        }

                        $this->_redirect('*/*/');
                        return;
                    } catch (Exception $e) {
                        $session->addError($e->getMessage());
                        $this->_redirect('*/*/');
                        return;
                    }
                } else {
                    $session->setFormData($data);
                    if (is_array($validate)) {
                        foreach ($validate as $errorMessage) {
                            $session->addError($errorMessage);
                        }
                    } else {
                        $session->addError($this->__('Unable to create your storefront.'));
                    }
                }
            }


        }

        $this->_redirect('*/*/');
    }
}