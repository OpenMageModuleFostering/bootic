<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_Settings
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('storefront_');

        $fieldset = $form->addFieldset('settings_fieldset', array('legend' => Mage::helper('bootic')->__('Settings')));

        $fieldset->addField('free_delivery_at_and_above', 'text', array(
            'label' => Mage::helper('bootic')->__('Delivery free if purchase is higher than:'),
            'name' => 'free_delivery_at_and_above',
            'after_element_html' => '<strong>[USD]</strong>'
        ));

        $fieldset->addField('cumulative_delivery_cost', 'select', array(
            'label' => Mage::helper('bootic')->__('Cumulative delivery cost:'),
            'name' => 'cumulative_delivery_cost',
            'values' => array(
                0 => array(
                    'value' => 1,
                    'label' => 'Yes'
                ),
                1 => array(
                    'value' => 0,
                    'label' => 'No'
                )
            )
        ));

//        $fieldset->addField('transferable', 'select', array(
//            'label' => Mage::helper('bootic')->__('Transferable:'),
//            'name' => 'transferable',
//            'values' => array(
//                0 => array(
//                    'value' => 1,
//                    'label' => 'Yes'
//                ),
//                1 => array(
//                    'value' => 0,
//                    'label' => 'No'
//                )
//            )
//        ));

        $fieldset->addField('indexable', 'select', array(
            'label' => Mage::helper('bootic')->__('Indexable:'),
            'name' => 'indexable',
            'values' => array(
                0 => array(
                    'value' => 1,
                    'label' => 'Yes'
                ),
                1 => array(
                    'value' => 0,
                    'label' => 'No'
                )
            )
        ));

        $form->setValues(Mage::getSingleton('bootic/storefront')->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Return Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('bootic')->__('Storefront Settings');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('bootic')->__('Storefront Settings');
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}
