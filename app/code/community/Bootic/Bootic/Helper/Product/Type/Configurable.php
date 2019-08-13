<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Product_Type_Configurable extends Mage_Core_Helper_Abstract
{
    /**
     * Prices
     *
     * @var array
     */
    protected $_prices      = array();

    /**
     * Prepared prices
     *
     * @var array
     */
    protected $_resPrices   = array();

    /**
     * Get allowed attributes
     *
     * @return array
     */
    public function getAllowAttributes(Mage_Catalog_Model_Product $currentProduct)
    {
        return $currentProduct->getTypeInstance(true)->getConfigurableAttributes($currentProduct);
    }

    /**
     * Get Allowed Products
     *
     * @return array
     */
    public function getAllowProducts(Mage_Catalog_Model_Product $currentProduct)
    {
        $products = array();
        $allProducts = $currentProduct->getTypeInstance(true)->getUsedProducts(null, $currentProduct);
        foreach ($allProducts as $product) {
            if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
                $products[] = $product;
            }
        }

        return $products;
    }

    /**
     * Creates array of product options
     *
     * @return array
     */
    public function getOptions(Mage_Catalog_Model_Product $currentProduct)
    {
        $attributes = array();
        $options = array();

        foreach ($this->getAllowProducts($currentProduct) as $product) {
            $productId = $product->getId();

            foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
                $productAttribute = $attribute->getProductAttribute();
                $productAttributeId = $productAttribute->getId();
                $attributeValue = $product->getData($productAttribute->getAttributeCode());
                if (!isset($options[$productAttributeId])) {
                    $options[$productAttributeId] = array();
                }

                if (!isset($options[$productAttributeId][$attributeValue])) {
                    $options[$productAttributeId][$attributeValue] = array();
                }
                $options[$productAttributeId][$attributeValue][] = $productId;
            }
        }

        $this->_resPrices = array(
            $this->_preparePrice($currentProduct, $currentProduct->getFinalPrice())
        );

        foreach ($this->getAllowAttributes($currentProduct) as $attribute) {
            $productAttribute = $attribute->getProductAttribute();
            $attributeId = $productAttribute->getId();
            $info = array(
                'id' => $productAttribute->getId(),
                'code' => $productAttribute->getAttributeCode(),
                'label' => $attribute->getLabel(),
                'options' => array()
            );

            $optionPrices = array();
            $prices = $attribute->getPrices();
            if (is_array($prices)) {
                foreach ($prices as $value) {
                    if (!$this->_validateAttributeValue($attributeId, $value, $options)) {
                        continue;
                    }
                    $currentProduct->setConfigurablePrice(
                        $this->_preparePrice($currentProduct, $value['pricing_value'], $value['is_percent'])
                    );
                    $currentProduct->setParentId(true);
                    $configurablePrice = $currentProduct->getConfigurablePrice();

                    if (isset($options[$attributeId][$value['value_index']])) {
                        $productsIndex = $options[$attributeId][$value['value_index']];
                    } else {
                        $productsIndex = array();
                    }

                    $info['options'][] = array(
                        'id' => $value['value_index'],
                        'label' => $value['label'],
                        'price' => $configurablePrice,
                        'oldPrice' => $this->_prepareOldPrice($currentProduct, $value['pricing_value'], $value['is_percent']),
                        'products' => $productsIndex,
                    );
                    $optionPrices[] = $configurablePrice;
                }
            }

            /**
             * Prepare formated values for options choose
             */
            foreach ($optionPrices as $optionPrice) {
                foreach ($optionPrices as $additional) {
                    $this->_preparePrice($currentProduct, abs($additional - $optionPrice));
                }
            }
            if ($this->_validateAttributeInfo($info)) {
                $attributes[$attributeId] = $info;
            }
        }

        $config = array(
            'attributes' => $attributes,
            'basePrice' => $this->_convertPrice($currentProduct->getFinalPrice()),
            'oldPrice' => $this->_convertPrice($currentProduct->getPrice()),
            'productId' => $currentProduct->getId(),
        );

        return $config;
    }

    /**
     * Calculation real price
     *
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _preparePrice(Mage_Catalog_Model_Product $currentProduct, $price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $currentProduct->getTypeInstance(true)->getFinalPrice() * $price / 100;
        }

        return $this->_convertPrice($price, true);
    }

    /**
     * Calculation price before special price
     *
     * @param float $price
     * @param bool $isPercent
     * @return mixed
     */
    protected function _prepareOldPrice(Mage_Catalog_Model_Product $currentProduct, $price, $isPercent = false)
    {
        if ($isPercent && !empty($price)) {
            $price = $currentProduct->getTypeInstance(true)->getPrice() * $price / 100;
        }

        return $this->_convertPrice($price, true);
    }

    /**
     * Convert price from default currency to current currency
     *
     * @param float $price
     * @param boolean $round
     * @return float
     */
    protected function _convertPrice($price, $round = false)
    {
        if (empty($price)) {
            return 0;
        }

        $price = $this->getCurrentStore()->convertPrice($price);
        if ($round) {
            $price = $this->getCurrentStore()->roundPrice($price);
        }

        return $price;
    }

    /**
     * retrieve current store
     *
     * @return Mage_Core_Model_Store
     */
    public function getCurrentStore()
    {
        return Mage::app()->getStore();
    }

    /**
     * Validating of super product option value
     *
     * @param array $attributeId
     * @param array $value
     * @param array $options
     * @return boolean
     */
    protected function _validateAttributeValue($attributeId, &$value, &$options)
    {
        if(isset($options[$attributeId][$value['value_index']])) {
            return true;
        }

        return false;
    }

    /**
     * Validation of super product option
     *
     * @param array $info
     * @return boolean
     */
    protected function _validateAttributeInfo(&$info)
    {
        if(count($info['options']) > 0) {
            return true;
        }

        return false;
    }
}
