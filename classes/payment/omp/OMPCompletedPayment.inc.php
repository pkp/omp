<?php

/**
 * @defgroup payment_omp OMP payment concerns
 */

/**
 * @file classes/payment/omp/OMPCompletedPayment.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPCompletedPayment
 * @ingroup payment_omp
 * @see OMPCompletedPaymentDAO
 *
 * @brief Class describing a payment ready to be in the database.
 *
 */
import('lib.pkp.classes.payment.Payment');

class OMPCompletedPayment extends Payment {
	var $pressId;
	var $type;
	var $timestamp;
	var $payMethod;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Get/set methods
	 */

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
	 */
	function setPressId($pressId) {
		$this->pressId = $pressId;
	}

	/**
	 * Set the Payment Type
	 * @param $type int
	 */
	function setType($type) {
		$this->type = $type;
	}

	/**
	 * Set the Payment Type
	 * @return $type int
	 */
	function getType() {
		return $this->type;
	}

	/**
	 * Returns the description of the CompletedPayment.
	 * Pulled from Press Settings if present, or from locale file otherwise.
	 * For subscriptions, pulls subscription type name.
	 * @return string
	 */
	function getName() {
		switch ($this->type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				fatalError('unimplemented');
			default:
				assert(false);
		}
	}

	/**
	 * Returns the description of the CompletedPayment.
	 * Pulled from Press Settings if present, or from locale file otherwise.
	 * For subscriptions, pulls subscription type name.
	 * @return string
	 */
	function getDescription() {
		switch ($this->type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				fatalError('unimplemented');
			default:
				assert(false);
		}
	}

	/**
	 * Get the row id of the payment.
	 * @return int
	 */
	function getTimestamp() {
		return $this->timestamp;
	}

	/**
	 * Set the id of payment
	 * @param $dt int/string *nix timestamp or ISO datetime string
	 */
	function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}

	/**
	 * Get the  method of payment.
	 * @return String
	 */
	function getPayMethodPluginName() {
		return $this->payMethod;
	}

	/**
	 * Set the method of payment.
	 * @param $pressId String
	 */
	function setPayMethodPluginName($payMethod){
		$this->payMethod = $payMethod;
	}

	/**
	 * Display-related get Methods
	 */

	/**
	 * Get some information about the assocId for display.
	 * @return String
	 */
	function getAssocDescription() {
		if (!$this->assocId) return false;
		switch ($this->type) {
			case PAYMENT_TYPE_PURCHASE_FILE:
				fatalError('unimplemented');
			default:
				assert(false);
		}

		return false;
	}
}

?>
