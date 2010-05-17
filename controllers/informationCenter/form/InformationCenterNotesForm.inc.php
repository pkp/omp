<?php

/**
 * @file controllers/informationCenter/form/InformationCenterNotesForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationCenterNotesForm
 * @ingroup informationCenter_form
 *
 * @brief Form to display and post notes on a file
 */


import('lib.pkp.classes.form.Form');

class InformationCenterNotesForm extends Form {
	/** @var int The file this form is for */
	var $assocId;
	
	/** @var int The file this form is for */
	var $assocType;

	/**
	 * Constructor.
	 */
	function InformationCenterNotesForm($assocId, $assocType) {
		parent::Form('controllers/informationCenter/notes.tpl');
		
		$this->assocId = $assocId;
		$this->assocType = $assocType;
		
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display(&$request, $fetch = true) {
		$templateMgr =& TemplateManager::getManager();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getNotesByAssoc($this->assocId, $this->assocType); 

		$templateMgr->assign_by_ref('notes', $notes);
		$templateMgr->assign_by_ref('assocId', $this->assocId);
		$templateMgr->assign_by_ref('assocType', $this->assocType);
		
		return parent::display($request, $fetch);
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
		import('lib.pkp.classes.note.NoteManager');
		$noteManager = new NoteManager();
		
		$user =& Request::getUser();
		
		return $noteManager->createNote($user->getId(), $this->getData('newNote'), $this->assocType, $this->assocId);
	}
}

?>
