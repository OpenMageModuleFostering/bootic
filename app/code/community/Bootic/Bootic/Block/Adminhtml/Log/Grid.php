<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct() {
        parent::__construct();
        $this->setId('Log');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Logs');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Load collection
     *
     * @return unknown
     */
    protected function _prepareCollection() {
        $collection = mage::getModel('bootic/log')->getCollection()->addAttributeToSort('id', 'desc');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

     /**
     * Grid configuration
     *
     * @return unknown
     */
    protected function _prepareColumns() {

        $this->addColumn('id', array(
                'header'=> Mage::helper('bootic')->__('Id'),
                'index' => 'id'
        ));

        $this->addColumn('product_id', array(
                'header'=> Mage::helper('bootic')->__('Product Id'),
                'index' => 'product_id'
        ));

        $this->addColumn('date', array(
                'header'=> Mage::helper('bootic')->__('Date'),
                'index' => 'date'
        ));

        $this->addColumn('status', array(
                'header'=> Mage::helper('bootic')->__('Status'),
                'index' => 'status'
        ));

        $this->addColumn('message', array(
                'header'=> Mage::helper('bootic')->__('Message'),
                'index' => 'message',
                'width' => '800',
                'renderer' => 'Bootic_Bootic_Block_Adminhtml_Widget_Grid_Column_Renderer_Log_Message'
        ));

        return parent::_prepareColumns();

    }

    public function getGridParentHtml() {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
}
