<?php

/**
 * @defgroup publicationFormat
 */

/**
 * @file classes/publicationFormat/IdentificationCode.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	function IdentificationCode() {
		parent::DataObject();
	}

	/**
	 * get assigned publication format id
	 * @return int
	 */
	function getAssignedPublicationFormatId() {
		return $this->getData('assignedPublicationformatId');
	}

	/**
	 * set assigned publication format id
	 * @param $pressId int
	 */
	function setAssignedPublicationformatId($assignedPublicationformatId) {
		return $this->setData('assignedPublicationformatId', $assignedPublicationformatId);
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
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
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

	/**
	 * Sets the id for this identification code
	 * @param $id int
	 */
	function setId($id) {
		$this->setData('identificationCodeId', $id);
	}

	/**
	 * Get the id for this identification code
	 * @return int
	 */
	function getId() {
		return $this->getData('identificationCodeId');
	}
}

?>
