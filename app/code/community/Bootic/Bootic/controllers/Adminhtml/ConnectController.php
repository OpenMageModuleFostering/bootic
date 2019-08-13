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

class Bootic_Bootic_Adminhtml_ConnectController extends Bootic_Bootic_Adminhtml_AbstractController
{

    public function indexAction()
    {
        $email = Mage::getStoreConfig('bootic/account/email');
        if ($email) {
        	$this->_title($this->__('Bootic Connect (email: '.$email.')'));
	} else {
        	$this->_title($this->__('Bootic Connect'));
	}
        $this
            ->loadLayout()
            ->_setActiveMenu('bootic/bootic')
            ->_addBreadcrumb(Mage::helper('bootic')->__('Profile'), Mage::helper('bootic')->__('Profile'))
            ;

        $session = Mage::getSingleton('adminhtml/session');
        $_p = $session->hasData('form_data') ? $session->getFormData() : Mage::helper('bootic/connect')->getProfile()->getData();

        $profile = Mage::getSingleton('bootic/profile');
        $profile->addData($_p);

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {

            $session = Mage::getSingleton('adminhtml/session');

            $profile = Mage::getSingleton('bootic/profile');

            if (isset($data['picture']['delete']) && $data['picture']['delete'] == 1) {
                Mage::helper('bootic/connect')->editProfilePicture(array('use_default_avatar' => true));
                unset($data['picture']);
            } else {
                $uploader = new Bootic_File_Uploader('picture');
                $uploader
                    ->setAllowedExtensions(array('jpg', 'jpeg', 'png'))
                    ->setAllowRenameFiles(false)
                    ->setFilesDispersion(false)
                ;

                $profile->setPicture($uploader->getTempFileName());
                unset($data['picture']);
            }

            // If outside of the US, we force the region to the default empty value
            if ($data['country'] != 2)  {
                $data['region'] = 0;
            }

            $profile = Mage::getSingleton('bootic/profile')->addData($data);

            $validate = $profile->validate();
            if ($validate === true) {
                try {
                    $dataArray = $profile->toArray();
                    unset($dataArray['picture']);
                    $result = Mage::helper('bootic/connect')->editProfile($dataArray);

                    $success = true;
                    if (!$result->isSuccess()) {
                        $success = false;
                        $session->addError($result->getErrorMessage());
                    }

                    // updating picture requires a different API call
                    if (null !== $profile->getPicture()) {
                        $binary = file_get_contents($profile->getPicture());
                        $result = Mage::helper('bootic/connect')->editProfilePicture(array('image_base64' => base64_encode($binary)));
                        if (!$result->isSuccess()) {
                            $success = false;
                            $session->addError($result->getErrorMessage());
                        }
                    }

                    if (!$success) {
                        $this->_redirect('*/*/');

                        return;
                    }

                    $mageConfig = new Mage_Core_Model_Config();
                    $mageConfig->saveConfig('bootic/account/profile_updated', true, 'default', 0);

                    // Here we have to reinit configuration
                    Mage::getConfig()->reinit();
                    Mage::app()->reinitStores();

                    if ($session->hasData('account_creation')) {
                        $session->unsetData('account_creation');
                        $session->addSuccess(Mage::helper('bootic')->__('Profile was successfully saved. Please create a Storefront now.'));
                        $this->_redirect('bootic/adminhtml_storefront/index');
                    } else {
                        $session->addSuccess(Mage::helper('bootic')->__('Profile was successfully saved'));
                        $this->_redirect('*/*/');
                    }

                    return;
                } catch (Exception $e) {
                    $session->addError($e->getMessage());
                    $this->_redirect('*/*/');

                    return;
                }
            }
            else {
                $session->setFormData($data);
                if (is_array($validate)) {
                    foreach ($validate as $errorMessage) {
                        $session->addError($errorMessage);
                    }
                }
                else {
                    $session->addError($this->__('Unable to update your profile.'));
                }
            }
        }

        $this->_redirect('*/*/');
    }
}
