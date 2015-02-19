<?php

/**
 * @file classes/note/NoteDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NoteDAO
 * @ingroup note
 * @see PKPNoteDAO
 *
 * @brief OMP extension of PKPNoteDAO
 */

import('lib.pkp.classes.note.PKPNoteDAO');
import('classes.note.Note');

class NoteDAO extends PKPNoteDAO {
	/**
	 * Constructor
	 */
	function NoteDAO() {
		parent::PKPNoteDAO();
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Note
	 */
	function newDataObject() {
		return new Note();
	}
}

?>
