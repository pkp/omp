<?php

/**
 * @file classes/payment/omp/OMPPaymentManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPPaymentManager
 * @ingroup payment
 * @see OMPQueuedPayment
 *
 * @brief Provides payment management functions.
 *
 */

import('classes.payment.omp.OMPQueuedPayment');
import('lib.pkp.classes.payment.PaymentManager');

define('PAYMENT_TYPE_PURCHASE_PUBLICATION_FORMAT',	0x000000001);

class OMPPaymentManager extends PaymentManager {
	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function OMPPaymentManager(&$request) {
		parent::PaymentManager($request);
	}

	/**
	 * Determine whether the payment system is configured.
	 * @return boolean true iff configured
	 */
	function isConfigured() {
		return parent::isConfigured();
	}

	/**
	 * Create a queued payment.
	 * @param $pressId int ID of press payment applies under
	 * @param $type int PAYMENT_TYPE_...
	 * @param $userId int ID of user responsible for payment
	 * @param $assocId int ID of associated entity
	 * @param $amount numeric Amount of currency $currencyCode
	 * @param $currencyCode string optional ISO 4217 currency code
	 * @return QueuedPayment
	 */
	function &createQueuedPayment($pressId, $type, $userId, $assocId, $amount, $currencyCode = null) {
		fatalError('NEED TO GET $currencyCode');
		$payment = new OMPQueuedPayment($amount, $currencyCode, $userId, $assocId);
		$payment->setPressId($pressId);
		$payment->setType($type);

	 	switch ($type) {
			case PAYMENT_TYPE_PURCHASE_PUBLICATION_FORMAT:
				fatalError('Unimplemented');
				break;
			default:
				// Invalid payment type.
				assert(false);
				break;
		}

		return $payment;
	}

	/**
	 * Get the payment plugin.
	 * @param $press Press
	 * @return PaymentPlugin
	 */
	function &getPaymentPlugin($press) {
		$paymentMethodPluginName = $press->getSetting('paymentMethodPluginName');
		$paymentMethodPlugin = null;
		if (!empty($paymentMethodPluginName)) {
			$plugins =& PluginRegistry::loadCategory('paymethod');
			if (isset($plugins[$paymentMethodPluginName])) $paymentMethodPlugin =& $plugins[$paymentMethodPluginName];
		}
		return $paymentMethodPlugin;
	}

	/**
	 * Fulfill a queued payment.
	 * @param $queuedPayment QueuedPayment
	 * @param $payMethodPluginName string Name of payment plugin.
	 * @return mixed Dependent on payment type.
	 */
	function fulfillQueuedPayment(&$queuedPayment, $payMethodPluginName = null) {
		$returner = false;
		if ($queuedPayment) switch ($queuedPayment->getType()) {
			case PAYMENT_TYPE_PURCHASE_PUBLICATION_FORMAT:
				fatalError('Unimplemented');
				break;
			default:
				// Invalid payment type
				assert(false);
		}

		return $returner;
	}
}

?>
