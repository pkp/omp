<?php

/**
 * @file classes/publicationFormat/AssignedPublicationFormat.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AssignedPublicationFormat
 * @ingroup publicationFormat
 * @see AssignedPublicationFormatDAO
 *
 * @brief A publication format that has been assigned to a published monograph.
 */


import('classes.publicationFormat.PublicationFormat');


class AssignedPublicationFormat extends PublicationFormat {

	/**
	 * Constructor.
	 */
	function AssignedPublicationFormat() {
		parent::PublicationFormat();
	}

	/**
	 * Get ID of assigned format.
	 * @return int
	 */
	function getAssignedPublicationFormatId() {
		return $this->getData('assignedPublicationFormatId');
	}

	/**
	 * Set ID of assigned format
	 * @param $id int
	 */
	function setAssignedPublicationFormatId($assignedPublicationFormatId) {
		return $this->setData('assignedPublicationFormatId', $assignedPublicationFormatId);
	}

	/**
	 * Get sequence of format in format listings for the monograph.
	 * @return float
	 */
	function getSeq() {
		return $this->getData('seq');
	}

	/**
	 * Set sequence of format in format listings for the monograph.
	 * @param $sequence float
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

	/**
	 * Get "localized" format title (if applicable).
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * Get the format title (if applicable).
	 * @return string
	 */
	function getTitle() {
		return $this->getData('title');
	}
	/**
	 * Set title.
	 * @param $title string
	 * @param $locale
	 */
	function setTitle($title) {
		return $this->setData('title', $title);
	}

	/**
	 * set monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * get monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}
}
?>