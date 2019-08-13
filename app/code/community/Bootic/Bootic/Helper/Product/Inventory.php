<?php
/**
 * Product Inventory Helper
 * take advantage of catalog inventory observer methods
 * and reuse in context of orders  
 */
class Bootic_Bootic_Helper_Product_Inventory extends Mage_CatalogInventory_Model_Observer
{
    /**
     * subtract order inventory
     * @param Mage_Sales_Model_Order $order
     * @see parent::subtractQuoteInventory
     */
    public function subtractOrderInventory(Mage_Sales_Model_Order $order)
    {
        $items = $this->_getProductsQty($order->getAllItems());
        /**
         * Remember items
         */
        $this->_itemsForReindex = Mage::getSingleton('cataloginventory/stock')->registerProductsSale($items);
    }
    
    /**
     * Refresh stock index for specific stock items after succesful order placement
     *
     * @param $observer
     */
    public function reindexOrderInventory(Mage_Sales_Model_Order $order)
    {
        // Reindex order ids
        $productIds = array();
        foreach ($order->getAllItems() as $item) {
            $productIds[$item->getProductId()] = $item->getProductId();
            $children   = $item->getChildrenItems();
            if ($children) {
                foreach ($children as $childItem) {
                    $productIds[$childItem->getProductId()] = $childItem->getProductId();
                }
            }
        }
    
        if( count($productIds)) {
            Mage::getResourceSingleton('cataloginventory/indexer_stock')->reindexProducts($productIds);
        }
    
        // Reindex previously remembered items
        $productIds = array();
        foreach ($this->_itemsForReindex as $item) {
            $item->save();
            $productIds[] = $item->getProductId();
        }
        Mage::getResourceSingleton('catalog/product_indexer_price')->reindexProductIds($productIds);
    
        $this->_itemsForReindex = array(); // Clear list of remembered items - we don't need it anymore
    
        return $this;
    }
}