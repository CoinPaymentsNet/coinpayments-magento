<?php

class CoinPayments_CoinPayments_Block_Form_CoinPayments extends Mage_Payment_Block_Form
{
    private $_coinpaymentsDomain = 'https://www.coinpayments.net/api.php';
    private $_availableCoinsCmd = 'rates';
    private $_publicApiKey;
    private $_privateApiKey;

    protected function _construct()
    {
        parent::_construct();
        $isDirect = Mage::getStoreConfig('payment/coinpayments/is_direct_mode');
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('coinpayments/form/mark.phtml')
            ->setPaymentAcceptanceMarkSrc(Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
                'frontend/base/default/images/coinpayments_logo.png');
        if ($isDirect) {
            $this->setTemplate('coinpayments/form/coinpayments.phtml');
        }
        $this->setMethodTitle('')->setMethodLabelAfterHtml($mark->toHtml());

        $this->_publicApiKey = Mage::getStoreConfig('payment/coinpayments/api_public_key');
        $this->_privateApiKey = Mage::getStoreConfig('payment/coinpayments/api_private_key');
    }

    /**
     * @return mixed
     */
    public function getCurrentBillingInformation()
    {
        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $billingAddress = $quote->getBillingAddress()->getData();

        return $billingAddress;
    }

    /**
     * @return mixed
     */
    public function getListAvailableCoins()
    {
        $data = array(
            'version' => '1',
            'cmd' => $this->_availableCoinsCmd,
            'key' => $this->_publicApiKey
        );
        $headers = array(
            'HMAC:' . hash_hmac('sha512', http_build_query($data), $this->_privateApiKey),
            'Content-Type:application/x-www-form-urlencoded'
        );
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $this->_coinpaymentsDomain);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));

        $result = curl_exec($curl);
        $data = json_decode($result);

        if ($data->error == 'ok') {
            return $data->result;
        }
    }

    /**
     * @return mixed
     */
    public function getGrandTotal()
    {
        $grandTotal = Mage::getModel('checkout/session')->getQuote()->getGrandTotal();
        return $grandTotal;
    }
}