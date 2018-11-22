<?php
/**
 * Created by PhpStorm.
 * User: peter
 * Date: 22.11.18
 * Time: 22:44
 */

class CoinPayments_CoinPayments_Model_System_Config_Source_Rate
{
    protected  $_empty = '--Please select currency--';

    public function toOptionArray()
    {
        $rates = Mage::getModel('coinpayments/api_rate')->getAvailableRates();

        $options = [];
        $options[] = [
            'value' => '',
            'label' => $this->_empty,
        ];

        if (is_object($rates)) {
            foreach ($rates as $key => $rate) {
                $options[] = ['value' => $key, 'label' => $rate->name];
            }
        } else {
            $options[] = ['value' => '', 'label' => $rates];
        }

        return $options;
    }
}