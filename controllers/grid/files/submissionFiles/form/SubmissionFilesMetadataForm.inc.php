<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesMetadataForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesMetadataForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for editing a submission file's metadata
 */

import('lib.pkp.classes.form.Form');

class SubmissionFilesMetadataForm extends Form {
	/** @var MonographFile */
	var $_monographFile;

	/**
	 * Constructor.
	 * @param $monographFile MonographFile
	 * @param $template String
	 */
	function SubmissionFilesMetadataForm(&$monographFile, $template = 'controllers/grid/files/submissionFiles/form/metadataForm.tpl') {
		parent::Form($template);

		$this->_monographFile =& $monographFile;

		$this->addCheck(new FormValidator($this, 'name', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the monograph file.
	 * @return MonographFile
	 */
	function &getMonographFile() {
		return $this->_monographFile;
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

		$monographFile =& $this->getMonographFile();
		$templateMgr->assign('monographFile', $monographFile);

		$noteDao =& DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $monographFile->getFileId());
		$templateMgr->assign('note', $notes->next());

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, &$request) {
		// Update the monograph file with data from the form.
		$monographFile =& $this->getMonographFile();
		$monographFile->setName($this->getData('name'), Locale::getLocale());
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFileDao->updateObject($monographFile);

		// Save the note if it exists.
		if ($this->getData('note')) {
			$noteDao =& DAORegistry::getDAO('NoteDAO'); /* @var $noteDao NoteDAO */
			$note = $noteDao->newDataObject();

			$router =& $request->getRouter();
			$press =& $router->getContext($request);
			$note->setContextId($press->getId());

			$user =& $request->getUser();
			$note->setUserId($user->getId());

			$note->setContents($this->getData('note'));
			$note->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
			$note->setAssocId($monographFile->getFileId());

		 	$noteDao->insertObject($note);
		}
	}
}

?>
