<?php

/**
 * @file controllers/grid/files/copyedit/form/CopyeditingFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/edditing a file
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class CopyeditingFileForm extends Form {
	/** the id of the monograph being edited */
	var $_monograph;

	/** the id of the copyediting signoff */
	var $_signoffId;

	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $signoffId integer
	 * @param $template string
	 */
	function CopyeditingFileForm($monograph, $signoffId, $template = 'controllers/grid/files/copyedit/form/copyeditingFileForm.tpl') {
		$this->_monograph =& $monograph;
		$this->_signoffId = $signoffId;

		parent::Form($template);

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the monograph
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Get the signoff id
	 * @return int
	 */
	function getSignoffId() {
		return $this->_signoffId;
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		$monograph =& $this->getMonograph();
		$this->_data['monographId'] = $monograph->getId();
		$this->_data['signoffId'] = $this->getSignoffId();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->getSignoffId());

		if ($signoff && $copyeditedFileId = $signoff->getFileId()) {
			$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
			$copyeditedFile =& $submissionFileDao->getLatestRevision($copyeditedFileId);

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('copyeditedFile', $copyeditedFile);
			$templateMgr->assign_by_ref('copyeditedFileName', $copyeditedFile->getLocalizedName());
		}
		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('temporaryFileId'));
		return parent::readInputData();
	}


	//
	// Public helper methods
	//
	/**
	 * Upload a copyediting file
	 * @param $userId int User ID of current user
	 * @return int Copyedited file ID
	 */
	function execute($userId) {
		// Get the copyediting signoff
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->getSignoffId());
		assert(is_a($signoff, 'Signoff'));

		// Get the file that is being copyedited
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$copyeditingFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());

		// Get the copyedited file if it exists
		if($signoff->getFileId()) {
			die('CASE NOT IMPLEMENTED YET');
			$copyeditedFile =& $submissionFileDao->getLatestRevision($signoff->getFileId());
		}

		// If we're updating a file, get its ID for the file manager
		$copyeditedFileId = isset($copyeditedFile) ? $copyeditedFile->getFileId() : null;

		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');

		// Fetch the temporary file storing the uploaded library file
		$temporaryFileDao =& DAORegistry::getDAO('TemporaryFileDAO');
		$temporaryFile =& $temporaryFileDao->getTemporaryFile(
			$this->getData('temporaryFileId'),
			$userId
		);

		$copyeditedFile = MonographFileManager::copyCopyeditorResponseFromTemporaryFile($temporaryFile, $monograph->getId(), $copyeditedFileId, $copyeditingFile->getGenreId());
		if (isset($copyeditedFile)) {
			// Amend the copyediting signoff with the new file
			$signoff->setFileId($copyeditedFile->getFileId());
			$signoff->setFileRevision($copyeditedFile->getRevision());

			$signoff->setDateCompleted(Core::getCurrentDate());

			$signoffDao->updateObject($signoff);
			$submissionFileDao->updateObject($copyeditedFile);
		}

		return $copyeditedFile?$copyeditedFile->getFileId():null;
	}
}

?>
