<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Account extends Bootic_Bootic_Helper_Abstract
{
    /**
     * Creates an account on Bootic, this method is different from the rest of the API
     * as we don't need to authenticate before querying
     *
     * @param $email
     * @param $password
     * @return Bootic_Api_Result
     */
    public function createAccount($email, $password)
    {
        $_bootic = new Bootic_Api_Client(new Zend_Rest_Client());
        $result = $_bootic->createAccount($email,$password);

        return $result;
    }
}
