<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Storefront extends Varien_Object
{
    public function validate()
    {
        $errors = array();

        if (!Zend_Validate::is($this->getName(), 'NotEmpty')) {
            $errors[] = Mage::helper('bootic')->__('Storefront name can\'t be empty');
        }

        if (empty($errors)) {
            return true;
        }
        return $errors;
    }
}
