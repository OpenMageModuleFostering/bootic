<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_System_Config_Connection extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $email = $this->getFieldsetDataValue('email');
        $password = $this->getFieldsetDataValue('password');

        try {
            $apiKey = Mage::helper('bootic/api')->createKey($email, $password);
            $userId = Mage::helper('bootic/api')->getUserId();

            $mageConfig = new Mage_Core_Model_Config();
            $mageConfig->saveConfig('bootic/account/user_id', $userId, 'default', 0);
            $mageConfig->saveConfig('bootic/account/api_key', $apiKey, 'default', 0);
            $mageConfig->saveConfig('bootic/account/connect', 'existing', 'default', 0);
        } catch (Bootic_Api_Exception $e) {
            Mage::throwException(Mage::helper('bootic')->__('Your configuration settings could not be saved. Your Bootic login and passwords are not valid.'));
        }

        parent::save();
    }
}
