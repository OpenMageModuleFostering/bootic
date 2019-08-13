<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Storefront extends Bootic_Bootic_Helper_Abstract
{
    public function getStoreFrontList()
    {
        return $this->getBootic()->getStoreFrontList();
    }

    public function createStorefront(array $data)
    {
        // Our storefront is always created in online state
        $data['online'] = true;

        return $this->getBootic()->createStorefront($data);
    }

    public function updateStoreFront(array $data)
    {
        return $this->getBootic()->updateStorefront($data);
    }

    public function addStorefrontBanner(array $data)
    {
        return $this->getBootic()->addStorefrontBanner($data);
    }

    public function getStoreFrontOptions($storeFrontId)
    {
        return $this->getBootic()->getAvailableOptionsForStorefront($storeFrontId);
    }

    public function getAvailableTemplatesValues()
    {
        $result     = $this->getBootic()->getCommonList('template');
        $templates  = $result->getData();

        $values = array();
        foreach ($templates as $key => $template) {
            $values[$key] = array(
                'value' => $template['name'],
                'label' => $template['title']
            );
        }

        return $values;
    }
}
