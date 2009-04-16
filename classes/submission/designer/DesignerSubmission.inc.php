<?php

/**
 * @defgroup submission_designer
 */
 
/**
 * @file classes/submission/designer/DesignerSubmission.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DesignerSubmission
 * @ingroup submission_designer
 * @see DesignerSubmissionDAO
 *
 * @brief Describes a designer's view of a submission
 */

// $Id$


import('monograph.Monograph');

class DesignerSubmission extends Monograph {

	/**
	 * Constructor.
	 */
	function DesignerSubmission() {
		parent::Monograph();
	}

	//
	// Get/set methods
	//

	/**
	 * Get the layout assignment for a monograph.
	 * @return LayoutAssignment
	 */
	function &getLayoutAssignments() {
		$layoutAssignment = &$this->getData('layoutAssignment');
		return $layoutAssignment;
	}

	/**
	 * Set the layout assignment for a monograph.
	 * @param $layoutAssignment LayoutAssignment
	 */
	function setLayoutAssignments(&$layoutAssignment) {
		return $this->setData('layoutAssignment', $layoutAssignment);
	}

	/**
	 * Get the galleys for a monograph.
	 * @return array ArticleGalley
	 */
	function &getGalleys() {
		$galleys = &$this->getData('galleys');
		return $galleys;
	}

	/**
	 * Set the galleys for a monograph.
	 * @param $galleys array ArticleGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}

	/**
	 * Get supplementary files for this monograph.
	 * @return array SuppFiles
	 */
	function &getSuppFiles() {
		$returner =& $this->getData('suppFiles');
		return $returner;
	}

	/**
	 * Set supplementary file for this monograph.
	 * @param $suppFiles array SuppFiles
	 */
	function setSuppFiles($suppFiles) {
		return $this->setData('suppFiles', $suppFiles);
	}


	// FIXME These should probably be in an abstract "Submission" base class

	/**
	 * Get edit assignments for this monograph.
	 * @return array
	 */
	function &getByIds() {
		$editAssignments = &$this->getData('editAssignments');
		return $editAssignments;
	}

	/**
	 * Set edit assignments for this monograph.
	 * @param $editAssignments array
	 */
	function setEditAssignments($editAssignments) {
		return $this->setData('editAssignments', $editAssignments);
	}

	/**
	 * Get proof assignment.
	 * @return proofAssignment object
	 */
	function &getProofAssignment() {
		$proofAssignment = &$this->getData('proofAssignment');
		return $proofAssignment;
	}

	/**
	 * Set proof assignment.
	 * @param $proofAssignment
	 */
	function setProofAssignment($proofAssignment) {
		return $this->setData('proofAssignment', $proofAssignment);
	}

	//
	// Comments
	//

	/**
	 * Get most recent layout comment.
	 * @return ArticleComment
	 */
	function getMostRecentLayoutComment() {
		return $this->getData('mostRecentLayoutComment');
	}

	/**
	 * Set most recent layout comment.
	 * @param $mostRecentLayoutComment ArticleComment
	 */
	function setMostRecentLayoutComment($mostRecentLayoutComment) {
		return $this->setData('mostRecentLayoutComment', $mostRecentLayoutComment);
	}

	/**
	 * Get most recent proofread comment.
	 * @return ArticleComment
	 */
	function getMostRecentProofreadComment() {
		return $this->getData('mostRecentProofreadComment');
	}

	/**
	 * Set most recent proofread comment.
	 * @param $mostRecentProofreadComment ArticleComment
	 */
	function setMostRecentProofreadComment($mostRecentProofreadComment) {
		return $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
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
