<?php

/**
 * @file classes/monograph/MonographFile.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographFile
 * @ingroup monograph
 * @see SubmissionFileDAO
 *
 * @brief Monograph file class.
 */

import('lib.pkp.classes.submission.SubmissionFile');

class MonographFile extends SubmissionFile {
	/**
	 * Constructor.
	 */
	function MonographFile() {
		parent::SubmissionFile();
	}

	//
	// Getters/setters
	//
	/**
	 * Get the monograph chapter id.
	 * @return int
	 */
	function getChapterId() {
		return $this->getData('chapterId');
	}

	/**
	 * Set the monograph chapter id.
	 * @param $chapterId int
	 */
	function setChapterId($chapterId) {
		return $this->setData('chapterId', $chapterId);
	}


	/**
	 * @copydoc SubmissionFile::copyEditableMetadataForm
	 */
	function copyEditableMetadataFrom($submissionFile) {
		if (is_a($submissionFile, 'MonographFile') || is_a($submissionFile, 'MonographArtworkFile')) {
			$this->setChapterId($submissionFile->getChapterId());
		}

		parent::copyEditableMetadataFrom($submissionFile);
	}
}

?>
