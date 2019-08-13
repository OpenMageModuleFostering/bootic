<?php

/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */


class Bootic_Bootic_Helper_Orders extends Bootic_Bootic_Helper_Abstract
{
    /* order statuses */
    const ORDER_CANCELLED             = '-201';
    const CANCELLED                   = '-101';
    //only canceled products are below 0
    const PRE_ORDERED                 = '0';
    //only ordered products are above 0
    const ORDERED                     = '101';
    const NO_STOCK__PENDING           = '201';
    const NO_STOCK__ORDERED           = '202';
    const IN_DELIVERY__PENDING        = '301';
    const IN_DELIVERY__SENT           = '302';
    const IN_DELIVERY__LOST           = '303';
    const SOURCE_REQUEST_CANCELLATION = '351';
    const DELIVERED__ACCEPTED         = '401';
    const DELIVERED__REFUSED          = '402';
    const DELIVERED__RETURN_WANTED    = '450';
    const DELIVERED__RETURNED         = '451';
    const REFUSED__DELIVERED_BACK     = '501';
    const RETURNED__DELIVERED_BACK    = '502';
    const COMPLETED                   = '601';
    
    /**
     * Checks for orders that need to be processed
     * and process its.
     *
     * @return array|Mage_Sales_Model_Order[]
     */
    public function processPendingOrders()
    {
        Mage::log('process pending orders...');
        $newOrders = array();
        $orderList = $this->fetchPendingOrders();
        Mage::log('got this many: '.count($orderList));
        foreach ($orderList as $order) {
            try {
                //try to load existing
                $orderData = Mage::getModel('bootic/order_data')->load($order['basket'], 'bootic_order_id');
                if (!$orderData->getOrderId()) {
                    if ($order['status_code'] == self::ORDERED) {
                        $newOrders[] = $this->createOrder($order);
                    } else {
                        Mage::log("will not create order unless its new! {$order['status_code']}");
                    }
                }
            } catch(Exception $e) {
                Mage::logException($e);
            }
        }
        return $newOrders;
    }

    /**
     * Fetches list of orders from Bootic
     * that need to be processed
     * re-organizes them by basket ID
     *
     * @return array|mixed
     */
    public function fetchPendingOrders()
    {
        Mage::log('fetch pending orders...');
        $orderResult = $this->getBootic()->getOrderList();
        $grouped = array();
        if ($orderResult->isSuccess() && $orderResult->hasData('transactions')) {
            Mage::log('we got transactions');
            $transactions = $orderResult->getData('transactions');
            foreach ($transactions as $transaction) {
                if (!isset($grouped[$transaction['basket']])) {
                    $orderDetails = array(
                            'basket'                 => $transaction['basket'],
                            'shop_name'              => $transaction['shop_name'],
                            'producer_currency_name' => $transaction['producer_currency_name'],
                            'buyer_email'            => $transaction['buyer_email'],
                            'producer'               => $transaction['producer'],
                            'buyer'                  => $transaction['buyer'],
                            'shop_deleted'           => $transaction['shop_deleted'],
                            'survey_feedback_id'     => $transaction['survey_feedback_id'],
                            'status'                 => $transaction['status'],
                            'shop_online'            => $transaction['shop_online'],
                            'shipping_company'       => $transaction['shipping_company'],
                            'shop_url'               => $transaction['shop_url'],
                            'marketer'               => $transaction['marketer'],
                            'shipping_tracking_number' => $transaction['shipping_tracking_number'],
                            'transaction_date'         => $transaction['transaction_date'],
                            'shop_banner'              => $transaction['shop_banner'],
                            'status_order'             => $transaction['status_order'],
                            'status_code'              => $transaction['status_code'],
                            'shop'                     => $transaction['shop'],
                            'buyer_name'               => $transaction['buyer_name'],
                            'shipping_address'         => array(
                                'name'            => $transaction['shipping_name'],
                                'address'         => $transaction['shipping_address'],
                                'address2'        => $transaction['shipping_address2'],
                                'city'            => $transaction['shipping_city'],
                                'post_code'       => $transaction['shipping_post_code'],
                                'region'          => $transaction['shipping_region'],
                                'phone'           => $transaction['buyer_phone'],
                                'country'         => $transaction['shipping_country']
                            ),
                            'billing_address'          => array(
                                'name'             => $transaction['billing_name'],
                                'address'          => $transaction['billing_address'],
                                'address2'         => $transaction['billing_address2'],
                                'city'             => $transaction['billing_city'],
                                'post_code'        => $transaction['billing_post_code'],
                                'region'           => $transaction['billing_region'],
                                'phone'           =>  $transaction['buyer_phone'],
                                'country'          => $transaction['billing_country']
                            ),
                            'items'                => array(),
                            'transactions'         => array()
                    );
                    $grouped[$transaction['basket']] = $orderDetails;
                }
                $grouped[$transaction['basket']]['transactions'][] = $transaction['transaction'];
                $grouped[$transaction['basket']]['items'][] = array(//@TODO we should have API do the math for us!
                        'transaction_id' => $transaction['transaction'],
                        'item_id'        => $transaction['product'],
                        'quantity'       => $transaction['quantity'],
                        'price'          => $transaction['wage_producer_total'] / $transaction['quantity'],
                        'tax'            => $transaction['transaction_wage_tax_total'] / $transaction['quantity'],
                        'tax_rate'       => $transaction['transaction_tax_rate'] * 100
                );
//                $grouped[$transaction['basket']]['shipping_amount'] += (double)$transaction['shipping_fee_compensation'];
                $grouped[$transaction['basket']]['shipping_amount'] = (double)$transaction['shipping_fees']['producer_credit'];
            }
        }
        return $grouped;
    }
    
