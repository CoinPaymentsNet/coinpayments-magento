<?php

class CoinPayments_CoinPayments_DirectController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var CoinPayments_CoinPayments_Helper_Data
     */
    protected $_helper;

    private $_coinpaymentsDomain = 'https://www.coinpayments.net/api.php';


    public function _construct()
    {
        $this->_helper = Mage::helper('coinpayments/data');
    }

    /**
     * @throws Exception
     * @throws Mage_Core_Model_Store_Exception
     */
    public function transactionAction()
    {
        $order = new Mage_Sales_Model_Order();
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order->loadByIncrementId($orderId);

        $data = $this->_helper->getTransactionRequestData($order);
        $headers = $this->_helper->getTransactionRequestHeaders($data);

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->_coinpaymentsDomain);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($curl);
        $responseData = json_decode($result);

        Mage::log(print_r($responseData, true), null, 'response.log');
        if ($responseData->error == 'ok') {
            $order->addStatusHistoryComment("Transaction was created!<br> Status Url: " . $responseData->result->status_url);
            $order->setStatus(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT);
            $order->save();
            $this->_helper->storeResponseToSession($responseData);
            $this->_redirect('coinpayments/direct/status', array('_secure' => true));
            return;
        } else {
            $order->setStatus(Mage_Sales_Model_Order::STATE_NEW)->setState(Mage_Sales_Model_Order::STATE_NEW);
            $order->save();
            $this->_helper->storeResponseToSession($responseData);
            $this->_redirect('coinpayments/direct/status', array('_secure' => true));
            return;
        }
    }

    public function statusAction()
    {
        $order = new Mage_Sales_Model_Order();
        $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order->loadByIncrementId($orderId);

        if ($order->getId()) {
            $this->loadLayout();
            $block = $this->getLayout()->createBlock(
                'Mage_Core_Block_Template',
                'coinpayments', array('template' => 'coinpayments/status.phtml')
            );

            $this->getLayout()->getBlock('content')->append($block);
            $this->renderLayout();
            return;
        }

        $this->_redirect('checkout/onepage/error', array('_secure' => true));
        return;
    }

}