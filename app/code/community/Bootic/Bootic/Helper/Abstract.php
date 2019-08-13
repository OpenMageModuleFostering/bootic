<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

abstract class Bootic_Bootic_Helper_Abstract extends Mage_Core_Helper_Abstract
{
    /** @var Bootic_Api_Client */
    protected static $_bootic;

    /**
     * Instantiates, authenticates and returns Bootic API Client
     */
    public function getBootic()
    {
        if (!self::$_bootic) {
            self::$_bootic = new Bootic_Api_Client(new Zend_Rest_Client());
            self::$_bootic->authenticateByApiKey(
                Mage::getStoreConfig('bootic/account/api_key')
            );
        }

        return self::$_bootic;
    }
}
