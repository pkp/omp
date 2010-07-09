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
	 * Fetch the form.
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc($this->assocType, $this->assocId); 

		$templateMgr->assign_by_ref('notes', $notes);
		$templateMgr->assign_by_ref('assocId', $this->assocId);
		$templateMgr->assign_by_ref('assocType', $this->assocType);
		
		return parent::fetch($request);
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
		$user =& Request::getUser();
		$context =& Request::getContext();
		$contextId = $context?$context->getId():0;

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$note = $noteDao->newDataObject();

		$note->setContextId($contextId);
		$note->setUserId($user->getId());
		$note->setContents($this->getData('newNote'));
		$note->setAssocType($this->assocType);
		$note->setAssocId($this->assocId);

		return $noteDao->insertNote($note);
	}
}

?>
