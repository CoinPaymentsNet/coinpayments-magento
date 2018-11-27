<?php

class CoinPayments_CoinPayments_Helper_Data extends Mage_Core_Helper_Data
{

    /**
     * @param $order
     * @return array
     * @throws Mage_Core_Model_Store_Exception
     */
    public function getTransactionRequestData($order)
    {
        $publicApiKey = Mage::getStoreConfig('payment/coinpayments/api_public_key');
        $session = Mage::getSingleton('core/session');
//        $storeCurrencty = Mage::app()->getStore()->getCurrentCurrencyCode();

        $items = $order->getAllVisibleItems();

        $skus = array();
        foreach ($items as $item) {
            $skus[] = $item->getName() . ' ' . $item->get;
        }

        $data = array(
            'version' => 1,
            'key' => $publicApiKey,
            'cmd' => 'create_transaction',
            'amount' => Mage::getModel('coinpayments/api_rate')
                ->getConverted($session->getCurrency2(), $order->getBaseGrandTotal()),
            'currency1' => $session->getCurrency2(),
            'currency2' => Mage::getStoreConfig('payment/coinpayments/receive_currency'),
            'buyer_email' =>  $order->getCustomerEmail(),
            'buyer_name' => $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname(),
            'item_name' => implode(', ', $skus),
            'invoice' => $order->getIncrementId(),
            'custom' => Mage::app()->getStore()->getStoreId(),
            'ipn_url' => Mage::getUrl('coinpayments/ipn/handle', array('_secure' => true)),
        );

        return $data;
    }

    /**
     * @param $data
     * @return array
     */
    public function getTransactionRequestHeaders($data)
    {
        $privateApiKey = Mage::getStoreConfig('payment/coinpayments/api_private_key');

        $headers = array(
            'HMAC:' . hash_hmac('sha512', http_build_query($data), $privateApiKey),
            'Content-Type:application/x-www-form-urlencoded'
        );
        return $headers;
    }

    public function storeResponseToSession($data)
    {
        $data = json_encode($data);

        $session = Mage::getSingleton('core/session');
        $session->setData('coinpayments_transaction_response', $data);

        return;
    }
}