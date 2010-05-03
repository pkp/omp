<?php

/**
 * @file classes/submission/AcquisitionsEditor/AcquisitionsEditorSubmission.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsEditorSubmission
 * @ingroup submission
 * @see AcquisitionsEditorSubmissionDAO
 *
 * @brief AcquisitionsEditorSubmission class.
 */

// $Id$


import('classes.submission.seriesEditor.SeriesEditorSubmission');

class EditorSubmission extends SeriesEditorSubmission {

	/**
	 * Constructor.
	 */
	function EditorSubmission() {
		parent::SeriesEditorSubmission();
	}
}
?>
