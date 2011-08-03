<?php

/**
 * @file classes/note/NoteDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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

	/**
	 * Determine whether or not unread notes exist for a given association.
	 */
	function unreadNotesExistByAssoc($assocType, $assocId, $userId) {
		$params = array((int) $assocId, (int) $assocType);
		if (isset($userId)) $params[] = (int) $userId;

		$result = $this->retrieve(
			'SELECT	COUNT(*)
			FROM	notes n, views v
			WHERE	n.assoc_type = ? AND
				n.assoc_id = ? AND
				v.assoc_type = ? AND
				v.assoc_id = n.note_id AND
				v.user_id = ?',
			array(
				(int) $assocType,
				(int) $assocId,
				(int) ASSOC_TYPE_NOTE,
				(int) $userId
			)
		);

		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? false : true;
		$result->Close();

		return $returner;
	}
}

?>
