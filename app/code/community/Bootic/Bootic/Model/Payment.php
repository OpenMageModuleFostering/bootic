<?php
class Bootic_Bootic_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    protected $_code  = 'bootic_payment_method';
    protected $_formBlockType = 'bootic/payment_form';
    protected $_infoBlockType = 'bootic/payment_info';
    
    public function getInformation() {
        return $this->getConfigData('information');
    }
    
    public function getAddress() {
        return $this->getConfigData('address');
    }
}