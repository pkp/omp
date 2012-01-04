<?php

/**
 * @file controllers/informationCenter/form/NewMonographNoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewMonographNoteForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a file
 */


import('controllers.informationCenter.form.NewNoteForm');

class NewMonographNoteForm extends NewNoteForm {
	/** @var $monographId int The ID of the monograph to attach the note to */
	var $monographId;

	/**
	 * Constructor.
	 */
	function NewMonographNoteForm($monographId) {
		parent::NewNoteForm();

		$this->monographId = $monographId;
	}

	/**
	 * Return the assoc type for this note.
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_MONOGRAPH;
	}

	/**
	 * Return the assoc ID for this note.
	 * @return int
	 */
	function getAssocId() {
		return $this->monographId;
	}
}

?>
