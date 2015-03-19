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


	/**
	 * Copy the user-facing (editable) metadata from another monograph
	 * file.
	 * @param $monographFile MonographFile
	 */
	function copyEditableMetadataFrom($monographFile) {
		if (is_a($monographFile, 'MonographFile')) {
			$this->setData('chapterId', $monographFile->getData('chapterId'));
		}

		parent::copyEditableMetadataFrom($monographFile);
	}
}

?>
