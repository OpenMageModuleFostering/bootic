<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Session_Observer extends Mage_Core_Model_Abstract
{
    /**
     * Upon login, let's pull unread messages
     */
    public function pullUnreadMessages()
    {
        Mage::helper('bootic/message')->pullUnreadMessages();
    }
}
