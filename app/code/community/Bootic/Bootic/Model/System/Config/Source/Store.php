<?php

class Bootic_Bootic_Model_System_Config_Source_Store
{
    public function toOptionArray()
    {
        return $stores = Mage::getModel('core/store')
            ->getCollection()
            ->toOptionArray()
        ;
    }
}