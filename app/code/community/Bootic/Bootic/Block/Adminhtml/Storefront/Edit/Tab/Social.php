<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_Social
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('storefront_');

        $fieldset = $form->addFieldset('social_fieldset', array('legend' => Mage::helper('bootic')->__('Social')));

        $fieldset->addField('follow_google_url', 'text', array(
            'name' => 'follow_google_url',
            'label' => Mage::helper('bootic')->__('Google+:')
        ));

        $fieldset->addField('follow_facebook_url', 'text', array(
            'name' => 'follow_facebook_url',
            'label' => Mage::helper('bootic')->__('Facebook:')
        ));

        $fieldset->addField('follow_twitter_url', 'text', array(
            'name' => 'follow_twitter_url',
            'label' => Mage::helper('bootic')->__('Twitter:')
        ));

        $fieldset->addField('follow_pinterest_url', 'text', array(
            'name' => 'follow_pinterest_url',
            'label' => Mage::helper('bootic')->__('Pinterest:')
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
        return Mage::helper('bootic')->__('Storefront Information');
    }

    /**
     * Return Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('bootic')->__('Storefront Information');
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
