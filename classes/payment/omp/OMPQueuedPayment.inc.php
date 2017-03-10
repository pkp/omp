<?php

/**
 * @file classes/payment/omp/OMPQueuedPayment.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPQueuedPayment
 * @ingroup payment
 *
 * @brief Queued payment data structure for OMP
 *
 */

import('lib.pkp.classes.payment.QueuedPayment');

class OMPQueuedPayment extends QueuedPayment {
	/** @var $pressId int press ID this payment applies to */
	var $pressId;

	/** @var $type int PAYMENT_TYPE_... */
	var $type;

	/** @var $requestUrl string URL associated with this payment */
	var $requestUrl;

	/**
	 * Get the press ID of the payment.
	 * @return int
	 */
	function getPressId() {
		return $this->pressId;
	}

	/**
	 * Set the press ID of the payment.
	 * @param $pressId int
	 * @return $pressId int New press ID
	 */
	function setPressId($pressId) {
		return $this->pressId = $pressId;
	}

	/**
	 * Set the type for this payment (PAYMENT_TYPE_...)
	 * @param $type int PAYMENT_TYPE_...
	 * @return int New payment type
	 */
	function setType($type) {
		return $this->type = $type;
	}

	/**
	 * Get the type of this payment (PAYMENT_TYPE_...)
	 * @return int PAYMENT_TYPE_...
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * Returns the name of the QueuedPayment.
	 * @return string|boolean
	 */
	function getName() {
		switch ($this->type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				list($fileId, $revision) = explode('-', $this->getAssocId());
				assert($fileId && $revision);
				$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
				$submissionFile =& $submissionFileDao->getRevision($fileId, $revision, SUBMISSION_FILE_PROOF);
				if (!$submissionFile || $submissionFile->getAssocType() !== ASSOC_TYPE_PUBLICATION_FORMAT) return false;

				return $submissionFile->getLocalizedName();
			default:
				// Invalid payment type
				assert(false);
		}
	}

	/**
	 * Returns the description of the QueuedPayment.
	 * @return string
	 */
	function getDescription() {
		switch ($this->type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				return __('payment.directSales.monograph.description');
			default:
				// Invalid payment ID
				assert(false);
		}
	}

	/**
	 * Set the request URL.
	 * @param $url string
	 * @return string New URL
	 */
	function setRequestUrl($url) {
		return $this->requestUrl = $url;
	}

	/**
	 * Get the request URL.
	 * @return string
	 */
	function getRequestUrl() {
		return $this->requestUrl;
	}
}

?>
