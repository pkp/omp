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
	/** @var int */
	var $_fileId;

	/** @var int */
	var $_monographId;

	/**
	 * Constructor.
	 * @param $fileId int
	 * @param @monographId int
	 */
	function SubmissionFilesMetadataForm($fileId = null, $monographId = null) {
		parent::Form('controllers/grid/files/submissionFiles/form/metadataForm.tpl');

		$this->_fileId = (int) $fileId;
		$this->_monographId = (int) $monographId;

		$this->addCheck(new FormValidator($this, 'name', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name');
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		if ( isset($this->fileId) ) {
			$this->_data['fileId'] = $this->fileId;
		}
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fileId', $this->_fileId);

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->_fileId);
		$templateMgr->assign('monographFile', $monographFile);

		$templateMgr->assign('name', $monographFile->getLocalizedName());

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $this->_fileId);
		$templateMgr->assign('note', $notes->next());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'note'));
	}

	/**
	 * Save submission file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->_fileId);

		$monographFile->setName($this->getData('name'), Locale::getLocale());
		$monographFileDao->updateMonographFile($monographFile);

		// Save the note if it exists
		if ($this->getData('note')) {
			$noteDao =& DAORegistry::getDAO('NoteDAO');
			$note = $noteDao->newDataObject();
			$press =& Request::getPress();
			$user =& Request::getUser();

			$note->setContextId($press->getId());
			$note->setUserId($user->getId());
			$note->setContents($this->getData('note'));
			$note->setAssocType(ASSOC_TYPE_MONOGRAPH_FILE);
			$note->setAssocId($this->_fileId);

		 	$noteDao->insertObject($note);
		}
	}
}

?>
