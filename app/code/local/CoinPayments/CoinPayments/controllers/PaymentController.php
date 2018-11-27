<?php
/*
Copyright 2013 CoinPayments.net.

Based on the Mygateway Payment Controller demo
By: Junaid Bhura
www.junaidbhura.com
*/

class CoinPayments_CoinPayments_PaymentController extends Mage_Core_Controller_Front_Action {
	// The redirect action is triggered when someone places an order
	public function redirectAction() {
		$this->loadLayout();
		$block = $this->getLayout()->createBlock('Mage_Core_Block_Template','coinpayments',array('template' => 'coinpayments/redirect.phtml'));
		$this->getLayout()->getBlock('content')->append($block);
		$this->renderLayout();
	}
	
	// The cancel action is triggered when an order is to be cancelled
	public function cancelAction() {
		if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
			$order = Mage::getModel('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
			if($order->getId()) {
				// Flag the order as 'cancelled' and save it
				$order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'Order canceled')->save();
			}
		}
	}
}