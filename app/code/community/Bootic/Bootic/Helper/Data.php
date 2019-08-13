<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Data extends Bootic_Bootic_Helper_Abstract
{
    public function testApiConnection($email, $password)
    {
        // Default response
        $response = new Varien_Object(array(
            'is_connected' => false,
            'request_success' => false
        ));

        if (!$this->canTestApiConnection($email)) {
            return $response;
        }

        $booticClient = new Bootic_Api_Client(new Zend_Rest_Client());
        try {
            $result = $booticClient->authenticateByEmailAndPassword($email, $password);

            $auth = $result->getData('auth');

            if (empty($auth)) {
                $response->setIsConnected(false);
            } else {
                $response->setIsConnected(true);
                $response->setRequestSuccess(true);
            }
        } catch (\Exception $e) {
            $response->setIsConnected(false);
        }

        return $response;
    }

    /**
     * Check if parameters are valid to test API connection
     *
     * @param string $emai
     *
     * @return boolean
     */
    public function canTestApiConnection($email)
    {
        $result = true;

        // TODO check if email is properly formatted
        if (!is_string($email)) {
            $result = false;
        }

        return $result;
    }
}
