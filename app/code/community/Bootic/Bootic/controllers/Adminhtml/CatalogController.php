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

class Bootic_Bootic_Adminhtml_CatalogController extends Bootic_Bootic_Adminhtml_AbstractController
{
    public function indexAction()
    {
        $collection = mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array('nin' => array('bundle', 'virtual', 'grouped')))
            ->addAttributeToFilter('status', 1)
        ;

        $session = Mage::getSingleton('adminhtml/session');
        foreach ($collection as $product) {
            if ($product->getBooticStatus() === Bootic_Bootic_Model_Product_Data::BOOTIC_STATUS_ERROR) {
                $session->addError(Mage::helper('bootic')->__('Some products have errors. Please look at the logs to fix the issues and add them to Bootic again.'));
                break;
            }
        }

        $this->_title($this->__('Bootic Catalog'));

        $this
            ->loadLayout()
            ->_setActiveMenu('bootic/bootic')
            ->renderLayout();
    }
}