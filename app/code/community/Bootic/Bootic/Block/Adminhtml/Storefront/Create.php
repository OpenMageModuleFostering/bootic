<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function  __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'bootic';
        $this->_controller = 'adminhtml_storefront';
        $this->_mode = 'create';

        parent::__construct();

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->_updateButton('save', 'label', Mage::helper('bootic')->__('Create Storefront'));
    }

    public function getHeaderText()
    {
        $headerText = Mage::helper('bootic')->__('Create Storefront');

        return $headerText;
    }
}
