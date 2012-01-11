<?php

/**
 * @file controllers/grid/files/review/form/ManageSubmissionFilesForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManageSubmissionFilesForm
 * @ingroup controllers_grid_files_form
 *
 * @brief Form for add or removing files from a review
 */

import('lib.pkp.classes.form.Form');
import('classes.monograph.MonographFile');

class ManageSubmissionFilesForm extends Form {
	/** @var int **/
	var $_monographId;

	/**
	 * Constructor.
	 */
	function ManageSubmissionFilesForm($monographId, $template) {
		parent::Form($template);
		$this->_monographId = (int)$monographId;

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters / Setters
	//
	/**
	 * Get the monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->_monographId;
	}

	//
	// Overridden template methods
	//
	/**
	 * Initialize variables
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->setData('monographId', $this->_monographId);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('selectedFiles'));
	}

	/**
	 * Save review round files
	 * @param $args array
	 * @param $request PKPRequest
	 * @stageMonographFiles array The files that belongs to a file stage
	 * that is currently being used by a grid inside this form.
	 */
	function execute($args, &$request, &$stageMonographFiles, $fileStage) {
		$selectedFiles = (array)$this->getData('selectedFiles');
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monographFiles =& $submissionFileDao->getLatestRevisions($this->getMonographId());

		foreach ($monographFiles as $monographFile) {
			// Get the viewable flag value.
			$isViewable = in_array(
				$monographFile->getFileIdAndRevision(),
				$selectedFiles);

			// If this is a monograph file that belongs to the current stage id...
			if (array_key_exists($monographFile->getFileId(), $stageMonographFiles)) {
				// ...update the "viewable" flag accordingly.
				$monographFile->setViewable($isViewable);
			} else {
				// If the viewable flag is set to true...
				if ($isViewable) {
					// Make a copy of the file to the current file stage.
					import('classes.file.MonographFileManager');
					$monographFileManager = new MonographFileManager();
					// Split the file into file id and file revision.
					$fileId = $monographFile->getFileId();
					$revision = $monographFile->getRevision();
					list($newFileId, $newRevision) = $monographFileManager->copyFileToFileStage($fileId, $revision, $fileStage, null, true);
					if ($fileStage == MONOGRAPH_FILE_REVIEW_FILE) {
						$submissionFileDao->assignRevisionToReviewRound($newFileId, $newRevision, $this->getReviewRound());
					}
					$monographFile =& $submissionFileDao->getRevision($newFileId, $newRevision);
				}
			}
			$submissionFileDao->updateObject($monographFile);
			unset($monographFile);
		}
	}
}

?>
