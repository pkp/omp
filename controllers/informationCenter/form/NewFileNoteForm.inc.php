<?php

/**
 * @file controllers/informationCenter/form/NewFileNoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewFileNoteForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a file
 */


import('controllers.informationCenter.form.NewNoteForm');

class NewFileNoteForm extends NewNoteForm {
	/** @var $fileId int The ID of the monograph file to attach the note to */
	var $fileId;

	/**
	 * Constructor.
	 */
	function NewFileNoteForm($fileId) {
		parent::NewNoteForm();

		$this->fileId = $fileId;
	}

	/**
	 * Return the assoc type for this note.
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_MONOGRAPH_FILE;
	}

	/**
	 * Return the assoc ID for this note.
	 * @return int
	 */
	function getAssocId() {
		return $this->fileId;
	}
}

?>
