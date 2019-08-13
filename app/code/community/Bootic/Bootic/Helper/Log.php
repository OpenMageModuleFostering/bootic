<?php
/*
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Helper_Log extends Mage_Core_Helper_Abstract
{
    /**
     * Logs Info about a product upload
     *
     * @param $productId
     * @param string $status
     * @param string $message
     * @return false|Mage_Core_Model_Abstract
     */
    public function addLog($productId, $status = 'success', $message = '')
    {
        $log = Mage::getModel('bootic/log');

        $log->setProductId($productId);
        $log->setStatus($status);
        $log->setMessage($message);
        $log->setDate(date('Y-m-d H:i:s', Mage::getModel('core/date')->timestamp()));

        $log->save();

        return $log;
    }
}
