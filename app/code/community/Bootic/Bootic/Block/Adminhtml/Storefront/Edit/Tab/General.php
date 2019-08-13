<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
        implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Load Wysiwyg on demand and Prepare layout
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    protected function _prepareForm()
    {
        $storeData = Mage::getSingleton('bootic/storefront')->getData();
        $_bootic = Mage::helper('bootic')->getBootic();

        $form = new Varien_Data_Form();

        $form->setHtmlIdPrefix('storefront_');

        $form->addField('shop_id', 'hidden', array(
            'name' => 'shop_id'
        ));

        $fieldset = $form->addFieldset('general_fieldset', array('legend'=>Mage::helper('bootic')->__('General')));


        if ($storeData['online'] == true) {
            $fieldset->addField('note', 'note', array(
                'name' => 'note',
                'text' => '<a href="' . $_bootic->getUri('server_main') . $storeData['url'] . '" target="_blank">' . Mage::helper('bootic')->__('Preview your storefront here') . '</a>'
            ));
        }

        $fieldset->addField('name', 'text', array(
            'label' => Mage::helper('bootic')->__('Name your new Storefront:'),
            'class' => 'required-entry',
            'required' => true,
            'name' => 'name',
        ));

        $fieldset->addField('description', 'editor', array(
            'label' => Mage::helper('bootic')->__('Describe your storefront:'),
            'style'     => 'height:15em',
            'config'    => Mage::getSingleton('cms/wysiwyg_config')->getConfig(),
            'wysiwyg'   => true,
            'name' => 'description',
        ));

        $url = $fieldset->addField('url', 'text', array(
            'label' => Mage::helper('bootic')->__('URL:'),
            'required' => true,
            'name' => 'url',
        ));

        $comment = '<p class="note"><span>'.Mage::helper('bootic')->__('Your storefront URL on Bootic').'</span></p>';
        $js = "<script type='text/javascript'>
            $('storefront_url').insert({
                before: 'http://bootic.com/ '
            });
        </script>";
        $url->setAfterElementHtml($comment . $js);

        $fieldset->addField('online', 'select', array(
            'label' => Mage::helper('bootic')->__('Online:'),
            'name' => 'online',
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

        $form->setValues($storeData);
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
