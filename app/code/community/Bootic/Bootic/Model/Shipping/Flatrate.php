<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */
class Bootic_Bootic_Model_Shipping_Flatrate extends Mage_Shipping_Model_Carrier_Abstract implements Mage_Shipping_Model_Carrier_Interface
{
    protected $_code = 'bootic';
    protected $_isFixed = true;
    
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (Mage::registry('bootic_order_creation') ) {

            $result = Mage::getModel('shipping/rate_result');

            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));
            $method->setMethod('flat_shipping');
            $method->setMethodTitle($this->getConfigData('name'));
            $method->setPrice(0);
            $method->setCost(0);

            $result->append($method);

            return $result;
        }
    }
    
    public function getAllowedMethods()
    {
        return array('flat_shipping'=>$this->getConfigData('name'));
    }
    
    public function isShippingLabelsAvailable()
    {
        return false;
    }
}
