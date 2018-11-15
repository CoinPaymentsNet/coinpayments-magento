<?php

class CoinPayments_CoinPayments_IpnController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var false|Coinpayments_CoinPayments_Model_Ipn
     */
    private $_ipnModel;

    public function _construct()
    {
        $this->_ipnModel = Mage::getModel('coinpayments/ipn');
    }

    public function handleAction()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json');
        $request = (object)$this->getRequest()->getParams();
        $hmac = $this->getRequest()->getHeader('HMAC');
        $order = Mage::getModel('sales/order')->loadByIncrementId($request->invoice);
        $error = [];

        if (!$order->getId()) {
            $order = Mage::getModel('sales/order')->load($request->invoice);
        }

        $this->_ipnModel
            ->setRequestData($request)
            ->setHmac($hmac)
            ->setOrder($order);

        $this->_ipnModel
            ->log('REQUEST: ' . print_r($request, true))
            ->log('HMAC: ' . $hmac);

        if ($error = $this->_ipnModel->validateIpn()) {
            $this->_ipnModel->log($error['error']);
            return $this->getResponse()->setBody(json_encode($error));
        }
        if (!$this->_ipnModel->checkHmac()) {
            $error['error'] = $this->__('Invalid HMAC signature');
            $this->_ipnModel->log($error['error']);
            return $this->getResponse()->setBody(json_encode($error));
        }

        $this->_ipnModel
            ->updateOrderPayment()
            ->updateOrderStatus()
            ->addToOrderHistory()
            ->addTransactionToOrder();

        try {
            $order->save();
        } catch (\Exception $e) {
            $error['error'] = $this->__('Error when save Order');
            $this->_ipnModel->log($error['error']);
            return $this->getResponse()->setBody(json_encode($error));
        }

        return $this->getResponse()->setBody(json_encode(['error' => 'OK']));
    }
}
