<?php

/**
 * @file classes/publicationFormat/IdentificationCode.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class IdentificationCode
 * @ingroup publicationFormat
 * @see IdentificationCodeDAO
 *
 * @brief Basic class describing an identification code (used on the ONIX templates for publication formats)
 */

class IdentificationCode extends DataObject {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * get publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->getData('publicationFormatId');
	}

	/**
	 * set publication format id
	 * @param $pressId int
	 */
	function setPublicationFormatId($publicationFormatId) {
		return $this->setData('publicationFormatId', $publicationFormatId);
	}

	/**
	 * Set the ONIX code for this identification code
	 * @param $code string
	 */
	function setCode($code) {
		$this->setData('code', $code);
	}

	/**
	 * Get the ONIX code for the identification code
	 * @return string
	 */
	function getCode() {
		return $this->getData('code');
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List5'); // List5 is for ISBN, GTIN-13, etc.
		return $codes[$this->getCode()];
	}

	/**
	 * Set the value for this identification code
	 * @param $value string
	 */
	function setValue($value) {
		$this->setData('value', $value);
	}

	/**
	 * Get the value for the identification code
	 * @return string
	 */
	function getValue() {
		return $this->getData('value');
	}
}


