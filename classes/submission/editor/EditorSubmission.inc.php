<?php

/**
 * @file classes/submission/AcquisitionsEditor/AcquisitionsEditorSubmission.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcquisitionsEditorSubmission
 * @ingroup submission
 * @see AcquisitionsEditorSubmissionDAO
 *
 * @brief AcquisitionsEditorSubmission class.
 */

// $Id$


import('submission.acquisitionsEditor.AcquisitionsEditorSubmission');

class EditorSubmission extends AcquisitionsEditorSubmission {

	/**
	 * Constructor.
	 */
	function EditorSubmission() { 
		parent::AcquisitionsEditorSubmission();
	}
}
?>
