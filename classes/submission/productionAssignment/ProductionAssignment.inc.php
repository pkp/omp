<?php

/**
 * @defgroup submission_productionAssignment
 */
 
/**
 * @file classes/submission/productionAssignment/ProductionAssignment.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionAssignment
 * @ingroup submission_productionAssignment
 * @see ProductionAssignmentDAO
 *
 * @brief Describes production assignments.
 */



define('DESIGN_ASSIGNMENT_TYPE_LAYOUT',		1);
define('DESIGN_ASSIGNMENT_TYPE_COVER',		2);
define('DESIGN_ASSIGNMENT_TYPE_VISUALS',	3);
define('DESIGN_ASSIGNMENT_TYPE_MEDIA',		4);

class ProductionAssignment extends DataObject {

	/**
	 * Get the monograph's id.
	 * @return string
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set the monograph's id.
	 * @param $monographId string
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get the signoffs associated with this assignment.
	 * @return int
	 */
	function &getSignoffs() {
		return $this->getData('signoffs');
	}

	/**
	 * Set the signoffs associated with this assignment.
	 * @param $signoffs array
	 */
	function setSignoffs(&$signoffs) {
		return $this->setData('signoffs', $signoffs);
	}

	/**
	 * Get the files associated with this assignment.
	 * @return int
	 */
	function &getFiles() {
		return $this->getData('files');
	}

	/**
	 * Set the files associated with this assignment.
	 * @param $signoffs array
	 */
	function setFiles(&$files) {
		return $this->setData('files', $files);
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
		return $this->getData('designerEmail');
	}

	/**
	 * Set the designer's email address.
	 * @param $designerEmail string
	 */
	function setDesignerEmail($designerEmail) {
		return $this->setData('designerEmail', $designerEmail);
	}

	/**
	 * Get the assignment type.
	 * @return string
	 */
	function getType() {
		return $this->getData('type');
	}

	/**
	 * Set the assignment type.
	 * @param $designerEmail string
	 */
	function setType($type) {
		return $this->setData('type', $type);
	}

	/**
	 * Get the assignment label.
	 * @return string
	 */
	function getLabel() {
		return $this->getData('label');
	}

	/**
	 * Set the assignment type.
	 * @param $designerEmail string
	 */
	function setLabel($label) {
		return $this->setData('label', $label);
	}
}

?>