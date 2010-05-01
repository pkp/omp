<?php

/**
 * @file classes/informationCenter/form/InformationCenterNotesForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotesForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a file
 */


import('form.Form');

class InformationCenterNotesForm extends Form {
	/** @var int The file this form is for */
	var $fileId;

	/**
	 * Constructor.
	 */
	function InformationCenterNotesForm($fileId) {
		parent::Form('informationCenter/notes.tpl');
		
		$this->fileId = $fileId;
		
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$blah = ASSOC_TYPE_MONOGRAPH_FILE;
		$notes =& $noteDao->getNotesByAssoc($this->fileId, ASSOC_TYPE_MONOGRAPH_FILE); 
		$templateMgr->assign_by_ref('notes', $notes);
		$templateMgr->assign_by_ref('fileId', $this->fileId);
		
		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'newNote'
		));

	}

	/**
	 * Register a new user.
	 * @return userId int
	 */
	function execute() {
		import('note.NoteManager');
		$noteManager = new NoteManager();
		
		$user =& Request::getUser();
		
		return $noteManager->createNote($user->getId(), $this->getData('newNote'), ASSOC_TYPE_MONOGRAPH_FILE, $this->fileId);
	}
}

?>
