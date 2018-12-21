<?php

/**
 * @file classes/payment/omp/OMPPaymentManager.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPPaymentManager
 * @ingroup payment
 * @see QueuedPayment
 *
 * @brief Provides payment management functions.
 *
 */

import('lib.pkp.classes.payment.QueuedPayment');
import('lib.pkp.classes.payment.PaymentManager');
import('lib.pkp.classes.submission.SubmissionFile');

define('PAYMENT_TYPE_PURCHASE_FILE',	0x000000001);

class OMPPaymentManager extends PaymentManager {
	/**
	 * Determine whether the payment system is configured.
	 * @return boolean true iff configured
	 */
	function isConfigured() {
		return parent::isConfigured() && $this->_context && $this->_context->getData('currency');
	}

	/**
	 * Create a queued payment.
	 * @param $request PKPRequest
	 * @param $type int PAYMENT_TYPE_...
	 * @param $userId int ID of user responsible for payment
	 * @param $assocId int ID of associated entity
	 * @param $amount numeric Amount of currency $currencyCode
	 * @param $currencyCode string optional ISO 4217 currency code
	 * @return QueuedPayment
	 */
	function createQueuedPayment($request, $type, $userId, $assocId, $amount, $currencyCode = null) {
		$payment = new QueuedPayment($amount, $this->_context->getData('currency'), $userId, $assocId);
		$payment->setContextId($this->_context->getId());
		$payment->setType($type);

	 	switch ($type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				list($fileId, $revision) = array_map(function($a) {
					return (int) $a;
				}, explode('-', $assocId));
				import('lib.pkp.classes.submission.SubmissionFile'); // const
				$submissionFile = $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF);
				assert($submissionFile);
				$payment->setRequestUrl($request->url(null, 'catalog', 'view', array(
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
	 * @return PaymentPlugin
	 */
	function getPaymentPlugin() {
		$paymentMethodPluginName = $this->_context->getData('paymentPluginName');
		$paymentMethodPlugin = null;
		if (!empty($paymentMethodPluginName)) {
			$plugins = PluginRegistry::loadCategory('paymethod');
			if (isset($plugins[$paymentMethodPluginName])) $paymentMethodPlugin = $plugins[$paymentMethodPluginName];
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
	function fulfillQueuedPayment($request, $queuedPayment, $payMethodPluginName = null) {
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
		$completedPayment = $this->createCompletedPayment($queuedPayment, $payMethodPluginName);
		$ompCompletedPaymentDao->insertCompletedPayment($completedPayment);

		$queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO');
		$queuedPaymentDao->deleteById($queuedPayment->getId());

		return $returner;
	}

	/**
	 * Create a completed payment from a queued payment.
	 * @param $queuedPayment QueuedPayment Payment to complete.
	 * @param $payMethod string Name of payment plugin used.
	 * @return CompletedPayment
	 */
	function createCompletedPayment($queuedPayment, $payMethod) {
		import('lib.pkp.classes.payment.CompletedPayment');
		$payment = new CompletedPayment();
		$payment->setContextId($queuedPayment->getContextId());
		$payment->setType($queuedPayment->getType());
		$payment->setAmount($queuedPayment->getAmount());
		$payment->setCurrencyCode($queuedPayment->getCurrencyCode());
		$payment->setUserId($queuedPayment->getUserId());
		$payment->setAssocId($queuedPayment->getAssocId());
		$payment->setPayMethodPluginName($payMethod);

		return $payment;
	}

	/**
	 * Returns the name of a payment.
	 * @return string
	 */
	function getPaymentName($payment) {
		switch ($payment->getType()) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				list($fileId, $revision) = explode('-', $payment->getAssocId());
				assert($fileId && $revision);
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				$submissionFile = $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF);
				if (!$submissionFile || $submissionFile->getAssocType() !== ASSOC_TYPE_PUBLICATION_FORMAT) return false;

				return $submissionFile->getLocalizedName();
			default:
				// Invalid payment type
				assert(false);
		}
	}
}


