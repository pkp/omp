<?php

/**
 * @file classes/publicationFormat/PublicationDate.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDate
 * @ingroup publicationFormat
 * @see PublicationDateDAO
 *
 * @brief Basic class describing a publication date for a format (used on the ONIX templates for publication formats)
 */

class PublicationDate extends DataObject {
	/**
	 * Constructor
	 */
	function PublicationDate() {
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
	 * Set the ONIX code for this publication date
	 * @param $role string
	 */
	function setRole($role) {
		$this->setData('role', $role);
	}

	/**
	 * Get the ONIX code for the publication date
	 * @return string
	 */
	function getRole() {
		return $this->getData('role');
	}

	/**
	 * Set the date format for this publication date (ONIX Codelist List55)
	 * @param $format string
	 */
	function setDateFormat($format) {
		$this->setData('dateFormat', $format);
	}

	/**
	 * Get the date format for the publication date
	 * @return string
	 */
	function getDateFormat() {
		return $this->getData('dateFormat');
	}

	/**
	 * Get the human readable name for this ONIX code
	 * @return string
	 */
	function getNameForONIXCode() {
		$onixCodelistItemDao =& DAORegistry::getDAO('ONIXCodelistItemDAO');
		$codes =& $onixCodelistItemDao->getCodes('List163'); // List163 is for Publication date, Embargo date, Announcement date, etc
		return $codes[$this->getRole()];
	}

	/**
	 * Set the date for this publication date
	 * @param $date string
	 */
	function setDate($date) {
		$this->setData('date', $date);
	}

	/**
	 * Get the date for the publication date
	 * @return string
	 */
	function getDate() {
		return $this->getData('date');
	}
}

?>
