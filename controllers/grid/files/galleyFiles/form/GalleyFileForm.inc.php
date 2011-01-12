<?php

/**
 * @file controllers/grid/files/galleyFiles/form/GalleyFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GalleyFileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/edditing a file
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class GalleyFileForm extends Form {
	/** the id of the monograph being edited */
	var $_monographId;

	/** the id of the galley signoff */
	var $_signoffId;

	/**
	 * Set the monograph
	 * @param $monograph Monograph
	 */
	function setMonograph(&$monograph) {
	    $this->_monograph =& $monograph;
	}

	/**
	 * Get the monograph
	 * @return Monograph
	 */
	function getMonograph() {
	    return $this->_monograph;
	}

	/**
	 * Set the signoff id
	 * @param $signoffId int
	 */
	function setSignoffId($signoffId) {
	    $this->_signoffId = (int) $signoffId;
	}

	/**
	 * Get the signoff id
	 * @return int
	 */
	function getSignoffId() {
	    return $this->_signoffId;
	}

	/**
	 * Constructor.
	 */
	function GalleyFileForm($monograph, $signoffId, $template = null) {
		$this->setMonograph($monograph);
		$this->setSignoffId($signoffId);

		if(!$template) {
			// Use the default template
			parent::Form('controllers/grid/files/galleyFiles/form/galleyFileForm.tpl');
		} else {
			parent::Form($template);
		}

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->_data['signoffId'] = $this->getSignoffId();
		$monograph =& $this->getMonograph();
		$this->_data['monographId'] = $monograph->getId();;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
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
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('signoffId'));
	}

	/**
	 * Upload a galley file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function uploadFile($args, &$request) {
		// Get the galley signoff
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->getSignoffId());
		assert(is_a($signoff, 'Signoff'));

		// Get the file that is being copyedited
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$galleyFile =& $submissionFileDao->getLatestRevision($signoff->getAssocId());

		// Get the copyedited file if it exists
		if($signoff->getFileId()) {
			$copyeditedFile =& $submissionFileDao->getLatestRevision($signoff->getFileId());
		}

		// If we're updating a file, get its ID for the file manager
		$copyeditedFileId = isset($copyeditedFile) ? $copyeditedFile->getFileId() : null;

		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');
		if (MonographFileManager::uploadedFileExists('galleyFile')) {
			$copyeditedFileId = MonographFileManager::uploadCopyeditResponseFile($monograph->getId(), 'galleyFile', $copyeditedFileId);
			if (isset($copyeditedFileId)) {
				// Amend the galley signoff with the new file
				$signoff->setFileId($copyeditedFileId);
				$signoff->setDateCompleted(Core::getCurrentDate());
				$signoffDao->updateObject($signoff);

				$copyeditedFile =& $submissionFileDao->getLatestRevision($copyeditedFileId);
				// Transfer some of the original file's metadata over to the new file
				$copyeditedFile->setName($galleyFile->getLocalizedName(), Locale::getLocale());
				$copyeditedFile->setGenreId($galleyFile->getGenreId());
				$submissionFileDao->updateObject($copyeditedFile);
			}

		}
		return $copyeditedFileId;
	}
}

?>
