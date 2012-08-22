<?php

/**
 * @file controllers/grid/files/fileSignoff/form/NewSignoffNoteForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewSignoffNoteForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a signoff.
 */


import('controllers.informationCenter.form.NewNoteForm');

class NewSignoffNoteForm extends NewNoteForm {
	/** @var $signoffId int The ID of the signoff to attach the note to */
	var $signoffId;

	/** @var $monographId int The ID of the signoff monograph */
	var $_monographId;

	/** @var $symbolic int The signoff symbolic. */
	var $_symbolic;

	/** @var $actionArgs array The fetch notes list action args. */
	var $_actionArgs;

	/**
	 * Constructor.
	 */
	function NewSignoffNoteForm($signoffId, $monographId, $signoffSymbolic, $actionArgs) {
		parent::NewNoteForm();

		$this->signoffId = $signoffId;
		$this->_monographId = $monographId;
		$this->_symbolic = $signoffSymbolic;
		$this->_actionArgs = $actionArgs;
	}

	/**
	 * Return the assoc type for this note.
	 * @return int
	 */
	function getAssocType() {
		return ASSOC_TYPE_SIGNOFF;
	}

	/**
	 * Return the assoc ID for this note.
	 * @return int
	 */
	function getAssocId() {
		return $this->signoffId;
	}

	/**
	 * @see NewNoteForm::getSubmitNoteLocaleKey()
	 */
	function getSubmitNoteLocaleKey() {
		return 'monograph.task.addNote';
	}

	/**
	 * @see NewNoteForm::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('linkParams', $this->_actionArgs);
		$templateMgr->assign('showEarlierEntries', false);

		return parent::fetch($request);
	}
}

?>
