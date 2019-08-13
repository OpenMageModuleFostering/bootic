<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_System_Config_Commission extends Mage_Core_Model_Config_Data
{
    public function save()
    {
        $commission = $this->getValue();
        $commission = preg_replace('#[^0-9]#','',$commission);
        if ((0 > (int)$commission) || (100 < (int)$commission)) {
            Mage::throwException("Commission is a percentage and its value must range from 0 to 100.");
        }

        return parent::save();
    }
}
