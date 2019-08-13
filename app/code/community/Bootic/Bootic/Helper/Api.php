<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Api extends Bootic_Bootic_Helper_Abstract
{
    /**
     * The User Id returned by Bootic API
     *
     * @var int
     */
    protected $user_id;

    /**
     * Instantiates, authenticates and returns Bootic API Client
     */
    private function _getBootic($email, $password)
    {
        if (!self::$_bootic) {
            self::$_bootic = new Bootic_Api_Client(new Zend_Rest_Client());
            $result = self::$_bootic->authenticateByEmailAndPassword($email, $password);

            $this->user_id = $result->getData('user_id');
        }

        return self::$_bootic;
    }

    /**
     * Creates an Api Key for the extension to use
     *
     * @param $email
     * @param $password
     * @return array|mixed
     */
    public function createKey($email, $password)
    {
        $apiKeyName = 'magento';
        $apiKeyDescription = 'Magento:' . Mage::getVersion();

        $result = $this
            ->_getBootic($email, $password)
            ->createKey($apiKeyName, $apiKeyDescription);

        return $result->getData('api_key');
    }

    /**
     * Classic User Id getter
     *
     * @return int
     */
    public function getuserId()
    {
        return $this->user_id;
    }
}
