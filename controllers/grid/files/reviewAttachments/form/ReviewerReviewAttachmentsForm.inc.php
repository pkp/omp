<?php

/**
 * @file controllers/grid/files/reviewAttachments/form/ReviewAttachmentsForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/editing a review attachment file
 */

import('lib.pkp.classes.form.Form');

class ReviewAttachmentsForm extends Form {
	/** the id of the review */
	var $reviewId;

	/** the id of the file being edited */
	var $fileId;

	/** the id of the parent grid */
	var $gridId;

	/**
	 * Constructor.
	 */
	function ReviewAttachmentsForm($reviewId = null, $fileId = null, $gridId = null) {
		$this->reviewId = $reviewId;
		$this->fileId = $fileId;
		$this->gridId = $gridId;
		parent::Form('controllers/grid/files/reviewAttachments/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
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
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('reviewId', $this->reviewId);

		if ($this->fileId) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$reviewAttachment =& $monographFileDao->getMonographFile($this->fileId);

			assert(!is_null($reviewAttachment));
			$templateMgr->assign_by_ref('attachmentFile', $reviewAttachment);
		}
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'rowId'));
	}

	/**
	 * Save name for library file
	 */
	function execute(&$args, &$request) {
		import('classes.file.MonographFileManager');
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $reviewAssignmentDao->getById($this->reviewId);

		$monographFileManager = new MonographFileManager($reviewAssignment->getMonographId());
		if ($monographFileManager->uploadedFileExists('attachment')) {
			if ($reviewAssignment->getReviewerFileId() != null) {
				$fileId = $monographFileManager->uploadReviewFile('attachment', $reviewAssignment->getReviewerFileId(), $this->reviewId);
			} else {
				$fileId = $monographFileManager->uploadReviewFile('attachment', null, $this->reviewId);
			}
		}

		return $fileId;
	}
}

?>
