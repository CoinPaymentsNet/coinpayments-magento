<?php

class CoinPayments_CoinPayments_Block_Info_CoinPayments extends Mage_Payment_Block_Info
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('coinpayments/info/coinpayments.phtml');
    }

    /**
     * @return array
     */
    public function getSpecificInformation()
    {
        $session = Mage::getSingleton('core/session');

        $data = array();
//        $data[Mage::helper('payment')->__('Buyer Email')] = $session->getBuyerEmail();
        $data[Mage::helper('payment')->__('Currency')] = $session->getCurrency2();

        return $data;
    }

}