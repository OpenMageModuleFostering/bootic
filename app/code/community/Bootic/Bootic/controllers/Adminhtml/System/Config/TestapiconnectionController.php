<?php
/**
 * @copyright  Copyright (c) 2012 by  Bootic.
 */

class Bootic_Bootic_Adminhtml_System_Config_TestapiconnectionController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Perform connection credentials test
     *
     * @return Varien_Object
     */
    protected function _test()
    {
        return Mage::helper('bootic')->testApiConnection(
            $this->getRequest()->getParam('email'),
            $this->getRequest()->getParam('password')
        );
    }

    /**
     * Check whether connection can be established
     *
     * @return void
     */
    public function testAction()
    {
        $result = $this->_test();
        $this->getResponse()->setBody((int)$result->getIsConnected());
    }
}