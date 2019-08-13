<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_Design
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('storefront_');

        $fieldset = $form->addFieldset('design_fieldset', array('legend' => Mage::helper('bootic')->__('Design')));

        $fieldset->addField('template', 'select', array(
            'label' => Mage::helper('bootic')->__('Template:'),
            'name' => 'template',
            'values' => Mage::helper('bootic/storefront')->getAvailableTemplatesValues()
        ));

        $fieldset->addField('color_theme', 'text', array(
            'name' => 'color_theme',
            'label' => Mage::helper('bootic')->__('Color:'),
            'style'   => "width:100px;float:left;margin-right:10px;",
            'after_element_html' => '<div id="color-preview" style="display:block;width:15px;height:15px;float:left;padding:1px;border:1px solid #AAA;"></div>'
        ));

        $bannerFieldset = $form->addFieldset('banner_fieldset', array('legend' => Mage::helper('bootic')->__('Storefront Banner')));

        $bannerFieldset->addField('banner', 'hidden', array(
            'label' => Mage::helper('bootic')->__('Default Banner'),
            'name' => 'banner',
            'note'      => Mage::helper('bootic')->__('You can pick up from'),
            'after_element_html' => $this->renderBannerSelector() . $this->renderPreview()
        ));

        $bannerFieldset->addField('banner_url', 'image', array(
            'label' => Mage::helper('bootic')->__('Custom Banner'),
            'name' => 'banner_url',
            'note'      => Mage::helper('bootic')->__('Image has to be either png, gif or jpg and be exactly 996*180px'),
        ));

        $form->addFieldset('preview_fieldset', array(
            'legend' => Mage::helper('bootic')->__('Storefront Preview'),
        ));

//        $previewFieldset->addType('preview','Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_Design_Preview');
//
//        $previewFieldset->addField('preview_field', 'preview', array(
//            'name'          => 'preview_field',
//            'required'      => false,
//        ));

        if (Mage::getSingleton('bootic/storefront')->getData('color_theme') == null) {
            Mage::getSingleton('bootic/storefront')->setData('color_theme', '000000');
        }
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

    private function renderBannerSelector()
    {
        return $this
            ->getLayout()
            ->getBlock('bootic_storefront_banner')
            ->toHtml()
            ;
    }

    private function renderPreview()
    {
        return $this
            ->getLayout()
            ->getBlock('bootic_storefront_preview')
            ->toHtml()
            ;

    }
}
