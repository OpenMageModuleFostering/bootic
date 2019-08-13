<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_System_Config_Source_Attribute
{
    public function toOptionArray()
    {
        $collection = Mage::getResourceModel('catalog/product_attribute_collection')->addVisibleFilter();

        $options[] = array(
            'value' => '',
            'label' => '',
        );

        foreach ($collection as $attribute) {
            $options[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getName(),
            );
        }

        return $options;
    }
}
