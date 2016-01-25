<?php

/**
 * @file classes/monograph/MonographArtworkFile.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographArtworkFile
 * @ingroup monograph
 * @see SubmissionFileDAO
 *
 * @brief Artwork file class.
 */

import('lib.pkp.classes.submission.SubmissionArtworkFile');

class MonographArtworkFile extends SubmissionArtworkFile {
	/**
	 * Constructor
	 */
	function MonographArtworkFile() {
		parent::SubmissionArtworkFile();
	}

	/**
	 * @copydoc SubmissionFile::copyEditableMetadataForm
	 */
	function copyEditableMetadataFrom($submissionFile) {
		if (is_a($submissionFile, 'MonographArtworkFile') || is_a($submissionFile, 'MonographFile')) {
			$this->setChapterId($submissionFile->getChapterId());
		}

		parent::copyEditableMetadataFrom($submissionFile);
	}
}

?>
