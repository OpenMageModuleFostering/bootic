<?php
/**
 * (c) Newcode <contact@newcodestudio.com>
 */

class Bootic_Bootic_Block_Adminhtml_Storefront_Edit_Tab_Design_Preview extends Varien_Data_Form_Element_Abstract
{
    public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->setType('label');
    }

    public function getElementHtml()
    {
        $html = $this->getJs();
        $html.= $this->getEscapedValue();
        $html.= $this->getBold() ? '</strong>' : '';
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    private function getOptions()
    {
        $options = Mage::helper('bootic/storefront')->getStoreFrontOptions(Mage::getStoreConfig('bootic/account/storefront_id'));

        return $options->getData();
    }

    private function getJs()
    {
        $js =
            '<script type="text/javascript">
                var storeOptions = ' . json_encode($this->getOptions()) . ';
                var templates = storeOptions.templates;
                /*var color = "#" + $("storefront_color_theme").value;
                var previewImg = new Element("img");

                templates.each(function (template) {
                    if (template.current_template == true) {
                        previewImg.src = template.preview;
                        $("storefront_preview_fieldset").insert({
                          top: previewImg
                        });
                    }
                })

                $("storefront_preview_fieldset").setStyle({ backgroundColor: color });
		*/
            </script>'
        ;

        return $js;
    }
}
