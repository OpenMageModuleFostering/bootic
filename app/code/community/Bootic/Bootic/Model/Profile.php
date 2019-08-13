<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Profile extends Varien_Object
{
    public function validate()
    {
        $errors = array();

        if (!Zend_Validate::is($this->getName(), 'NotEmpty')) {
            $errors[] = Mage::helper('bootic')->__('Username can\'t be empty');
        }

        if (!Zend_Validate::is($this->getCountry(), 'NotEmpty')) {
            $errors[] = Mage::helper('bootic')->__('Country can\'t be empty');
        }

        if (!Zend_Validate::is($this->getRegion(), 'NotEmpty')) {
            $errors[] = Mage::helper('bootic')->__('Region can\'t be empty');
        }

        if ($this->getPost_code() != '' && !Zend_Validate::is($this->getPost_code(), 'Int')) {
            $errors[] = Mage::helper('bootic')->__('Postal code has to be an integer');
        }

        if ($this->getPhone_code() != '' && !Zend_Validate::is($this->getPhone_number(), 'Alnum')) {
            $errors[] = Mage::helper('bootic')->__('Phone number is not valid');
        }

        // picture validation
        if (null !== $this->getPicture()) {
            $pictureValidators = new Zend_Validate();
            $pictureValidators
                ->addValidator(new Zend_Validate_File_FilesSize('10MB'), true)
                ->addValidator(new Zend_Validate_File_IsImage(array('image/jpeg', 'image/png')), true)
//            TODO: validate for a minimum and maximum size ??
//                ->addValidator(new Zend_Validate_File_ImageSize(array('minwidth' => '240', 'minheight' => '240')))
            ;

            if (!$pictureValidators->isValid($this->getPicture())) {
                $errors[] = Mage::helper('bootic')->__(
                    'The selected file for profile picture is not a valid or a supported image.'
                   .' The accepted formats are jpeg and png.'
                );
            }
        }


        if (empty($errors)) {
            return true;
        }

        return $errors;
    }
}
