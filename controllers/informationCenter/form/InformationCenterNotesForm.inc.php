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
	/** @var int The file/submission this form is for */
	var $itemId;

	/** @var int The file this form is for */
	var $itemType;

	/**
	 * Constructor.
	 */
	function InformationCenterNotesForm($itemId, $itemType) {
		parent::Form('controllers/informationCenter/notes.tpl');

		$this->itemId = $itemId;
		$this->itemType = $itemType;

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Fetch the form.
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc($this->itemType, $this->itemId);

		$templateMgr->assign_by_ref('notes', $notes);
		$templateMgr->assign_by_ref('itemId', $this->itemId);
		$templateMgr->assign_by_ref('itemType', $this->itemType);
		if($this->itemType == ASSOC_TYPE_MONOGRAPH) {
			$monographId = $this->itemId;
		} else {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$monographFile =& $monographFileDao->getMonographFile($this->itemId);
			$monographId = $monographFile->getMonographId();
		}
		$templateMgr->assign_by_ref('monographId', $monographId);

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
		$note->setAssocType($this->itemType);
		$note->setAssocId($this->itemId);

		return $noteDao->insertObject($note);
	}
}

?>
