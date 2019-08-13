<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Block_Adminhtml_Log_Error extends Mage_Adminhtml_Block_Widget
{
    public function getErrors(){

        $timestamp = Mage::getModel('core/date')->timestamp() - 3600 * 24;

        $collection = mage::getModel('bootic/log')
                                ->getCollection()
                                ->addFieldToFilter('status', array('neq' => 'success'))
                                ->addAttributeToSort('id', 'desc')
                                ->addFieldToFilter('date', array('gt'=>date('Y-m-d H:i:s', $timestamp)));

        $collection->getSelect()->limit(5);

        return $collection;
    }
}