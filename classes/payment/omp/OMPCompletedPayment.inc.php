<?php

/**
 * @file classes/payment/omp/OMPCompletedPayment.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPCompletedPayment
 * @ingroup payment
 * @see OMPCompletedPaymentDAO
 *
 * @brief Class describing a completed payment.
 */

import('lib.pkp.classes.payment.Payment');

class OMPCompletedPayment extends Payment {
	/** @var int Press ID */
	var $_pressId;

	/** @var string Payment completion timestamp */
	var $_timestamp;

	/** @var int PAYMENT_TYPE_... */
	var $_type;

	/** @var string Payment plugin name */
	var $_paymentPluginName;

	/**
	 * Get the press ID for the payment.
	 * @return int
	 */
	function getPressId() {
		return $this->_pressId;
	}

	/**
	 * Set the press ID for the payment.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		$this->_pressId = $pressId;
	}

	/**
	 * Get the payment completion timestamp.
	 * @return string
	 */
	function getTimestamp() {
		return $this->_timestamp;
	}

	/**
	 * Set the payment completion timestamp.
	 * @param $timestamp string Timestamp
	 */
	function setTimestamp($timestamp) {
		$this->_timestamp = $timestamp;
	}

	/**
	 * Set the payment type.
	 * @param $type int PAYMENT_TYPE_...
	 */
	function setType($type) {
		$this->_type = $type;
	}

	/**
	 * Set the payment type.
	 * @return $type int PAYMENT_TYPE_...
	 */
	function getType() {
		return $this->_type;
	}

	/**
	 * Get the payment plugin name.
	 * @return string
	 */
	function getPayMethodPluginName() {
		return $this->_paymentPluginName;
	}

	/**
	 * Set the payment plugin name.
	 * @param $paymentPluginName string
	 */
	function setPayMethodPluginName($paymentPluginName) {
		$this->_paymentPluginName = $paymentPluginName;
	}
}

?>
