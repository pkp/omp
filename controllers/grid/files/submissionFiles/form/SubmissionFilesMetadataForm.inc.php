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
	var $_signoffId;

	/**
	 * Constructor.
	 * @param $fileId int
	 * @param @monographId int
	 */
	function SubmissionFilesMetadataForm($fileId, $signoffId = null) {
		parent::Form('controllers/grid/files/submissionFiles/form/metadataForm.tpl');

		$this->_fileId = (int) $fileId;
		$this->_signoffId = (int) $signoffId;

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
		if (isset($this->_fileId)) {
			$this->_data['fileId'] = $this->_fileId;
		}

		if (isset($this->_signoffId)) {
			$this->_data['signoffId'] = $this->_signoffId;
		}
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($this->_fileId);
		$templateMgr->assign('monographFile', $monographFile);
		$templateMgr->assign('monographId', $monographFile->getMonographId());

		$templateMgr->assign('name', $monographFile->getLocalizedName());

		$noteDao =& DAORegistry::getDAO('NoteDAO');
		$notes =& $noteDao->getByAssoc(ASSOC_TYPE_MONOGRAPH_FILE, $this->_fileId);
		$templateMgr->assign('note', $notes->next());

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
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
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFile =& $submissionFileDao->getLatestRevision($this->_fileId);

		$monographFile->setName($this->getData('name'), Locale::getLocale());
		$submissionFileDao->updateObject($monographFile);

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
