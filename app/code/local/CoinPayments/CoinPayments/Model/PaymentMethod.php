<?php

/*
Copyright 2013 CoinPayments.net.

Based on the Mygateway Payment Controller demo
By: Junaid Bhura
www.junaidbhura.com
*/

class CoinPayments_CoinPayments_Model_PaymentMethod extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'coinpayments';

    protected $_isInitializeNeeded = true;
    protected $_canUseInternal = true;
    protected $_canUseForMultishipping = false;

    protected $_formBlockType = 'coinpayments/form_coinPayments';
    protected $_infoBlockType = 'coinpayments/info_coinPayments';

    public function assignData($data)
    {
        $session = Mage::getSingleton('core/session');
//        if ($data->getData('_buyer_email')) {
//            $session->setBuyerEmail($data->getData('_buyer_email'));
//        }

        if ($data->getData('_currency2')) {
            $session->setCurrency2($data->getData('_currency2'));
        }

        return $this;
    }

    public function validate()
    {
        parent::validate();

        $session = Mage::getSingleton('core/session');
//        if (!$session->getBuyerEmail()) {
//            $errorMsg = $this->_getHelper()->__("Buyer Email is a required field.\n");
//        }

        if (!$session->getCurrency2()) {
            $errorMsg = $this->_getHelper()->__('Currency is a required field.');
        }

        if ($errorMsg) {
            Mage::throwException($errorMsg);
        }

        return $this;
    }


    public function getOrderPlaceRedirectUrl()
    {
        $isDirect = Mage::getStoreConfig('payment/coinpayments/is_direct_mode');

        if ($isDirect) {
            return Mage::getUrl('coinpayments/direct/transaction', array('_secure' => true));
        }
        return Mage::getUrl('coinpayments/payment/redirect', array('_secure' => true));
    }

    public function getTitle()
    {
        $imageUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) .
            'frontend/base/default/images/coinpayments_logo.png';
        $image = "<img src='$imageUrl' style='vertical-align: -webkit-baseline-middle;
                                              width: 38px;
                                              height: 53px;'>";
        return $image . ' ' . parent::getTitle();
    }
}

