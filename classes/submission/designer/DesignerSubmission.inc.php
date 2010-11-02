<?php

/**
 * @defgroup submission_designer
 */

/**
 * @file classes/submission/designer/DesignerSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerSubmission
 * @ingroup submission_designer
 * @see DesignerSubmissionDAO
 *
 * @brief Describes a designer's view of a submission
 */



import('classes.monograph.Monograph');

class DesignerSubmission extends Monograph {

	//
	// Get/set methods
	//

	/**
	 * Get design assignments for this monograph.
	 * @return array
	 */
	function &getProductionAssignments() {
		$designAssignments =& $this->getData('productionAssignments');
		return $designAssignments;
	}

	/**
	 * Set design assignments for this monograph.
	 * @param $productionAssignments array
	 */
	function setProductionAssignments(&$productionAssignments) {
		return $this->setData('productionAssignments', $productionAssignments);
	}

	/**
	 * Get the layout assignment for a monograph.
	 * @return LayoutAssignment
	 */
	function &getLayoutAssignment() {
		$layoutAssignment =& $this->getData('layoutAssignment');
		return $layoutAssignment;
	}

	/**
	 * Set the layout assignment for a monograph.
	 * @param $layoutAssignment Signoff
	 */
	function setLayoutAssignment(&$layoutAssignment) {
		return $this->setData('layoutAssignment', $layoutAssignment);
	}

	/**
	 * Get the layout assignment for a monograph.
	 * @return LayoutAssignment
	 */
	function &getProofAssignments() {
		$proofAssignment =& $this->getData('proofAssignment');
		return $proofAssignment;
	}

	/**
	 * Set the layout assignment for a monograph.
	 * @param $layoutAssignment LayoutAssignment
	 */
	function setProofAssignments(&$proofAssignment) {
		return $this->setData('proofAssignment', $proofAssignment);
	}

	/**
	 * Get the galleys for a monograph.
	 * @return array MonographGalley
	 */
	function &getGalleys() {
		$galleys =& $this->getData('galleys');
		return $galleys;
	}

	/**
	 * Set the galleys for a monograph.
	 * @param $galleys array MonographGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}

	//
	// Comments
	//

	/**
	 * Get most recent layout comment.
	 * @return MonographComment
	 */
	function getMostRecentLayoutComment() {
		return $this->getData('mostRecentLayoutComment');
	}

	/**
	 * Set most recent layout comment.
	 * @param $mostRecentLayoutComment MonographComment
	 */
	function setMostRecentLayoutComment($mostRecentLayoutComment) {
		return $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
	}

	/**
	 * Get the current layout file for a monograph.
	 * @return MonographFile
	 */
	function &getLayoutFile() {
		$layoutFile =& $this->getData('layoutFile');
		return $layoutFile;
	}

	/**
	 * Set the layout file.
	 * @param $layoutFile MonographFile
	 */
	function setLayoutFile(&$layoutFile) {
		return $this->setData('layoutFile', $layoutFile);
	}

}

?>
