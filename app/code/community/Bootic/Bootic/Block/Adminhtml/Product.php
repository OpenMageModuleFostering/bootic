<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Product extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setId('ProductGrid');
        $this->_parentTemplate = $this->getTemplate();
        $this->setEmptyText('No Products');
        $this->setSaveParametersInSession(true);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('type_id', array('nin' => array('bundle', 'virtual', 'grouped', 'downloadable')))
            ->addAttributeToFilter('status', 1)
            ->joinTable(
                'bootic/product_data',
                'magento_product_id=entity_id',
                array(
                    'bootic_product_id'     => 'bootic_product_id',
                    'bootic_stock_id'       => 'bootic_stock_id',
                    'bootic_status'         => 'bootic_status',
                    'creation_time'         => 'creation_time',
                    'update_time'           => 'update_time',
                    'upload_failures'       => 'upload_failures'
                ),
                null,
                'left'
            )
        ;

        if (Mage::helper('catalog')->isModuleEnabled('Mage_CatalogInventory')) {
            $collection->joinField('qty',
                'cataloginventory/stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left');
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id', array(
                'header'=> Mage::helper('bootic')->__('ID'),
                'width' => '50px',
                'type'  => 'number',
                'index' => 'entity_id',
        ));

        $this->addColumn('sku', array(
                'header'=> Mage::helper('bootic')->__('Sku'),
                'index' => 'sku',
        ));

        $this->addColumn('name', array(
                'header'=> Mage::helper('bootic')->__('Product'),
                'index' => 'name'
        ));

        $sets = Mage::getResourceModel('eav/entity_attribute_set_collection')
            ->setEntityTypeFilter(Mage::getModel('catalog/product')->getResource()->getTypeId())
            ->load()
            ->toOptionHash();

        $this->addColumn('set_name',
            array(
                'header'=> Mage::helper('catalog')->__('Attrib. Set Name'),
                'width' => '100px',
                'index' => 'attribute_set_id',
                'type'  => 'options',
                'options' => $sets,
        ));

        $options = Mage::getSingleton('catalog/product_type')->getOptionArray();
        foreach (array('virtual', 'grouped', 'bundle', 'downloadable') as $type) {
            unset($options[$type]);
        }

        $this->addColumn('type',
            array(
                'header'=> Mage::helper('catalog')->__('Type'),
                'width' => '140px',
                'index' => 'type_id',
                'type'  => 'options',
                'options' => $options,
        ));

        $this->addColumn('price', array(
                'header'   => Mage::helper('bootic')->__('Price excl tax'),
                'index'    => 'price',
                'type'	   => 'price',
                'align'    => 'right',
                'currency_code' => Mage::getStoreConfig('currency/options/base')
        ));

        $this->addColumn('visibility',
                array(
                'header'  => Mage::helper('bootic')->__('Visibility'),
                'width'   => '150px',
                'index'   => 'visibility',
                'type'    => 'options',
                'align'   => 'center',
                'options' => Mage::getModel('catalog/product_visibility')->getOptionArray(),
        ));

        $this->addColumn('qty', array(
                'header'   => Mage::helper('bootic')->__('Qty'),
                'width' => '100px',
                'type'  => 'number',
                'index' => 'qty'
        ));

        $this->addColumn('status', array(
                'header'   => Mage::helper('bootic')->__('Bootic Status'),
                'width' => '160px',
                'index' => 'bootic_status',
                'type'    => 'options',
                'sortable'   => true,
                'options' => Mage::getModel('bootic/product_data')->getOptionArray(),
//                'filter' => 'Bootic_Bootic_Block_Adminhtml_Widget_Grid_Column_Filter_Product_Status',
                'default' => 0
        ));

        return parent::_prepareColumns();

    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }

    protected function _prepareMassaction(){

        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('ids');

        $this->getMassactionBlock()->addItem('add_selection', array(
             'label'=> Mage::helper('bootic')->__('Add to Bootic'),
             'url'  => $this->getUrl('bootic/adminhtml_catalog_product/massAddProducts'),
        ));

//        $this->getMassactionBlock()->addItem('reset_selection', array(
//             'label'=> Mage::helper('bootic')->__('Set to Not Created'),
//             'url'  => $this->getUrl('bootic/adminhtml_catalog_product/massResetProducts'),
//        ));

        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('adminhtml/catalog_product/edit', array(
            'store' => $this->getRequest()->getParam('store'),
            'id' => $row->getId())
        );
    }
}
