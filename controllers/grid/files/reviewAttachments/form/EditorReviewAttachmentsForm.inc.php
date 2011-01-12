<?php

/**
 * @file controllers/grid/files/reviewAttachments/form/EditorReviewAttachmentsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditorReviewAttachmentsForm
 * @ingroup controllers_grid_files_reviewAttachments_form
 *
 * @brief Form for adding/editing a review attachment file
 */

import('lib.pkp.classes.form.Form');

class EditorReviewAttachmentsForm extends Form {
	/** the id of the monograph */
	var $monographId;

	/** the id of the file being edited */
	var $fileId;

	/** the id of the parent grid */
	var $gridId;

	/**
	 * Constructor.
	 */
	function EditorReviewAttachmentsForm($monographId, $fileId = null, $gridId = null) {
		$this->monographId = $monographId;
		$this->fileId = $fileId;
		$this->gridId = $gridId;
		parent::Form('controllers/grid/files/reviewAttachments/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->_data['monographId'] = $this->monographId;
		if ( isset($this->fileId) ) {
			$this->_data['fileId'] = $this->fileId;
		}

		// grid related data
		$this->_data['gridId'] = $this->gridId;
		if ( isset($this->fileId) ) {
			$this->_data['rowId'] = $this->fileId;
		}
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewId', $this->reviewId);

		if ($this->fileId) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$reviewAttachment =& $submissionFileDao->getLatestRevision($this->fileId);

			assert(!is_null($reviewAttachment));
			$templateMgr->assign_by_ref('attachmentFile', $reviewAttachment);
		}
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save name for library file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		import('classes.file.MonographFileManager');
		if (MonographFileManager::uploadedFileExists('attachment')) {
			$fileId = MonographFileManager::uploadReviewFile($this->monographId, 'attachment');
		}

		return $fileId;
	}
}

?>
