<?php

/**
 * @file controllers/grid/files/copyeditingFiles/form/CopyeditingFileForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
	/** the id of the copyediting signoff */
	var $_signoffId;

	/** the id of the monograph being edited */
	var $_monographId;

	/**
	 * Constructor.
	 */
	function CopyeditingFileForm($monographId, $signoffId, $template = null) {
		$this->_monographId = $monographId;
		$this->_signoffId = $signoffId;
		if(!$template) {
			parent::Form('controllers/grid/files/copyeditingFiles/form/copyeditingFileForm.tpl');
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
		$this->_data['signoffId'] = $this->_signoffId;
		$this->_data['monographId'] = $this->_monographId;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->_signoffId);

		if ($signoff && $copyeditedFileId = $signoff->getFileId()) {
			$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
			$copyeditedFile =& $monographFileDao->getMonographFile($copyeditedFileId);

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
	 * Upload a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function uploadFile($args, &$request) {
		// Get the copyediting signoff
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->_signoffId);
		assert(is_a($signoff, 'Signoff'));

		// Get the file that is being copyedited
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$copyeditingFile =& $monographFileDao->getMonographFile($signoff->getAssocId());

		// Get the copyedited file if it exists
		if($signoff->getFileId()) {
			$copyeditedFile =& $monographFileDao->getMonographFile($signoff->getFileId());
		}

		$copyeditedFileId = isset($copyeditedFile) ? $copyeditedFile->getFileId() : null;

		import('classes.file.MonographFileManager');
		$monographFileManager = new MonographFileManager($this->_monographId);

		if ($monographFileManager->uploadedFileExists('copyeditingFile')) {
			$copyeditedFileId = $monographFileManager->uploadCopyeditFile('copyeditingFile', $copyeditedFileId);
			if (isset($copyeditedFileId)) {
				// Amend the copyediting signoff with the new file
				$signoff->setFileId($copyeditedFileId);
				$signoff->setDateCompleted(Core::getCurrentDate());
				$signoffDao->updateObject($signoff);

				$copyeditedFile =& $monographFileDao->getMonographFile($copyeditedFileId);
				// Transfer some of the original file's metadata over to the new file
				$copyeditedFile->setName($copyeditingFile->getLocalizedName(), Locale::getLocale());
				$copyeditedFile->setMonographFileType($copyeditingFile->getMonographFileType());
				$monographFileDao->updateMonographFile($copyeditedFile);
			}

		}
		return $copyeditedFileId;
	}
}

?>
