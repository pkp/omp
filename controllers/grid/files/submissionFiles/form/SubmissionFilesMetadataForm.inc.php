<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesMetadataForm
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_grid_file_form
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
	 */
	function SubmissionFilesMetadataForm($fileId = null, $monographId = null) {	
		parent::Form('controllers/grid/files/submissionFiles/form/metadataForm.tpl');

		$this->_fileId = $fileId;	
		$this->_monographId = $monographId;
		
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
	 */
	function initData(&$args, &$request) {
		if ( isset($this->fileId) ) {
			$this->_data['fileId'] = $this->fileId;
		}
	}

	/**
	 * Display
	 */
	function display(&$request, $fetch = false) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fileId', $this->_fileId);
		
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->_fileId);
		$templateMgr->assign('monographFile', $monographFile);
		
		$templateMgr->assign('name', $monographFile->getLocalizedName());
		
		return parent::display($request, $fetch);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('name'));
	}

	/**
	 * Save submission file
	 */
	function execute(&$args, &$request) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFile =& $monographFileDao->getMonographFile($this->_fileId);

		$monographFile->setName($this->getData('name'), Locale::getLocale());		
		$monographFileDao->updateMonographFile($monographFile);
	}
}

?>
