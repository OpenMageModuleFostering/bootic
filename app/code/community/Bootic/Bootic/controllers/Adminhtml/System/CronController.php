<?php

class Bootic_Bootic_Adminhtml_System_CronController extends Mage_Adminhtml_Controller_action
{
    public function runProcessOrdersAction()
    {
        Mage::log('runProcessOrdersAction');
        try {
            $orders = $this->getCronObserver()->processOrders();
            $response = array(
                'success' => 1,
                'orderCount' => count($orders),
            );
        } catch(Exception $e){
            $response = array(
                'success' => 0,
                'error' => $e->getMessage(),
            );
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(json_encode($response));
    }

    /**
     * @return Bootic_Bootic_Model_OrdersCronObserver
     */
    public function getCronObserver()
    {
        Mage::log('getCronObserver');
        return Mage::getModel('bootic/OrdersCronObserver');
    }
}
