<?php

class Coinpayments_CoinPayments_Model_Ipn extends Mage_Payment_Model_Method_Abstract
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_currentOrder;
    protected $_data;
    protected $_hmac;
    protected $_coinpaymentsStoreConfig;

    public function __construct()
    {
        $this->_coinpaymentsStoreConfig = Mage::getStoreConfig('payment/coinpayments');
    }

    public function setRequestData($data)
    {
        $this->_data = $data;
        return $this;
    }

    public function getRequestData()
    {
        return $this->_data;
    }
    public function setHmac($hmac)
    {
        $this->_hmac = $hmac;
        return $this;
    }
    public function getHmac()
    {
        return $this->_hmac;
    }

    public function setOrder($order)
    {
        $this->_currentOrder = $order;
        return $this;
    }

    public function getOrder()
    {
        return $this->_currentOrder;
    }

    public function checkHmac()
    {
        if (!$this->_hmac) {
            return false;
        }

        $serverHmac = hash_hmac(
            "sha512",
            http_build_query($this->_data),
            trim($this->_coinpaymentsStoreConfig['api_private_key'])
        );

        if ($this->_hmac != $serverHmac) {
            return false;
        }
        return true;
    }

    public function updateOrderPayment()
    {
        if ($this->_data['status'] == 100) {
            $this->_currentOrder->setTotalPaid($this->_data['amount1']);
        }
        return $this;
    }

    public function updateOrderStatus()
    {
        if ($this->_data['status'] == 100) {
            $this->_currentOrder
                ->setState(Mage_Sales_Model_Order::STATE_PROCESSING)
                ->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
        }
        return $this;
    }

    public function log($message)
    {
        $orderId = $this->_currentOrder->getId();
        Mage::log("Order $orderId: $message", null, 'coinpayments.log');
        return $this;
    }

    public function addToOrderHistory()
    {
        $str = 'CoinPayments.net Payment Status: <strong>' . $this->_data['status'] . '</strong> ' . $this->_data['status_text'] . '<br />';

        if ($this->_data['status'] == 100) {
            $str .= 'Transaction ID: ' . $this->_data['txn_id']
                . '<br />';
            $str .= 'Original Amount: ' . sprintf('%.08f', $this->_data['amount1'])
                . ' ' . $this->_data['currency1'] . '<br />';
            $str .= 'Received Amount: ' . sprintf('%.08f', $this->_data['amount2'])
                . ' ' . $this->_data['currency2'];
        }
        $this->_currentOrder->addStatusHistoryComment($str);
        return $this;
    }

    public function addTransactionToOrder()
    {
        if ($this->_data['status'] == 100) {
            $payment = $this->_currentOrder->getPayment();
            $payment->setTransactionId($this->_data['txn_id'])
                ->setCurrencyCode($this->_currentOrder->getBaseCurrencyCode())
                ->setPreparedMessage('Transaction paid')
                ->setIsTransactionClosed(true)
                ->setAdditionalInformation(
                    Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,
                    $this->_data
                )
                ->registerCaptureNotification($this->_currentOrder->getBaseGrandTotal());
            $payment->save();
        }
        return $this;
    }

    public function validateIpn()
    {
        if (!$this->_currentOrder->getId()) {
            return ['error' => 'order not found'];
        }

        if ($this->_currentOrder->getStatus() == Mage_Sales_Model_Order::STATE_PROCESSING) {
            return ['error' => 'order is already paid'];
        }

        return false;
    }
}