    /**
     * Creates a new Order in Magento from the
     * order data returned by Bootic API
     *
     * @param array $order  The Bootic order Data
     * @return Mage_Sales_Model_Order
     */
    public function createOrder($order)
    {
        // We set a flag so that from our custom shipping method we know that it's a bootic order
        // otherwise that shipping method is showed to front end users
        Mage::register('bootic_order_creation', true);

        //create customer
        $customer = $this->createOrGetCustomer($order);

        Mage::Log(sprintf('Creating order for transaction #%s', $order['basket']));
        Mage::Log(sprintf('Customer is "%s %s <%s>"',
            $customer->getFirstname(),
            $customer->getLastname(),
            $customer->getEmail())
        );
//         Mage::Log($order);
        /* @var $listHelper Bootic_Bootic_Helper_Lists */
        $listHelper = Mage::helper('bootic/lists');
        //create order
        /* @var @newOrder Mage_Sales_Model_Order */
        $newOrder = Mage::getModel('sales/order');
        $newOrder->reset();
        $newOrder->setCustomerId($customer->getId());
        $newOrder->setCustomerGroupId($customer->getGroupId());
        $newOrder->setCustomerFirstname($customer->getFirstname());
        $newOrder->setCustomerLastname($customer->getLastname());
        $newOrder->setCustomerIsGuest(0);
        $newOrder->setCustomerEmail($customer->getemail());
        //use default store currency
        $currency = Mage::app()->getStore($this->getStoreId())->getCurrentCurrency();
        $newOrder->setStoreId($this->getStoreId());
        $newOrder->setOrderCurrencyCode($currency->getCode());
        $newOrder->setBaseCurrencyCode($currency->getCode());
        $newOrder->setStoreCurrencyCode($currency->getCode());
        $newOrder->setStoreToBaseRate(1);
        
        //shipping address
        /* @var $shippingAddress Mage_Sales_Model_Order_Address */
        $shippingAddress = Mage::getModel('sales/order_address');
        $shippingAddress->setOrder($newOrder);
        $shippingAddress->setId(null);
        //12 is default quote address entity type
        $shippingAddress->setEntityTypeId(12);
        $shippingAddress->setFirstname($order['shipping_address']['name']);
        $shippingAddress->setLastname('');
        
        $shippingAddress->setStreet($order['shipping_address']['address']);
        
        $shippingAddress->setCity($order['shipping_address']['city']);
        $shippingAddress->setPostcode($order['shipping_address']['post_code']);
        $shippingAddress->setRegion($listHelper->getRegionLabel($order['shipping_address']['region']));
        $shippingAddress->setEmail($customer->getEmail());
        $shippingAddress->setTelephone($order['shipping_address']['phone']);
        $shippingAddress->setCompany('');
        $shippingAddress->setCountryId($listHelper->getCountryLabel($order['shipping_address']['country']));
        $newOrder->setShippingAddress($shippingAddress);
        
        //billing address
        /* @var $billingAddress Mage_Sales_Model_Order_Address */
        $billingAddress = Mage::getModel('sales/order_address');
        $billingAddress->setOrder($newOrder);
        $billingAddress->setId(null);
        
        $billingAddress->setEntityTypeId(12);
        $billingAddress->setFirstname($order['billing_address']['name']);
        $billingAddress->setLastname('');
        $billingAddress->setStreet($order['billing_address']['address']);
        $billingAddress->setCity($order['billing_address']['city']);
        $billingAddress->setPostcode($order['billing_address']['post_code']);
        $billingAddress->setRegion($listHelper->getRegionLabel($order['billing_address']['region']));
        $billingAddress->setCountryId($listHelper->getCountryLabel($order['billing_address']['country']));
        $billingAddress->setEmail($customer->getEmail());
        $billingAddress->setTelephone($order['billing_address']['phone']);
        $billingAddress->setcompany('');
        
        $newOrder->setBillingAddress($billingAddress);
        
        //Payment method
        /* @var Mage_Sales_Model_Order_Payment */
        $payment = Mage::getModel('sales/order_payment');
        $payment->setMethod('bootic_payment_method');
        $newOrder->setPayment($payment);
        
        //shipping method
        $shippingTaxAmount = 0.00; 
        $shippingAmount = $order['shipping_amount'];
        $newOrder->setShippingMethod('bootic_flat_shipping');
        $newOrder->setShippingDescription('Bootic Flat Shipping');
        $newOrder->setShippingAmount((double) $shippingAmount);
        $newOrder->setBaseShippingAmount((double) $shippingAmount);
        $newOrder->setShippingTaxAmount((double) $shippingTaxAmount);
        $newOrder->setBaseShippingTaxAmount((double) $shippingTaxAmount);
        
        //init order totals
        $newOrder
        ->setGrandTotal($shippingAmount + $shippingTaxAmount)
        ->setBaseGrandTotal($shippingAmount + $shippingTaxAmount)
        ->setTaxAmount($shippingTaxAmount)
        ->setBaseTaxAmount($shippingTaxAmount);
        
        foreach ($order['items'] as $item) {
            /* @var $product Mage_Catalog_Model_Product */
            $product  =  Mage::helper('bootic/product')->loadByBooticId($item['item_id']);
            if (!$product) {
                Mage::log('skipping: '.$item['item_id']);
                continue;
            }
            //set price and tax
            $price_excl_tax = $item['price'];
            $price_incl_tax = $item['price'] + $item['tax'];
            $tax          = $item['tax'];
            $qty          = $item['quantity'];
            $taxRate      = $item['tax_rate'];
            $taxTotal     = $tax * $qty;
            $htTotal      = $price_excl_tax * $qty;
            $newOrderItem = Mage::getModel('sales/order_item')
            ->setProductId($product->getId())
            ->setSku($product->getSku())
            ->setName($product->getName())
            ->setWeight($product->getWeight())
            ->setTaxClassId($product->getTaxClassId())
            ->setCost($product->getCost())
            ->setOriginalPrice($price_excl_tax)
            ->setBaseOriginalPrice($price_excl_tax)
            ->setIsQtyDecimal(0)
            ->setProduct($product)
            ->setPrice((double) $price_excl_tax)
            ->setBasePrice((double) $price_excl_tax)
            ->setQtyOrdered($qty)
            ->setTotalQty($qty)//used to track quantity to decrement from inventory
            ->setTaxAmount($taxTotal)
            ->setBaseTaxAmount($taxTotal)
            ->setTaxPercent($taxRate)
            ->setRowTotal($htTotal)
            ->setBaseRowTotal($htTotal)
            ->setRowWeight($product->getWeight() * $qty)
            ->setBaseTaxBeforeDiscount($taxTotal)
            ->setTaxBeforeDiscount($taxTotal);
            
            //add product
            $newOrder->addItem($newOrderItem);
            $newOrder
            ->setSubtotal($newOrder->getSubtotal() + $price_excl_tax * $qty)
            ->setBaseSubtotal($newOrder->getBaseSubtotal() + $price_excl_tax * $qty)
            ->setGrandTotal($newOrder->getGrandTotal() + (($tax + $price_excl_tax) * $qty))
            ->setBaseGrandTotal($newOrder->getBaseGrandTotal() + (($tax + $price_excl_tax) * $qty))
            ->setTaxAmount($newOrder->getTaxAmount() + $tax * $qty)
            ->setBaseTaxAmount($newOrder->getBaseTaxAmount() + $tax * $qty);
            //done with product
        }   
        //save order
        $newOrder->setstatus('processing');
        $newOrder->setstate('new');
        $newOrder->addStatusToHistory(
                'pending',
                'Bootic Order from shop:' . $order['shop_name'] . ' #' . $order['basket']
        );
        $newOrder->setInvoiceComments('Bootic Order ' . $order['shop_name'] . ' #' . $order['basket']);
        $this->_updateProductStock($newOrder);
        $newOrder->save();
        if ($newOrder->getId()) {
            Mage::getModel('bootic/order_data')
                ->setOrderId($newOrder->getId())
                ->setBooticOrderId($order['basket'])
                ->setInSync(true)
                ->setTransactions($order['transactions'])
                ->setLastStatus(self::ORDERED)
                ->save();
        }
        
        return $newOrder;
    }

