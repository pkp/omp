<?php

/**
 * @file controllers/wizard/fileUpload/form/SubmissionFilesMetadataForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesMetadataForm
 * @ingroup controllers_wizard_fileUpload_form
 *
 * @brief Form for editing a submission file's metadata
 */

import('lib.pkp.classes.form.Form');

class SubmissionFilesMetadataForm extends Form {
	/** @var SubmissionFile */
	var $_submissionFile;

	/**
	 * Constructor.
	 * @param $submissionFile SubmissionFile
	 */
	function SubmissionFilesMetadataForm(&$submissionFile) {
		parent::Form('controllers/wizard/fileUpload/form/metadataForm.tpl');

		// Initialize the object.
		$this->_submissionFile =& $submissionFile;

		// Add validation checks.
		$this->addCheck(new FormValidator($this, 'name', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the submission file.
	 * @return SubmissionFile
	 */
	function &getSubmissionFile() {
		return $this->_submissionFile;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'note'));
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		// Submission file.
		$submissionFile =& $this->getSubmissionFile();
		$templateMgr->assign('submissionFile', $submissionFile);

		// Note attached to the file.
		$noteDao =& DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $submissionFile->getFileId());
		$templateMgr->assign('note', $notes->next());

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Update the submission file with data from the form.
		$submissionFile =& $this->getSubmissionFile();
		$submissionFile->setName($this->getData('name'), Locale::getLocale());
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->updateObject($submissionFile);

		// Save the note if it exists.
		if ($this->getData('note')) {
			$noteDao =& DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
			$note = $noteDao->newDataObject();

			$user =& $request->getUser();
			$note->setUserId($user->getId());

			$note->setContents($this->getData('note'));
			$note->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
			$note->setAssocId($submissionFile->getFileId());

		 	$noteDao->insertObject($note);
		}
	}
}

?>
