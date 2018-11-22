<?php
/**
 * Created by PhpStorm.
 * User: Peter Vayda
 * Date: 22.11.18
 * Time: 22:36
 */

class Coinpayments_CoinPayments_Model_Api_Rate
{
    private $_coinpaymentsDomain = 'https://www.coinpayments.net/api.php';

    private $_publicApiKey;

    private $_privateApiKey;

    public function __construct()
    {
        $this->_publicApiKey = Mage::getStoreConfig('payment/coinpayments/api_public_key');
        $this->_privateApiKey = Mage::getStoreConfig('payment/coinpayments/api_private_key');
    }

    public function getAvailableRates()
    {
        $data = array(
            'version' => '1',
            'cmd' => 'rates',
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
        return $data->error;
    }

    public function getConverted($currency, $amount)
    {
        $ratesData = $this->getAvailableRates();

        $rate = $currency != 'BTC' ?
            ($amount * $ratesData->USD->rate_btc) / $ratesData->$currency->rate_btc :
            $amount * $ratesData->USD->rate_btc;
        return round($rate, 8);
    }
}