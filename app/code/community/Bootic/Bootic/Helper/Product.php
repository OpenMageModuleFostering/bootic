<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Product extends Bootic_Bootic_Helper_Abstract
{
    /** @var array */
    protected $_booticAttributes;

    /** @var int */
    protected $_limit;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_limit = (Mage::getStoreConfig('bootic/product/limit')) ? Mage::getStoreConfig('bootic/product/limit') : 50;
    }

    /**
     * Queues a collection of products for cron to upload them to Bootic
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function massCreateProducts(Mage_Core_Controller_Request_Http $request)
    {
        Mage::log('massCreateProducts');
        $ids = $request->__get('ids');

        $addedToQueue                   = array();
        $alreadyQueued                  = array();
        $configurableChildrenInQueue    = array();
        $configurableChildrenNeedParent = array();
        $notProcessable                 = array();

        foreach ($ids as $id) {
            $product = Mage::getModel('catalog/product')->load($id);

            if ($product->isConfigurable()) {
                $childrenIds = Mage::getModel('catalog/product_type_configurable')->getChildrenIds($product->getId());

                foreach ($childrenIds[0] as $childId) {
                    $childProduct = Mage::getModel('catalog/product')->load($childId);

                    $this->_queueProduct($childProduct, $addedToQueue, $alreadyQueued);

                    if (!in_array($childId, $ids)) {
                        // We remove the children from the errors list
                        if (($key = array_search($childId, $configurableChildrenNeedParent)) !== false) {
                            unset($configurableChildrenNeedParent[$key]);
                        }
                        $configurableChildrenInQueue[] = $childId;
                    }
                }

                $this->_queueProduct($product, $addedToQueue, $alreadyQueued);

            } elseif ($product->getTypeId() == 'simple' || $product->getTypeId() == 'downloadable') {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                $product->setParentIds($parentIds);

                if (count($parentIds) > 0) {
                    foreach ($parentIds as $parentId) {
                        $parent = Mage::getModel('catalog/product')->load($parentId);

                        if (
                            Mage::helper('bootic/product_data')->isNotCreated($parent) &&
                            !in_array($parentId, $ids)
                        ) {
                            $errorStatus = Mage::getModel('bootic/product_data')->getStatusError();
                            Mage::helper('bootic/product_data')->updateProductStatus($product, $errorStatus);
                            $configurableChildrenNeedParent[] = $product;
                        } else {
                            $parentData = Mage::getModel('bootic/product_data')->load($parent->getId());
                            $parentStatus = $parentData->getBooticStatus();
                            Mage::helper('bootic/product_data')->updateProductStatus($product, $parentStatus);
                        }
                    }
                } else {
                    $this->_queueProduct($product, $addedToQueue, $alreadyQueued);
                }
            } else {
                $notProcessable[] = $id;
            }
        }

        if (count($alreadyQueued) > 0) {
            Mage::getSingleton('adminhtml/session')->addNotice(count($alreadyQueued) . ' products were skipped because they were already queued.');
        }

        if (count($addedToQueue) > 0) {
            Mage::getSingleton('adminhtml/session')->addSuccess(count($addedToQueue) . ' products were successfully queued.');
        }

        if (count($configurableChildrenInQueue) > 0) {
            Mage::getSingleton('adminhtml/session')->addNotice(count($configurableChildrenInQueue) . ' configurable products children were automatically added to the queue because you added their parents.');
        }

        if (count($configurableChildrenNeedParent) > 0) {
            // For these ones, we also add a log to make it easier for users to retrieve the products
            foreach($configurableChildrenNeedParent as $configurableChild) {
                $message = 'Please add parent products with ids ' . implode(',', $configurableChild->getParentIds());
                Mage::helper('bootic/log')->addLog($configurableChild->getId(), 'error', $message);
            }
        }

        if (count($notProcessable) > 0) {
            Mage::getSingleton('adminhtml/session')->addError(count($notProcessable) . ' products have types that can\'t be currently processed on Bootic.');
        }

        return;
    }

    /**
     * Queues a unique product
     *
     * @param Mage_Catalog_Model_Product $product
     * @param $addedToQueue
     * @param $alreadyQueued
     */
    protected function _queueProduct(Mage_Catalog_Model_Product $product, &$addedToQueue, &$alreadyQueued)
    {
        if (Mage::helper('bootic/product_data')->isQueueable($product)) {
            Mage::helper('bootic/product_data')->updateProductStatus($product, Mage::getModel('bootic/product_data')->getStatusProcessing());
            $addedToQueue[] = $product->getId();
        }   else {
            $alreadyQueued[] = $product->getId();
        }
    }
    
    /**
     * load a product product model by bootic ID
     * @param str $id
     * @return Mage_Catalog_Model_Product | null
     */
    public function loadByBooticId($id)
    {
        Mage::log('loadByBooticId');
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->joinTable(
                    'bootic/product_data',
                    'magento_product_id=entity_id',
                    array (
                            'bootic_product_id'     => 'bootic_product_id',
                    )
            )
            ->addAttributeToFilter('bootic_product_id', $id)
        ;

        Mage::getModel('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($collection);
        $collection->load();

        if ($collection->getSize()) {
            $product = $collection->getFirstItem();
            Mage::dispatchEvent('catalog_product_load_after', array('product' => $product)); 

            return $product;
        }

        return null;
    }

    /**
     * Uploads a batch of n products
     */
    public function uploadProducts()
    {
        Mage::log('uploadProducts');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToSort('entity_id', 'ASC')
            ->joinTable(
                'bootic/product_data',
                'magento_product_id=entity_id',
                array(
                    'bootic_product_id'     => 'bootic_product_id',
                    'bootic_stock_id'       => 'bootic_stock_id',
                    'bootic_status'         => 'bootic_status',
                    'creation_time'         => 'creation_time',
                    'update_time'           => 'update_time',
                    'is_info_synced'        => 'is_info_synced',
                    'is_stock_synced'       => 'is_stock_synced',
                    'upload_failures'       => 'upload_failures'
                ),
                "{{table}}.bootic_status=". Mage::getModel('bootic/product_data')->getStatusProcessing() .""
            )
            ->load()
        ;

        $count = 1;
        foreach ($collection as $product) {
            if ($count > $this->_limit) {
                break;
            }

            if ($this->_isProductUploadable($product)) {
                $booticProductId = $product->getBooticProductId();

                try {
                    if (empty($booticProductId)) {
                        if ($product->isConfigurable()) {
                            $this->makeConfigurableProduct($product);
                        } else {
                            $this->makeSimpleProduct($product);
                        }
                    } else {
                        if ($product->isConfigurable()) {
                            $this->editConfigurableProduct($product);
                        } else {
                            $this->editSimpleProduct($product);
                        }
                    }

                    // Everything went well, we flag product as ready to go
                    $statusPending = Mage::getModel('bootic/product_data')->getStatusPendingApproval();
                    $options = array(
                        'upload_failures'   => 0,
                        'is_info_synced'    => true
                    );
                    Mage::helper('bootic/product_data')->updateProductStatus($product, $statusPending, $options);

                    Mage::helper('bootic/log')->addLog($product->getId(), 'success', 'The product ' . $product->getName() . ' was succesfully created.');
                    $count ++;

                } catch (Bootic_Bootic_Exception $e) {
                    // If an error occured on one of the calls, we flag product as errored and log the errors
                    Mage::logException($e);
                    $statusError = Mage::getModel('bootic/product_data')->getStatusError();
                    Mage::helper('bootic/product_data')->updateProductStatus($product, $statusError);

                    Mage::helper('bootic/log')->addLog($product->getId(), 'error', $e->getMessage());
                    $count ++;

                } catch (Bootic_Api_Exception $e) {
                    // Probably a network error or an API downtime
                    // We try 5 times and then we notify Admin
                    Mage::logException($e);
                	$statusProcessing = Mage::getModel('bootic/product_data')->getStatusProcessing();
                    $productData = Mage::getModel('bootic/product_data')->load($product->getId());
                    $productData->setBooticStatus($statusProcessing);
                    $productData->incrementUploadFailures();
                    $productData->save();

                    // If 5 Api exceptions occur, we dispatch this event to notify the admin
                    if ($productData->getUploadFailures() > 4) {
                        $statusError = Mage::getModel('bootic/product_data')->getStatusError();
                        Mage::helper('bootic/product_data')->updateProductStatus($product, $statusError);

                        Mage::dispatchEvent('bootic_product_upload_failure', array('product' => $product));
                    }

                    $count ++;

                } catch (Exception $e) {
                    // If anything else weird happens, we re-queue the product
                    Mage::logException($e);
                	$statusProcessing = Mage::getModel('bootic/product_data')->getStatusProcessing();
                    Mage::helper('bootic/product_data')->updateProductStatus($product, $statusProcessing);

                    $count ++;

                }
            }
        }
    }

    /**
     * Edits a batch of n products
     */
    public function editProducts()
    {
        Mage::log('editProducts');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToSort('entity_id', 'ASC')
            ->joinTable(
                'bootic/product_data',
                'magento_product_id=entity_id',
                array(
                    'bootic_product_id'     => 'bootic_product_id',
                    'bootic_stock_id'       => 'bootic_stock_id',
                    'bootic_status'         => 'bootic_status',
                    'creation_time'         => 'creation_time',
                    'update_time'           => 'update_time',
                    'is_info_synced'        => 'is_info_synced',
                    'is_stock_synced'       => 'is_stock_synced',
                    'upload_failures'       => 'upload_failures'
                ),
                "({{table}}.bootic_status=". Mage::getModel('bootic/product_data')->getStatusCreated() .""
                    . " OR {{table}}.bootic_status=". Mage::getModel('bootic/product_data')->getStatusPendingApproval() .""
                    . " OR {{table}}.bootic_status=". Mage::getModel('bootic/product_data')->getStatusIncomplete() .")"
                    . " AND {{table}}.is_info_synced=0"
            )
            ->load()
        ;

        $count = 1;
        foreach ($collection as $product) {
            if ($count > $this->_limit) {
                break;
            }

            try {
                if ($product->isConfigurable()) {
                    $this->editConfigurableProduct($product);
                } else {
                    $this->editSimpleProduct($product);
                }

                // Everything went well, we set flag the product data to created and synced
                $statusCreated = Mage::getModel('bootic/product_data')->getStatusCreated();
                Mage::helper('bootic/product_data')->updateProductStatus($product, $statusCreated);
                Mage::helper('bootic/product_data')->setInfoSync($product, true);

                $count ++;

            } catch (Bootic_Bootic_Exception $e) {
                Mage::logException($e);
            	// If update fails or get warning, we show admin the product has errors
                $statusException = ($e->isWarning()) ? Mage::getModel('bootic/product_data')->getStatusIncomplete() : Mage::getModel('bootic/product_data')->getStatusError();
                $status = ($e->isWarning()) ? 'warning' : 'error';
                Mage::helper('bootic/product_data')->updateProductStatus($product, $statusException);

                Mage::helper('bootic/log')->addLog($product->getId(), $status, $e->getMessage());

                // If it's an error / then we disable the product and Admin will have to fix it
                if (!$e->isWarning()) {
                    $this->getBootic()->editProduct(array(
                        'product_id' => $product->getBooticProductId(),
                        'available' => false
                    ));
                }

            } catch (Exception $e) {
            	Mage::logException($e);
                // If anything else weird happens, we simply disregard
                Mage::log('Failed editing a product on Bootic');
            }
        }
    }

    /**
     * Tests is the product is uploadable to Bootic
     * Only Simple, Downloadable and Configurable products can be uploaded
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    protected function _isProductUploadable(Mage_Catalog_Model_Product $product)
    {
        Mage::log('_isProductUploadable');
        $parents = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());

        $result = true;

        if (!$product->isConfigurable() && $product->getTypeId() != 'simple' && $product->getType != 'downloadable') {
            Mage::helper('bootic/log')->addLog($product->getId(), 'error', 'This type of product cannot currently be uploaded to Bootic.');
            $result = false;
        } elseif (count($parents) > 0) {
            $result = false;
        }

        return $result;
    }

    /**
     * Adds a simple product to Bootic
     *
     * @param Mage_Catalog_Model_Product $product
     */
    public function makeSimpleProduct(Mage_Catalog_Model_Product $product)
    {
        Mage::log('makeSimpleProduct');
        $attributes = $product->getAttributes();
        $personalizations = array();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $personalizations[] = $attribute->getFrontendLabel() .': ' .$attribute->getFrontend()->getValue($product) . '<br/>';
            }
        }
        $product->setPersonalizations($personalizations);

        // We prepare the array to be uploaded
        $p = $this->_prepareBooticProductArray($product);

        // Here we go
        $result = $this->getBootic()->addProduct($p);

        if (!$result->isSuccess()) {
            throw new Bootic_Bootic_Exception($result->getErrorMessage());

        } else {
            $stockCombinations  = $result->getData('stock_combinations');
            $valid              = true;
            $stockId            = $stockCombinations[0]['stock_id'];
            $productId          = $result->getData('product_id');
            $sku                = $product->getSku();
            $stock              = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();

            $productData = Mage::getModel('bootic/product_data')->load($product->getId());
            $productData->setBooticProductId($productId);
            $productData->setBooticStockId($stockId );

            try {
                $this->updateProductStock($productId, $stockId, $sku, $stock, $valid);
                $productData->setIsStockSynced(true);
                $productData->save();

            } catch (Bootic_Api_Exception $e) {
            	Mage::logException($e);
                // If an error occurs during this call, product stock gets set to out of sync
                $productData->setIsStockSynced(false);
                $productData->save();
            }

            // If product upload call originally had warnings, we notify the admin
            if ($result->hasWarning()) {
                $warningMessages = $result->getWarningMessages();
                Mage::log($warningMessages);
                
                // We remove the first message which is not intended for Magento Admins
                array_shift($warningMessages);
                $message = implode(' ', $warningMessages);
                Mage::log($message);
                throw new Bootic_Bootic_Exception($message);
            }
        }
    }

    /**
     * Edits a simple product on Bootic
     *
     * @param Mage_Catalog_Model_Product $product
     * @throws Bootic_Bootic_Exception
     */
    public function editSimpleProduct(Mage_Catalog_Model_Product $product)
    {
        Mage::log('editSimpleProduct');
        $attributes = $product->getAttributes();
        $personalizations = array();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront()) {
                $personalizations[] = $attribute->getFrontendLabel() .': ' .$attribute->getFrontend()->getValue($product) . '<br/>';
            }
        }
        $product->setPersonalizations($personalizations);

        // We prepare the array to be uploaded
        $p = $this->_prepareBooticProductArray($product);

        if ($product->getBooticProductId() == null) {
            $productData = Mage::getModel('bootic/product_data')->load($product->getId());
            $product->setBooticProductId($productData->getBooticProductId());
        }

        $p = array_merge(array('product_id' => $product->getBooticProductId()), $p);

        // Here we go
        $result = $this->getBootic()->editProduct($p);

        if (!$result->isSuccess()) {
            throw new Bootic_Bootic_Exception($result->getErrorMessage());

        } elseif ($result->hasWarning()) {
            $warningMessages = $result->getWarningMessages();

            // We remove the first message which is not intended for Magento Admins
            array_shift($warningMessages);
            $message = implode(' ', $warningMessages);

            throw new Bootic_Bootic_Exception($message, 0, null, true);
        }
    }

    /**
     * Adds a configurable product to Bootic
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     * @throws Exception
     */
    public function makeConfigurableProduct(Mage_Catalog_Model_Product $product)
    {
        Mage::log('makeConfigurableProduct');
        $productAttributes = $this->_getConfigurableProductAttributes($product, $productOptions, $matchedAttributes);

        $attributes = $product->getAttributes();
        $personalizations = array();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute, $productOptions['attributes'])) {
                $personalizations[] = $attribute->getFrontendLabel() .': ' .$attribute->getFrontend()->getValue($product) . '<br/>';
            }
        }
        $product->setPersonalizations($personalizations);

        // We prepare array to get uploaded
        $p = $this->_prepareBooticProductArray($product, $productAttributes);

        // Here we go
        $result = $this->getBootic()->addProduct($p);

        if (!$result->isSuccess()) {
            throw new Bootic_Bootic_Exception($result->getErrorMessage());

        } else {
            $stockCombinations  = $result->getData('stock_combinations');
            $productId          = $result->getData('product_id');
            $stockInSync        = true;

            $productData = Mage::getModel('bootic/product_data')->load($product->getId());
            $productData->setBooticProductId($result->getData('product_id'));

            foreach ($stockCombinations as $stockCombination) {
                $productArray = array();

                foreach ($stockCombination['elements'] as $element) {

                    // now we loop through Mage matchedAttributes array to find a matching
                    foreach ($matchedAttributes as $attribute) {

                        foreach ($attribute['options'] as $option) {
                            if ($option['label'] == $element['value']) {
                                $productArray[] = $option['products'];
                            }
                        }
                    }
                }

                $prod = array();
                foreach ($productArray as $products) {
                    $prod = empty($prod) ? $products : array_intersect($prod, $products);
                }

                $valid      = false;
                $stockId    = $stockCombination['stock_id'];
                $sku        = '';
                $stock      = '';

                // If product exists, we set its stock combination
                if (!empty($prod)) {
                    $_product = Mage::getModel('catalog/product')->load(current($prod));
                    $_productData = Mage::getModel('bootic/product_data')->load($_product->getId());
                    $_productData->setBooticProductId($productId);
                    $_productData->setBooticStockId($stockId);

                    $valid  = true;
                    $sku    = $_product->getSku();
                    $stock  = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();

                    try {
                        $this->updateProductStock($productId, $stockId, $sku, $stock, $valid);
                        $_productData->setIsStockSynced(true);
                        $_productData->save();

                    } catch (Bootic_Api_Exception $e) {
                    	Mage::logException($e);
                    	 
                        // If an error occurs during this call, product stock gets set to out of sync
                        $_productData->setIsStockSynced(false);
                        $_productData->save();
                    }

                // If not, we just set its stock to not valid - no big deal if this one fails
                } else {
                    try {
                        $this->updateProductStock($productId, $stockId, $sku, $stock, $valid);
                    } catch (Exception $e) {
                    	Mage::logException($e);
                        $stockInSync = false;
                    }
                }
            }

            $productData->setIsStockSynced($stockInSync);
            $productData->save();

            // If product upload call originally had warnings, we notify the admin
            if ($result->hasWarning()) {
                $warningMessages = $result->getWarningMessages();
                Mage::log($warningMessages);
                // We remove the first message which is not intended for Magento Admins
                array_shift($warningMessages);
                $message = implode(' ', $warningMessages);
                Mage::log(message);
                
                throw new Bootic_Bootic_Exception($message);
            }
        }
    }

    /**
     * Edit a configurable product on Bootic
     *
     * @param Mage_Catalog_Model_Product $product
     * @throws Bootic_Bootic_Exception
     */
    public function editConfigurableProduct(Mage_Catalog_Model_Product $product)
    {
        Mage::log('editConfigurableProduct');
        $productAttributes = $this->_getConfigurableProductAttributes($product, $productOptions, $matchedAttributes);

        $attributes = $product->getAttributes();
        $personalizations = array();
        foreach ($attributes as $attribute) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute, $productOptions['attributes'])) {
                $personalizations[] = $attribute->getFrontendLabel() .': ' .$attribute->getFrontend()->getValue($product) . '<br/>';
            }
        }
        $product->setPersonalizations($personalizations);

        // We prepare array to get uploaded
        $p = $this->_prepareBooticProductArray($product, $productAttributes);

        if ($product->getBooticProductId() == null) {
            $productData = Mage::getModel('bootic/product_data')->load($product->getId());
            $product->setBooticProductId($productData->getBooticProductId());
        }

        $p = array_merge(array('product_id' => $product->getBooticProductId()), $p);

        // Here we go
        $result = $this->getBootic()->editProduct($p);

        if (!$result->isSuccess()) {
            throw new Bootic_Bootic_Exception($result->getErrorMessage());
        } elseif ($result->hasWarning()) {
            $warningMessages = $result->getWarningMessages();
                            
            Mage::log($warningMessages);
            
            // We remove the first message which is not intended for Magento Admins
            array_shift($warningMessages);
            $message = implode(' ', $warningMessages);
            Mage::log($message);
            
            throw new Bootic_Bootic_Exception($message, 0, null, true);
        }
    }

    protected function _getConfigurableProductAttributes(Mage_Catalog_Model_Product $product, &$productOptions, &$matchedAttributes)
    {
        Mage::log('_getConfigurableProductAttributes');
        $productOptions = Mage::helper('bootic/product_type_configurable')->getOptions($product);

        $matchedAttributes = array();
        foreach ($productOptions['attributes'] as $attribute) {
            $exists = false;
            foreach ($this->getBooticAttributes() as $booticAttribute) {
                if ($attribute['code'] == $booticAttribute['name']) {
                    $matchedAttributes[$booticAttribute['id']] = $attribute;
                    $exists = true;
                    break;
                }
            }

            if (!$exists) {
                $booticAttribute = $this->createProductAttribute($attribute['code']);
                $matchedAttributes[$booticAttribute['id']] = $attribute;
            }
        }

        // We prepare the string for bootic's product_attributes property
        // should look something like 'attributeId,price(i.e. 2.00),qty;attributeId,price,etc...'
        $productAttributes = '';
        foreach ($matchedAttributes as $id => $attribute) {
            foreach ($attribute['options'] as $option) {
                $productAttributes .= $id . ',';
                $productAttributes .= number_format($option['price'], 2) . ',';
                $productAttributes .= $option['label'] . ';';
            }
        }

        return $productAttributes;
    }

    /**
     * Updates stock for a product
     *
     * @param null $stockId
     * @param null $sku
     * @param null $stock
     * @param bool $valid
     * @return bool
     * @throws Exception
     */
    public function updateProductStock($productId = null, $stockId = null, $sku = null, $stock = null, $valid = false)
    {
        Mage::log('updateProductStock');
        $p['product_id']    = (int)$productId;
        $p['stock_id']      = $stockId;
        $p['sku']           = $sku;
        $p['stock']         = (int)$stock;
        $p['valid']         = $valid;

        $result = $this->getBootic()->updateProductStock($p);

        if (!$result->isSuccess()) {
            throw new Bootic_Bootic_Exception($result->getErrorMessage());
        }

        return $result;
    }

    /**
     * Lists all attributes existing on Bootic
     *
     * @return array|mixed
     */
    public function listProductAvailableAttributes()
    {
        Mage::log('listProductAvailableAttributes');
        $result = $this->getBootic()->listProductAvailableAttributes();

        return $result->getData();
    }

    /**
     * Creates a new attribute on Bootic
     *
     * @param $attributeCode
     * @return array|bool|mixed
     * @throws Exception
     */
    public function createProductAttribute($attributeCode)
    {
        Mage::log('createProductAttribute');
        if (is_null($attributeCode)) {
            throw new Bootic_Bootic_Exception('Attribute code cannot be empty');
        }

        $exist = false;
        $existingAttributes = $this->getBooticAttributes();

        foreach ($existingAttributes as $existingAttribute) {
            if ($attributeCode == $existingAttribute['name']) {
                $exist = true;
            }
        }

        if (!$exist) {
            $result = $this->getBootic()->createProductAttribute(array(
                'name' => $attributeCode
            ));

            if ($result->isSuccess()) {
                $this->addBooticAttribute($result->getData('attribute'));
                return $result->getData('attribute');
            }
        }

        return false;
    }

    /**
     * Gets all locally stored Bootic attributes
     *
     * @return array
     */
    public function getBooticAttributes()
    {
        if (is_null($this->_booticAttributes)) {
            $this->_booticAttributes = $this->listProductAvailableAttributes();
        }

        return $this->_booticAttributes;
    }

    /**
     * Add a new attribute to the local stored Bootic attributes
     *
     * @param array $attribute
     * @return Bootic_Bootic_Helper_Product
     */
    public function addBooticAttribute(array $attribute)
    {
        Mage::log('addBooticAttribute');
        $this->_booticAttributes[] = $attribute;
        return $this;
    }

    /**
     * Prepares and formats the product array to be uploaded to Bootic
     *
     * @param $product
     * @param null $product_attributes
     * @return array
     */
    private function _prepareBooticProductArray(Mage_Catalog_Model_Product $_product, $product_attributes = null)
    {
        Mage::log('_prepareBooticProductArray');
        /** @var $product Mage_Catalog_Model_Product */
        $product = Mage::getModel('catalog/product')->load($_product->getId());

        $p['product_name'] = $product->getName();

        // Here we add all the attributes to the long description
        $p['long_desc'] = $product->getDescription();
        $productPersonalizations = $_product->getPersonalizations();
        foreach ($productPersonalizations as $personalization) {
            $p['long_desc'] .= '<p>' . $personalization . '</p>';
        }

        // Truncate the string
        $shortDesc = Mage::helper('core/string')->truncate($product->getShortDescription(), 120, '...');
        // Replace line breaks with spaces
        $shortDesc = preg_replace("/[\n\r\t]/"," ", $shortDesc);
        // Make sure everything is UTF8
        $p['short_desc'] = Mage::helper('core/string')->cleanString($shortDesc);
        $p['price'] = floatval($product->getPrice());
        $p['in_stock'] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getIsInStock();
        $p['supplier_reference'] = $product->getSku();

        // Category Mapping
        $categoryCollection = Mage::helper('bootic/category')->getMappedCategoryCollection($product);

        if (count($categoryCollection) == 0) {
            throw new Bootic_Bootic_Exception('Product\'s Magento category needs to be mapped to a Bootic category in Bootic > Catalog > Category Mapping');
        }

        $i = 1;
        $secondaryCategories = null;
        foreach ($categoryCollection as $category) {
            if ($i == 1) {
                $p['category'] = (int) $category->getBooticCategoryId();
            } else {
                $secondaryCategories .= (int) $category->getBooticCategoryId() . ',';
            }
            $i++;
        }

        if ($secondaryCategories) {
            $secondaryCategories = rtrim($secondaryCategories, ',');
            $p['secondary_category'] = $secondaryCategories;
        }

        $p['can_others_sell'] = true;
        $p['others_can_edit_price'] = false;
        $p['others_can_edit_content'] = false;
        $p['monthly_sales_req_for_bonus'] = Mage::getStoreConfig('bootic/sales/monthly_sales_req_for_bonus');
        $p['bonus_amount'] = Mage::getStoreConfig('bootic/sales/bonus_amount');
        $p['commission'] = Mage::getStoreConfig('bootic/sales/commission');

        // We get the Main image
        $mainImage = Mage::getStoreConfig('bootic/product/image') ? Mage::getStoreConfig('bootic/product/image') : 'image';
        $img = file_get_contents(str_replace(':8080', '', $product->getMediaConfig()->getMediaUrl($product->getData($mainImage))));
        $imgEncoded = base64_encode($img);
        $p['upload_file_method'] = 'base64';
        $p['product_image_1'] = $imgEncoded;

        // We get the product's image gallery - Bootic limits to 4 additional images
        $gallery = $product->getMediaGalleryImages();
        $i = 2;
        foreach ($gallery as $image) {
            if ($i > 5) break;
            $img = file_get_contents(str_replace(':8080', '', Mage::helper('catalog/image')->init($product, 'thumbnail', $image->getFile())->__toString()));
            $imgEncoded = base64_encode($img);
            $index = 'product_image_' . $i;
            $p[$index] = $imgEncoded;
            $i++;
        }

        // Default options - non editable from Magento
        $p['need_quotation']    = false;
        $p['publish']           = true;
        $p['available']         = true;
        $p['category_linked']   = true;
        $p['position_in_shop']  = 500;
        $p['active_in_shop']    = true;

        // Options from config
        if ($brandName = Mage::getStoreConfig('bootic/product/brand_name')) {
            $p['brand_name'] = $product->getResource()->getAttribute($brandName)->getFrontend()->getValue($product);
        }

        if ($warrantyMonths = Mage::getStoreConfig('bootic/product/warranty_months')) {
            $p['warranty_months'] = $product->getResource()->getAttribute($warrantyMonths)->getFrontend()->getValue($product);
        }

        if ($isbn = Mage::getStoreConfig('bootic/product/isbn')) {
            $p['isbn'] = $product->getResource()->getAttribute($isbn)->getFrontend()->getValue($product);
        }

        if ($ean = Mage::getStoreConfig('bootic/product/ean')) {
            $p['ean13'] = $product->getResource()->getAttribute($ean)->getFrontend()->getValue($product);
        }

        if ($upc = Mage::getStoreConfig('bootic/product/upc')) {
            $p['upc'] = $product->getResource()->getAttribute($upc)->getFrontend()->getValue($product);
        }

        if ($pkgWidth = Mage::getStoreConfig('bootic/product/pkg_width')) {
            $p['pkg_width'] = $product->getResource()->getAttribute($pkgWidth)->getFrontend()->getValue($product);
        }

        if ($pkgLength = Mage::getStoreConfig('bootic/product/pkg_length')) {
            $p['pkg_length'] = $product->getResource()->getAttribute($pkgLength)->getFrontend()->getValue($product);
        }

        if ($pkgHeight = Mage::getStoreConfig('bootic/product/pkg_height')) {
            $p['pkg_height'] = $product->getResource()->getAttribute($pkgHeight)->getFrontend()->getValue($product);
        }

        if ($pkgWeight = Mage::getStoreConfig('bootic/product/pkg_weight')) {
            $p['pkg_weight'] = $product->getResource()->getAttribute($pkgWeight)->getFrontend()->getValue($product);
        }

        if (!is_null($product_attributes)) {
            $p['product_attributes'] = $product_attributes;
        }

        return $p;
    }

    /**
     * Checks the status of a product on Bootic
     * Pending Approval, Accepted, Denied
     */
    public function checkProductsStatus()
    {
        Mage::log('checkProductsStatus');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToSort('entity_id', 'ASC')
            ->joinTable(
                'bootic/product_data',
                'magento_product_id=entity_id',
                array(
                    'bootic_product_id'     => 'bootic_product_id',
                    'bootic_stock_id'       => 'bootic_stock_id',
                    'bootic_status'         => 'bootic_status',
                    'creation_time'         => 'creation_time',
                    'update_time'           => 'update_time',
                    'is_info_synced'        => 'is_info_synced',
                    'is_stock_synced'       => 'is_stock_synced',
                    'upload_failures'       => 'upload_failures'
                ),
                "{{table}}.bootic_status=". Mage::getModel('bootic/product_data')->getStatusPendingApproval() .""
            )
            ->load()
        ;

        foreach ($collection as $product) {
            // If product has parents, no need to query the API as things will happen at the parent level
            if ($product->getTypeId() == 'simple') {
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                if (count($parentIds) > 0) {
                    continue;
                }
            }

            $approved           = null;
            $approval_remark    = null;

            $productInfo = $this->getBootic()->getProductInfo(array(
                'product_id' => $product->getBooticProductId()
            ));

            $approved = $productInfo->getData('approved');
            $approval_remark = $productInfo->getData('approval_remark');

            switch ($approved) {
                // Case 1: Approved
                case 1:
                    Mage::helper('bootic/product_data')->updateProductStatus($product, Mage::getModel('bootic/product_data')->getStatusCreated());
                    Mage::helper('bootic/log')->addLog($product->getId(), 'success', Mage::helper('bootic')->__('The product was approved'));
                    break;
                // Cases -1: Rejected ; -2: Definitely Rejected
                case -1:
                case -2:
                    Mage::helper('bootic/product_data')->updateProductStatus($product, Mage::getModel('bootic/product_data')->getStatusNotApproved());
                    Mage::helper('bootic/log')->addLog($product->getId(), 'error', Mage::helper('bootic')->__('The product was denied. ') . $approval_remark);
                    break;
                // Case -3: Deleted , we clear Bootic's product data
                case -3:
                    $id = $product->getId();
                    Mage::helper('bootic/product_data')->resetProduct($id);
                    Mage::helper('bootic/log')->addLog($product->getId(), 'warning', Mage::helper('bootic')->__('The product was deleted from Bootic'));
                    break;
                default:
                    // We do nothing
            }
        }
    }

    /**
     * Synchronizes products stocks
     */
    public function syncProductsStocks()
    {
        Mage::log('syncProductsStocks');
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToSort('entity_id', 'ASC')
            ->joinTable(
                'bootic/product_data',
                'magento_product_id=entity_id',
                array(
                    'bootic_product_id'     => 'bootic_product_id',
                    'bootic_stock_id'       => 'bootic_stock_id',
                    'bootic_status'         => 'bootic_status',
                    'creation_time'         => 'creation_time',
                    'update_time'           => 'update_time',
                    'is_info_synced'        => 'is_info_synced',
                    'is_stock_synced'       => 'is_stock_synced',
                    'upload_failures'       => 'upload_failures'
                ),
                "{{table}}.is_stock_synced = 0"
                    . " AND {{table}}.bootic_product_id != 0"
            )
            ->load()
        ;

        foreach ($collection as $product) {
            // At this point, we should not have configurable products, but in case we do
            // we skip them
            if ($product->isConfigurable()) {
                continue;
            }

            $productId  = $product->getBooticProductId();
            $stockId    = $product->getBooticStockId();
            $sku        = $product->getSku();
            $stock      = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product)->getQty();
            $valid      = true;

            try {
                $this->updateProductStock($productId, $stockId, $sku, $stock, $valid);
                Mage::helper('bootic/product_data')->setStockSync($product, true);
            } catch (Exception $e) {
            	Mage::logException($e);
                // Here we just do nothing to let the system retry on its own on a next cron run
            }
        }
    }
}
