<?php

/**
 * @file classes/payment/omp/OMPPaymentManager.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
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

define('PAYMENT_TYPE_PURCHASE_FILE',	0x000000001);

class OMPPaymentManager extends PaymentManager {
	/** @var $press Press */
	var $press;

	/**
	 * Constructor
	 * @param $request PKPRequest
	 */
	function __construct($request) {
		parent::__construct($request);
		$this->press = $request->getPress();
	}

	/**
	 * Determine whether the payment system is configured.
	 * @return boolean true iff configured
	 */
	function isConfigured() {
		return parent::isConfigured() && $this->press && $this->press->getSetting('currency');
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
		$pressDao = DAORegistry::getDAO('PressDAO');
		$press = $pressDao->getById($pressId);
		assert($press);
		$payment = new OMPQueuedPayment($amount, $press->getSetting('currency'), $userId, $assocId);
		$payment->setPressId($pressId);
		$payment->setType($type);

	 	switch ($type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				list($fileId, $revision) = array_map(create_function('$a', 'return (int) $a;'), explode('-', $assocId));
				import('lib.pkp.classes.submission.SubmissionFile'); // const
				$submissionFile =& $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF);
				assert($submissionFile);
				$payment->setRequestUrl($this->request->url(null, 'catalog', 'download', array(
					$submissionFile->getSubmissionId(),
					$submissionFile->getAssocId(),
					$assocId
				)));
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
	function &getPaymentPlugin() {
		$paymentMethodPluginName = $this->press->getSetting('paymentPluginName');
		$paymentMethodPlugin = null;
		if (!empty($paymentMethodPluginName)) {
			$plugins =& PluginRegistry::loadCategory('paymethod');
			if (isset($plugins[$paymentMethodPluginName])) $paymentMethodPlugin =& $plugins[$paymentMethodPluginName];
		}
		return $paymentMethodPlugin;
	}

	/**
	 * Fulfill a queued payment.
	 * @param $request PKPRequest
	 * @param $queuedPayment QueuedPayment
	 * @param $payMethodPluginName string Name of payment plugin.
	 * @return mixed Dependent on payment type.
	 */
	function fulfillQueuedPayment($request, &$queuedPayment, $payMethodPluginName = null) {
		$returner = false;
		if ($queuedPayment) switch ($queuedPayment->getType()) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				$returner = true;
				break;
			default:
				// Invalid payment type
				assert(false);
		}

		$ompCompletedPaymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO');
		$completedPayment =& $this->createCompletedPayment($queuedPayment, $payMethodPluginName);
		$ompCompletedPaymentDao->insertCompletedPayment($completedPayment);

		$queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
		$queuedPaymentDao->deleteQueuedPayment($queuedPayment->getId());

		return $returner;
	}

	/**
	 * Create a completed payment from a queued payment.
	 * @param $queuedPayment QueuedPayment Payment to complete.
	 * @param $payMethod string Name of payment plugin used.
	 * @return OMPCompletedPayment
	 */
	function &createCompletedPayment($queuedPayment, $payMethod) {
		import('classes.payment.omp.OMPCompletedPayment');
		$payment = new OMPCompletedPayment();
		$payment->setPressId($queuedPayment->getPressId());
		$payment->setType($queuedPayment->getType());
		$payment->setAmount($queuedPayment->getAmount());
		$payment->setCurrencyCode($queuedPayment->getCurrencyCode());
		$payment->setUserId($queuedPayment->getUserId());
		$payment->setAssocId($queuedPayment->getAssocId());
		$payment->setPayMethodPluginName($payMethod);

		return $payment;
	}

}

?>