    /**
     * Fetch and returns customer if it already exists,
     * Create a new one otherwise.
     *
     * @param array $order
     * @return Mage_Customer_Model_Customer
     */
    public function createOrGetCustomer($order)
    {
        $email     = $order['buyer_email'];
        $storeId   = $this->getStoreId();
        $webSiteId = $this->getWebsiteId();
        $customer = Mage::getModel('customer/customer')
            ->setWebsiteId($webSiteId)
            ->loadByEmail($email);
        
        if ($customer->getId()) {
            return $customer;
        }

        list($firstname, $lastname) = sscanf($order['buyer_name'], '%s %s');

        $customer = Mage::getModel('customer/customer');
        $customer
            ->setStoreId($storeId)
            ->setWebsiteId($webSiteId)
            ->setFirstname($firstname)
            ->setLastname($lastname)
            ->setEmail($email)
            ->save();

        return $customer;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return Mage::getStoreConfig('bootic/system/store_id');
    }

    /**
     * @return int
     */
    public function getWebsiteId($storeId = null)
    {
        $storeId = null == $storeId ? $this->getStoreId() : $storeId;

        return Mage::getModel('core/store')->load($storeId)->getWebsiteId();
    }
    /**
     * set order data to out of sync
     * @param Mage_Sales_Model_Order $order
     */
    public function setOutOfSync(Mage_Sales_Model_Order $order)
    {
        /* @var $orderData Bootic_Bootic_Model_Order_Data */
        $orderData = Mage::getModel('bootic/order_data')->load($order->getId());
        if ($orderData->getBooticOrderId()) {
            $orderData->setInSync(false)->save();
        }
    }
    /**
     * syncrhonize order status
     */
    public function syncOrders()
    {
        /* @var $collection Mage_Sales_Model_Mysql4_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')->addAttributeToSelect('*');
        $collection->join(
            'bootic/order_data',
            'entity_id=order_id',
            array(
                'bootic_order_id'     => 'bootic_order_id',
                'magento_order_id'    => 'order_id',
            )
        );

        $collection->addAttributeToFilter('in_sync', false);

        foreach ($collection as $order) {
            /* @var $order Mage_Sales_Model_Order */
            /* @var $orderData Bootic_Bootic_Model_Order_Data */
            $orderData = Mage::getModel('bootic/order_data')->load($order->getId());
            Mage::log($order->getState());
            try {
                switch ($order->getState()) {
                    case Mage_Sales_Model_Order::STATE_CANCELED:
                        $this->_syncCanceledOrder($order, $orderData);
                        break;
                    case Mage_Sales_Model_Order::STATE_PROCESSING:
                        if ($this->allItemsShipped($order)) {
                            $this->_syncShippedOrder($order, $orderData);
                        }
                        break;
                }
            } catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        }
    }
    /**
     * pull order status from orders marked as shipped
     */
    public function pullShippedOrderStatus()
    {
        /* @var $collection Mage_Sales_Model_Resource_Order_Collection */
        $collection = Mage::getResourceModel('sales/order_collection')->addAttributeToSelect('*');
        $collection->join(
                array(
                        'order_data' => 'bootic/order_data'
                ),
                'order_data.order_id=entity_id',
                array(
                        'bootic_order_id'     => 'bootic_order_id',
                        'magento_order_id'    => 'order_id',
                )
        );
        $collection->addAttributeToFilter('last_status', self::IN_DELIVERY__SENT);
        foreach ($collection as $order) {
            /* @var $order Mage_Sales_Model_Order */
            /* @var $orderData Bootic_Bootic_Model_Order_Data */
            $orderData = Mage::getModel('bootic/order_data')->load($order->getId());
            try {
                if ($order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $details = $this->getBootic()->getOrderDetails($orderData->getBooticOrderId());
                    if (true) {//@TODO if status complete
                         if ($this->_invoiceOrder($order)) {
                             $orderData->setInSync(true)->setLastStatus(self::COMPLETED)->save();
                         }
                    }
                }
            }catch (Exception $e) {
                Mage::log($e->getMessage());
            }
        }
    }
    /**
     * sync cnaceled order
     * @param Mage_Sales_Model_Order $order
     * @param Bootic_Bootic_Model_Order_Data $orderData
     */
    protected function _syncCanceledOrder(Mage_Sales_Model_Order $order, Bootic_Bootic_Model_Order_Data $orderData)
    {
        foreach ($orderData->getTransactions() as $transaction) {
            $result = $this->getBootic()->updateTransactionStatus($transaction, array('status' => self::CANCELLED));
            if ($result->getSuccess()) {
                $orderData->setInSync(true)->setLastSyncStatus(self::CANCELLED)->save();//@TODO only save if success num == trans num?
            }
        }
    }
    
    /**
     * sync shipped order
     * @param Mage_Sales_Model_Order $order
     * @param Bootic_Bootic_Model_Order_Data $orderData
     */
    protected function _syncShippedOrder(Mage_Sales_Model_Order $order, Bootic_Bootic_Model_Order_Data $orderData)
    {
        /* @var $shipment Mage_Sales_Model_Order_Shipment */
        $shipment = $order->getShipmentsCollection()->getFirstItem();
        /* @var $track Mage_Sales_Model_Order_Shipment_Track */
        $track = $shipment->getTracksCollection()->getFirstItem();

        $carrierId = Mage::helper('bootic/lists')->getIdByCarrierCode($track->getCarrierCode());
        // Here for Magento 1.5 compatibility
        $tracking  = $track->getTrackNumber() ? $track->getTrackNumber() : $track->getNumber();

        foreach ($orderData->getTransactions() as $transaction) {
            $params = array(
                'status'    => self::IN_DELIVERY__SENT,
                'tracking_no' => $tracking,
                'shipping_company_id' => $carrierId
            );

            if ($carrierId == 4) {
                $params['custom_shipping_company'] = $track->getCarrierCode();
            }

            $result = $this->getBootic()->updateTransactionStatus($transaction, $params);
            if ($result->getSuccess()) {
                $orderData->setInSync(true)->setLastStatus(self::IN_DELIVERY__SENT)->save();
            }
        }
    }
    /**
     * check if all items have been shipped
     * @param Mage_Sales_Model_Order $order
     */
    public function allItemsShipped(Mage_Sales_Model_Order $order)
    {
        $shipmentComplete = true;
        foreach ($order->getAllItems() as $item) {
            /* @var $item Mage_Sales_Model_Order_Shipment_Item */
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                    && !$item->getLockedDoShip())
            {
                $shipmentComplete = false;
            }
        }
        return $shipmentComplete;
    }
    /**
     * update product stock quantity
     * @param Mage_Catalog_Model_Product $product
     * @param unknown_type $qty
     */
    protected function _updateProductStock(Mage_Sales_Model_Order $order)
    {
        /* @var $helper Bootic_Bootic_Helper_Product_Inventory */
        $helper = Mage::helper('bootic/product_inventory');
        try {
            $helper->subtractOrderInventory($order);
            $helper->reindexOrderInventory($order);
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }
    /**
     * invoice order
     * @param Mage_Sales_Model_Order $order
     */
    protected function _invoiceOrder(Mage_Sales_Model_Order $order) {
        try {
            /* @var $convertor Mage_Sales_Model_Convert_Order */
            $convertor = Mage::getModel('sales/convert_order');
            $invoice   = $convertor->toInvoice($order);
            foreach ($order->getAllItems() as $orderItem) {
                $invoiceItem = $convertor->itemToInvoiceItem($orderItem);
                $invoiceItem->setQty($orderItem->getQtyOrdered());
                $invoice->addItem($invoiceItem);
            }
            $invoice->collectTotals();
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder())
            ->save();
            $invoice->save();
            //validate payment
            $payment = $order->getPayment();
            $payment->pay($invoice);
            $payment->save();
            $order->save();
            return true;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
}
