<?php

/**
 * @file classes/submission/productionEditor/ProductionEditorSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProductionEditorSubmission
 * @ingroup submission
 * @see ProductionEditorSubmissionDAO
 *
 * @brief ProductionEditorSubmission class.
 */



import('classes.monograph.Monograph');

class ProductionEditorSubmission extends Monograph {

	/**
	 * Get design assignments for this monograph.
	 * @return array
	 */
	function &getProductionAssignments() {
		$designAssignments =& $this->getData('designAssignments');
		return $designAssignments;
	}

	/**
	 * Set design assignments for this monograph.
	 * @param $designAssignments array
	 */
	function setProductionAssignments(&$designAssignments) {
		return $this->setData('designAssignments', $designAssignments);
	}

	/**
	 * Get post-review file.
	 * @return MonographFile
	 */
	function &getEditorFile() {
		$returner =& $this->getData('editorFile');
		return $returner;
	}

	/**
	 * Set post-review file.
	 * @param $editorFile MonographFile
	 */
	function setEditorFile($editorFile) {
		return $this->setData('editorFile', $editorFile);
	}

	/**
	 * Get submission file for this monograph.
	 * @return MonographFile
	 */
	function &getSubmissionFile() {
		$returner =& $this->getData('submissionFile');
		return $returner;
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
	/**
	 * Set submission file for this monograph.
	 * @param $submissionFile MonographFile
	 */
	function setSubmissionFile($submissionFile) {
		return $this->setData('submissionFile', $submissionFile);
	}
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
	 * Get most recent proofread comment.
	 * @return MonographComment
	 */
	function getMostRecentProofreadComment() {
		return $this->getData('mostRecentProofreadComment');
	}

	/**
	 * Set most recent proofread comment.
	 * @param $mostRecentProofreadComment MonographComment
	 */
	function setMostRecentProofreadComment($mostRecentProofreadComment) {
		return $this->setData('mostRecentProofreadComment', $mostRecentProofreadComment);
	}

	/**
	 * Get the galleys for a monograph.
	 * @return array MonographGalley
	 */
	function &getGalleys() {
		$galleys = &$this->getData('galleys');
		return $galleys;
	}

	/**
	 * Set the galleys for a monograph.
	 * @param $galleys array MonographGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}
}

?>