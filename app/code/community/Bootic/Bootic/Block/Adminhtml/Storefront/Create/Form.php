<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Create_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/create'),
            'method' => 'post'
        ));

        $fieldset = $form->addFieldset('name_fieldset', array('legend' => Mage::helper('bootic')->__('Name')));

        $fieldset->addField('note', 'note', array(
            'name' => 'note',
            'text' => Mage::helper('bootic')->__('Please start by filling this simple form to create your storefront. On the next step, you will be able to configure it more thoroughly.')
        ));

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('bootic')->__('Name your new Storefront:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $url = $fieldset->addField('url', 'text', array(
            'label' => Mage::helper('bootic')->__('URL:'),
            'required' => true,
            'name' => 'url',
        ));

        $comment = '<p class="note"><span>'.Mage::helper('bootic')->__('Your storefront URL on Bootic').'</span></p>';
        $js = "<script type='text/javascript'>
            $('url').insert({
                before: 'http://bootic.com/ '
            });

            $('name').observe('blur', respondToBlur);

            function respondToBlur(event)
            {
                if ($('url').getValue() == '') {
                    $('url').setValue($('name').getValue())
                }
            }

        </script>";
        $url->setAfterElementHtml($comment . $js);

        $fieldset->addField('template', 'select', array(
            'label' => Mage::helper('bootic')->__('Template:'),
            'name' => 'template',
            'values' => Mage::helper('bootic/storefront')->getAvailableTemplatesValues()
        ));

        //$fieldset->addField('color_theme', 'hidden', array(
        //    'name' => 'color_theme',
        //    'value' => '000000'
        //));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
