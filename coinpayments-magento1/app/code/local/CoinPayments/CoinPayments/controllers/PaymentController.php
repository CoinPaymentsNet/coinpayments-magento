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
	
	private function _errorAndDie($error_msg) {		
		$email = Mage::getStoreConfig('payment/coinpayments/ipn_debug');
		if (!empty($email)) {
			$report = "Error Message: ".$error_msg."\n\n";
	
			$report .= "AUTH User: ".$_SERVER['PHP_AUTH_USER']."\n";
			$report .= "AUTH Pass: ".$_SERVER['PHP_AUTH_PW']."\n\n";
				
			$report .= "POST Fields\n\n";
			foreach ($_POST as $key => $value) {
				$report .= $key . '=' . html_entity_decode($value, ENT_QUOTES, 'UTF-8'). "\n";
			}
				
			@mail($email, "CoinPayments.net Invalid IPN", $report);
		}
		die('IPN Error: '.$error_msg);
	}
	
	function _is_ipn_valid($ipn, $order = null) {
		if (!isset($ipn['ipn_mode'])) {
			$this->_errorAndDie('IPN received with no ipn_mode.');
		}
		if ($ipn['ipn_mode'] == 'hmac') {
			if (!isset($_SERVER['HTTP_HMAC']) || empty($_SERVER['HTTP_HMAC'])) {
				$this->_errorAndDie('No HMAC signature sent.');
			}

			$request = file_get_contents('php://input');
			if ($request === FALSE || empty($request)) {
				$this->_errorAndDie('Error reading POST data: '.print_r($_SERVER, TRUE).'/'.print_r($_POST, TRUE));
			}

			$merchant = isset($ipn['merchant']) ? $ipn['merchant']:'';
			if (empty($merchant)) {
				$this->_errorAndDie('No Merchant ID passed');
			}
			if ($merchant != trim(Mage::getStoreConfig('payment/coinpayments/merchant_id'))) {
				$this->_errorAndDie('Invalid Merchant ID');
			}

			$hmac = hash_hmac("sha512", $request, trim(Mage::getStoreConfig('payment/coinpayments/ipn_secret')));
			if ($hmac != $_SERVER['HTTP_HMAC']) {
				$this->_errorAndDie('HMAC signature does not match');
			}

			return TRUE;
		} else if ($ipn['ipn_mode'] == 'httpauth' && Mage::getStoreConfig('payment/coinpayments/ipn_mode') == 1) {
			if (isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] == trim(Mage::getStoreConfig('payment/coinpayments/merchant_id'))) {
				if (isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] == trim(Mage::getStoreConfig('payment/coinpayments/ipn_secret'))) {
					$merchant = isset($ipn['merchant']) ? $ipn['merchant']:'';
					if (empty($merchant)) {
						$this->_errorAndDie('No Merchant ID passed');
					}
					if ($merchant != trim(Mage::getStoreConfig('payment/coinpayments/merchant_id'))) {
						$this->_errorAndDie('Invalid Merchant ID');
					}
					return TRUE;
				} else {
					$this->_errorAndDie('IPN Secret not correct or no HTTP Auth variables passed. If you are using PHP in CGI mode try the HMAC method.');
				}
			} else {
				$this->_errorAndDie('Merchant ID not correct or no HTTP Auth variables passed. If you are using PHP in CGI mode try the HMAC method.');
			}
		} else {
			$this->_errorAndDie('Unknown ipn_mode.');
		}
		return true;
	}
	
	// The response action is triggered when your gateway sends back a response after processing the customer's payment
	public function responseAction() {
		if ($this->getRequest()->isPost()) {
			if ($this->_is_ipn_valid($_POST)) {
				// Payment was successful, so update the order's state, send order email and move to the success page
				$order_id = intval($_POST['invoice']);
				$order = Mage::getModel('sales/order');
				if ($order->loadByIncrementId($order_id)) {
					if (in_array($order->getState(), array(
					    Mage_Sales_Model_Order::STATE_NEW,
                        Mage_Sales_Model_Order::STATE_PENDING_PAYMENT)
                    )) {
						if ($_POST['ipn_type'] == 'button') {
							if ($_POST['currency1'] == $order->getBaseCurrency()->getCode()) {
								if ($_POST['amount1'] >= $order->getBaseGrandTotal()) {
									$status = intval($_POST['status']);

									if ($status < 0) {
										//canceled or timed out
										$order->cancel();
										$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, 'CoinPayments.net Payment Status: '.$_POST['status_text']);
									} else if ($status >= 100 || $status == 2) {										
										//order complete or queued for nightly payout
										$str = 'CoinPayments.net Payment Status: '.$_POST['status_text'].'<br />';
										$str .= 'Transaction ID: '.$_POST['txn_id'].'<br />';
										$str .= 'Original Amount: '.sprintf('%.08f', $_POST['amount1']).' '.$_POST['currency1'].'<br />';
										$str .= 'Received Amount: '.sprintf('%.08f', $_POST['amount2']).' '.$_POST['currency2'];
										$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $str);
										$order->sendNewOrderEmail();
										$order->setEmailSent(true);						
									} else {
										//order pending
										$order->setState(Mage_Sales_Model_Order::STATE_NEW, true, 'CoinPayments.net Payment Status: '.$_POST['status_text']);					
									}
									$order->save();
									die('IPN OK');
								} else {
									$this->_errorAndDie('Amount paid is less than order total!', $order);
								}
							} else {
								$this->_errorAndDie('Original currency does not match!', $order);							
							}						
						} else {
							$this->_errorAndDie('Invalid IPN type!', $order);
						}
					} else {
						$this->_errorAndDie('Order is no longer new. (most likely IPN has already been processed)');
					}
				} else {
					$this->_errorAndDie('Could not load order with ID: '.$order_id);
				}
			}
		} else {
			$this->_errorAndDie('Request is not POST');
		}
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