<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Message_Observer extends Mage_Core_Model_Abstract
{
    public function markMessageAsRead($observer)
    {
        $params = $observer->getControllerAction()->getRequest()->getParams();
        $messageId = $params['id'];

        $booticMessage = Mage::getModel('bootic/message')->load($messageId, 'magento_message_id');

        if (!$booticMessage->isObjectNew()) {
            Mage::helper('bootic/message')->markMessageAsRead($booticMessage->getId());
        }
    }
}
