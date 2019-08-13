<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_System_Config_Account_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $helper = Mage::helper('bootic');

        $form = new Varien_Data_Form(array(
            'id'     => 'edit_form',
            'action' => $this->getUrl('*/*/create'),
            'method' => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('account_create_form', array('legend' => $helper->__('Account Information')));

        $fieldset->addField('email', 'text', array(
            'name'  => 'email',
            'label' => Mage::helper('adminhtml')->__('Email'),
            'id'    => 'customer_email',
            'title' => Mage::helper('adminhtml')->__('User Email'),
            'class' => 'required-entry validate-email',
            'required' => true,
        ));

        $fieldset->addField('password', 'password', array(
            'name'  => 'password',
            'label' => Mage::helper('adminhtml')->__('Password'),
            'id'    => 'customer_pass',
            'title' => Mage::helper('adminhtml')->__('Password'),
            'class' => 'input-text required-entry validate-password',
            'required' => true,
        ));

        $fieldset->addField('confirmation', 'password', array(
            'name'  => 'password_confirmation',
            'label' => Mage::helper('adminhtml')->__('Password Confirmation'),
            'id'    => 'confirmation',
            'title' => Mage::helper('adminhtml')->__('Password Confirmation'),
            'class' => 'input-text required-entry validate-cpassword',
            'required' => true,
        ));

        $fieldset->addField('selling_agreement', 'checkbox', array(
            'name'  => 'selling_agreement',
            'label' => Mage::helper('bootic')->__('Terms & Conditions'),
            'id'    => 'selling_agreement',
            'style'   => "width:10px;float:left;margin-right:10px;margin-top:4px;",
            'after_element_html' => '<div id="selling-agreement-link" style="float:left;padding:1px;width:270px;">By using Bootic.com, you agree to the terms listed in our <a href="https://secure.bootic.com/_vendor/footer/selling_agreement" target="_blank">Selling Agreement.</a></div>'
        ));

        $data = Mage::getSingleton('adminhtml/session')->getFormData();
        $data['selling_agreement'] = 1;

        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
