<?php

/**
 * @file classes/note/Note.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Note
 * @ingroup note
 * @see NoteDAO
 * @brief OMP subclass for Notes (defines OMP-specific types)
 */

/* Notification associative types. */
define('NOTE_TYPE_EDITOR_TO_REVIEWER',	0x1000001);
define('NOTE_TYPE_SUBMISSION_ACCEPTED', 0x1000002);
define('NOTE_TYPE_SUBMISSION_DECLINED', 0x1000003);
define('NOTE_TYPE_COPYEDITING_FILE',	0x1000004);

import('lib.pkp.classes.note.PKPNote');

class Note extends PKPNote {
	/**
	 * Constructor.
	 */
	function Note() {
		parent::PKPNote();
	}
}

?>
