<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_System_Config_Tabs extends Mage_Adminhtml_Block_System_Config_Tabs
{
    /**
     * Let's override the left hand column
     *
     */
    public function initTabs()
    {
        $current = 'bootic';
        $websiteCode = $this->getRequest()->getParam('website');
        $storeCode = $this->getRequest()->getParam('store');

        $url = Mage::getModel('adminhtml/url');

        $configFields = Mage::getSingleton('adminhtml/config');
        $sections = $configFields->getSections($current);
        $tabs     = (array)$configFields->getTabs()->children();


        $sections = (array)$sections;

        usort($sections, array($this, '_sort'));
        usort($tabs, array($this, '_sort'));

        foreach ($tabs as $tab) {
            $helperName = $configFields->getAttributeModule($tab);
            $label = Mage::helper($helperName)->__((string)$tab->label);

            $this->addTab($tab->getName(), array(
                'label' => $label,
                'class' => (string) $tab->class
            ));
        }


        foreach ($sections as $section) {
            Mage::dispatchEvent('adminhtml_block_system_config_init_tab_sections_before', array('section' => $section));
            $hasChildren = $configFields->hasChildren($section, $websiteCode, $storeCode);

            //$code = $section->getPath();
            $code = $section->getName();

            $sectionAllowed = $this->checkSectionPermissions($code);
            if ((empty($current) && $sectionAllowed)) {

                $current = $code;
                $this->getRequest()->setParam('section', $current);
            }

            $helperName = $configFields->getAttributeModule($section);

            $label = Mage::helper($helperName)->__((string)$section->label);

            if ($code == $current) {
                if (!$this->getRequest()->getParam('website') && !$this->getRequest()->getParam('store')) {
                    $this->_addBreadcrumb($label);
                } else {
                    $this->_addBreadcrumb($label, '', $url->getUrl('*/*/*', array('section'=>$code)));
                }
            }
            if ( $sectionAllowed && $hasChildren) {
                $this->addSection($code, (string)$section->tab, array(
                    'class'     => (string)$section->class,
                    'label'     => $label,
                    'url'       => $url->getUrl('adminhtml/system_config/edit', array('_current'=>true, 'section'=>$code)),
                ));
            }

            if ($code == $current) {
                $this->setActiveTab($section->tab);
                $this->setActiveSection($code);
            }
        }

        /*
         * Set last sections
         */
        foreach ($this->getTabs() as $tab) {
            $sections = $tab->getSections();
            if ($sections) {
                $sections->getLastItem()->setIsLast(true);
            }
        }

        Mage::helper('adminhtml')->addPageHelpUrl($current.'/');

        return $this;
    }
}
