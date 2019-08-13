<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_System_Config_Observer extends Mage_Core_Model_Abstract
{
    public function redirectToCategoryMapping($observer)
    {
        if (Mage::getSingleton('adminhtml/session')->hasData('bootic_category_redirect')) {
            $redirectUrl = Mage::getSingleton('adminhtml/url')->getUrl(Mage::getSingleton('adminhtml/session')->getData('bootic_category_redirect'));
            Mage::getSingleton('adminhtml/session')->unsetData('bootic_category_redirect');
            return $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
        }
    }
}
