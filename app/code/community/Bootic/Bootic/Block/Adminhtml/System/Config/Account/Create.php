<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_System_Config_Account_Create extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function  __construct()
    {
        parent::__construct();

        $this->_objectId = 'id';
        $this->_blockGroup = 'bootic';
        $this->_controller = 'adminhtml_system_config_account';
        $this->_mode = 'create';

        $this->_removeButton('back');
    }

    public function getHeaderText()
    {
        return Mage::helper('bootic')->__('Create your account');
    }
}
