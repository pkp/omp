<?php

/**
 * @defgroup submission_layoutAssignment
 */
 
/**
 * @file classes/submission/layoutAssignment/LayoutAssignment.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LayoutAssignment
 * @ingroup submission_layoutAssignment
 * @see LayoutAssignmentDAO
 *
 * @brief Describes layout editing assignments.
 */

// $Id$


class LayoutAssignment extends DataObject {

	/**
	 * Get ID of monograph.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set ID of monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get user ID of the layout designer.
	 * @return int
	 */
	function getDesignerId() {
		return $this->getData('designerId');
	}

	/**
	 * Set user ID of layout designer.
	 * @param $designerId int
	 */
	function setDesignerId($designerId) {
		return $this->setData('designerId', $designerId);
	}

	/**
	 * Get full name of the designer.
	 * @return string
	 */
	function getDesignerFullName() {
		return $this->getData('designerFullName');
	}

	/**
	 * Set full name of the designer.
	 * @param $designerFullName string
	 */
	function setDesignerFullName($designerFullName) {
		return $this->setData('designerFullName', $designerFullName);
	}

	/**
	 * Get the designer's email address.
	 * @return string
	 */
	function getDesignerEmail() {
		return $this->getData('editorEmail');
	}

	/**
	 * Set the designer's email address.
	 * @param $designerEmail string
	 */
	function setDesignerEmail($designerEmail) {
		return $this->setData('designerEmail', $designerEmail);
	}

	/**
	 * Get the assignment requested by date.
	 * @return string
	 */
	function getDateNotified() {
		return $this->getData('dateNotified');
	}

	/**
	 * Set the assignment requested by date.
	 * @param $dateNotified string
	 */
	function setDateNotified($dateNotified) {
		return $this->setData('dateNotified', $dateNotified);
	}

	/**
	 * Get the assignment underway date.
	 * @return string
	 */
	function getDateUnderway() {
		return $this->getData('dateUnderway');
	}

	/**
	 * Set the assignment underway date.
	 * @param $dateUnderway string
	 */
	function setDateUnderway($dateUnderway) {
		return $this->setData('dateUnderway', $dateUnderway);
	}

	/**
	 * Get the assignment completion date.
	 * @return string
	 */
	function getDateCompleted() {
		return $this->getData('dateCompleted');
	}

	/**
	 * Set the assignment completion date.
	 * @param $dateCompleted string
	 */
	function setDateCompleted($dateCompleted) {
		return $this->setData('dateCompleted', $dateCompleted);
	}

	/**
	 * Get the assignment acknowledgement date.
	 * @return string
	 */
	function getDateAcknowledged() {
		return $this->getData('dateAcknowledged');
	}

	/**
	 * Set the assignment acknowledgement date.
	 * @param $dateAcknowledged string
	 */
	function setDateAcknowledged($dateAcknowledged) {
		return $this->setData('dateAcknowledged', $dateAcknowledged);
	}

	/**
	 * Get ID of the layout file.
	 * @return int
	 */
	function getLayoutFileId() {
		return $this->getData('layoutFileId');
	}

	/**
	 * Set ID of the layout file.
	 * @param $layoutFileId int
	 */
	function setLayoutFileId($layoutFileId) {
		return $this->setData('layoutFileId', $layoutFileId);
	}

	/**
	 * Get layout file.
	 * @return MonographFile
	 */
	function getLayoutFile() {
		return $this->getData('layoutFile');
	}

	/**
	 * Set layout file.
	 * @param $layoutFile MonographFile
	 */
	function setLayoutFile($layoutFile) {
		return $this->setData('layoutFile', $layoutFile);
	}

}

?>
