<?php

/**
 * @file classes/submission/copyeditor/CopyeditorSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditorSubmission
 * @ingroup submission
 * @see CopyeditorSubmissionDAO
 *
 * @brief CopyeditorSubmission class.
 */



import('classes.monograph.Monograph');

class CopyeditorSubmission extends Monograph {

	/**
	 * Get/Set Methods.
	 */

	//
	// Comments
	//

	/**
	 * Get most recent copyedit comment.
	 * @return MonographComment
	 */
	function getMostRecentCopyeditComment() {
		return $this->getData('mostRecentCopyeditComment');
	}

	/**
	 * Set most recent copyedit comment.
	 * @param $mostRecentCopyeditComment MonographComment
	 */
	function setMostRecentCopyeditComment($mostRecentCopyeditComment) {
		return $this->setData('mostRecentCopyeditComment', $mostRecentCopyeditComment);
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

	/**
	 * Get layout assignment.
	 * @return layoutAssignment object
	 */
	function &getLayoutAssignment() {
		$returner =& $this->getData('layoutAssignment');
		return $returner;
	}

	/**
	 * Set layout assignment.
	 * @param $layoutAssignment
	 */
	function setLayoutAssignment($layoutAssignment) {
		return $this->setData('layoutAssignment', $layoutAssignment);
	}

	/**
	 * Get proof assignment.
	 * @return proofAssignment object
	 */
	function &getProofAssignment() {
		$returner =& $this->getData('proofAssignment');
		return $returner;
	}

	/**
	 * Set proof assignment.
	 * @param $proofAssignment
	 */
	function setProofAssignment($proofAssignment) {
		return $this->setData('proofAssignment', $proofAssignment);
	}
}

?>
