<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function  __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'bootic';
        $this->_controller = 'adminhtml_storefront';
        $this->_mode = 'edit';

        parent::__construct();

        $this->removeButton('back');
        $this->_updateButton('save', 'label', Mage::helper('bootic')->__('Save Storefront'));
    }

    public function getHeaderText()
    {
        $headerText = Mage::helper('bootic')->__('Edit Storefront');

        return $headerText;
    }

    /**
     * Prepare layout
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _prepareLayout()
    {
        $color = Mage::getSingleton('bootic/storefront')->getColor_theme() ? Mage::getSingleton('bootic/storefront')->getColor_theme() : null;

        $this->_formScripts[] = "
            var cp = new colorPicker('storefront_color_theme', {
            	color:'#" . $color . "',
            	previewElement:'color-preview',
            	onChange: function (e) {
                    var val = '#' + e.HSBToHex(e.color);
                    $('previewDiv').setStyle({ backgroundColor: val });
                }
            });
        ";

        return parent::_prepareLayout();
    }
}
