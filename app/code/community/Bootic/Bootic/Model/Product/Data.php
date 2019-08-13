<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Model_Product_Data extends Mage_Core_Model_Abstract
{
    /**
     * Display Status "Not Created"
     */
    const BOOTIC_STATUS_NOT_CREATED = 0;

    /**
     * Display Status "Processing"
     */
    const BOOTIC_STATUS_PROCESSING = 1;

    /**
     * Display Status "Incomplete"
     */
    const BOOTIC_STATUS_INCOMPLETE = 2;

    /**
     * Display Status "Pending Approval"
     */
    const BOOTIC_STATUS_PENDING_APPROVAL = 3;

    /**
     * Display Status "Not Approved"
     */
    const BOOTIC_STATUS_NOT_APPROVED = 4;

    /**
     * Display Status "Created"
     */
    const BOOTIC_STATUS_CREATED = 5;

    /**
     * Display Status "Error"
     */
    const BOOTIC_STATUS_ERROR = 6;


    protected function _construct()
    {
        $this->_init('bootic/product_data');
    }

    /**
     * Get options as array
     *
     * @return array
     */
    static public function getOptionArray()
    {
        return array(
            self::BOOTIC_STATUS_NOT_CREATED => Mage::helper('bootic')->__('Not Created'),
            self::BOOTIC_STATUS_PROCESSING => Mage::helper('bootic')->__('Processing'),
            self::BOOTIC_STATUS_INCOMPLETE => Mage::helper('bootic')->__('Incomplete'),
            self::BOOTIC_STATUS_PENDING_APPROVAL => Mage::helper('bootic')->__('Pending Approval'),
            self::BOOTIC_STATUS_NOT_APPROVED => Mage::helper('bootic')->__('Not Approved'),
            self::BOOTIC_STATUS_CREATED => Mage::helper('bootic')->__('Created'),
            self::BOOTIC_STATUS_ERROR => Mage::helper('bootic')->__('Error')
        );
    }

    public function getStatusNotCreated()
    {
        return self::BOOTIC_STATUS_NOT_CREATED;
    }

    public function getStatusProcessing()
    {
        return self::BOOTIC_STATUS_PROCESSING;
    }

    public function getStatusIncomplete()
    {
        return self::BOOTIC_STATUS_INCOMPLETE;
    }

    public function getStatusPendingApproval()
    {
        return self::BOOTIC_STATUS_PENDING_APPROVAL;
    }

    public function getStatusNotApproved()
    {
        return self::BOOTIC_STATUS_NOT_APPROVED;
    }

    public function getStatusCreated()
    {
        return self::BOOTIC_STATUS_CREATED;
    }

    public function getStatusError()
    {
        return self::BOOTIC_STATUS_ERROR;
    }

    public function incrementUploadFailures()
    {
        $this->upload_failures ++;
    }

    public function resetUploadFailures()
    {
        $this->upload_failures = 0;
    }

    public function isProcessable(Mage_Catalog_Model_Product $product)
    {
        $productData = $this->load($product->getId());

        if ($productData->getData('bootic_status') != self::BOOTIC_STATUS_NOT_CREATED) {
            return false;
        }

        return true;
    }
}
