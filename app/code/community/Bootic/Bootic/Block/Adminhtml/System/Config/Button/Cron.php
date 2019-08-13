<?php


class Bootic_Bootic_Block_Adminhtml_System_Config_Button_Cron extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template to itself
     *
     * @return Bootic_Bootic_Block_Adminhtml_System_Config_Button_Cron
     */
    protected function _prepareLayout()
    {
        Mage::log('_prepareLayout');
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate('bootic/adminhtml/system/config/button/cron.phtml');
        }

        return $this;
    }

    /**
     * Unset some non-related element parameters
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        Mage::log('render');
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        Mage::log('_getElementHtml');
        $originalData = $element->getOriginalData();
        $this->addData(array(
            'button_label' => Mage::helper('bootic')->__($originalData['button_label']),
            'cron_type' => $originalData['cron_type'],
            'html_id' => $element->getHtmlId(),
            'ajax_url' => Mage::getSingleton('adminhtml/url')->getUrl($originalData['button_url']),
        ));

        return $this->_toHtml();
    }
}
