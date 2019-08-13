<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Order_Observer
{
    /**
     * handle order canceled event
     * @param Varien_Event_Observer $observer
     */
    public function orderCanceled(Varien_Event_Observer $observer)
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();
        Mage::helper('bootic/orders')->setOutOfSync($order);
    }

    /**
     * handle order shipped event
     * check if all items are marked as shipped
     * if so set order as out of sync to capture shipped status
     * @param Varien_Event_Observer $observer
     */
    public function orderShipped(Varien_Event_Observer $observer)
    {
        try {
            /* @var $shipment Mage_Sales_Model_Order_Shipment */
            $shipment = $observer->getShipment();
            /* @var $order Mage_Sales_Model_Order */
            $order = $shipment->getOrder();
            /* @var $helper Bootic_Bootic_Helper_Orders */
            $helper = Mage::helper('bootic/orders');
            if ($helper->allItemsShipped($order)) {
                $helper->setOutOfSync($order);
            }
        } catch(Exception $e) {
            Mage::logException($e);
        }
    }
}