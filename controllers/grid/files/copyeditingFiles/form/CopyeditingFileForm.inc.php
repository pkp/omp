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
	function CopyeditingFileForm($monograph, $signoffId, $template = null) {
		$this->_monograph =& $monograph;
		$this->_signoffId = $signoffId;

		if(!$template) {
			// Use the default template
			parent::Form('controllers/grid/files/copyeditingFiles/form/copyeditingFileForm.tpl');
		} else {
			parent::Form($template);
		}

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
		$this->_data['signoffId'] = $this->getSignoffId();
		$monograph =& $this->getMonograph();
		$this->_data['monographId'] = $monograph->getId();;
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->getSignoffId());

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
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('signoffId'));
	}


	//
	// Public helper methods
	//
	/**
	 * Upload a copyediting file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function uploadFile($args, &$request) {
		// Get the copyediting signoff
		$signoffDao =& DAORegistry::getDAO('SignoffDAO'); /* @var $signoffDao SignoffDAO */
		$signoff =& $signoffDao->getById($this->getSignoffId());
		assert(is_a($signoff, 'Signoff'));

		// Get the file that is being copyedited
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO'); /* @var $monographFileDao MonographFileDAO */
		$copyeditingFile =& $monographFileDao->getMonographFile($signoff->getAssocId());

		// Get the copyedited file if it exists
		if($signoff->getFileId()) {
			$copyeditedFile =& $monographFileDao->getMonographFile($signoff->getFileId());
		}

		// If we're updating a file, get its ID for the file manager
		$copyeditedFileId = isset($copyeditedFile) ? $copyeditedFile->getFileId() : null;

		$monograph =& $this->getMonograph();
		import('classes.file.MonographFileManager');
		if (MonographFileManager::uploadedFileExists('copyeditingFile')) {
			$copyeditedFileId = MonographFileManager::uploadCopyeditResponseFile($monograph->getId(), 'copyeditingFile', $copyeditedFileId);
			if (isset($copyeditedFileId)) {
				// Amend the copyediting signoff with the new file
				$signoff->setFileId($copyeditedFileId);
				$signoff->setDateCompleted(Core::getCurrentDate());
				$signoffDao->updateObject($signoff);

				$copyeditedFile =& $monographFileDao->getMonographFile($copyeditedFileId);
				// Transfer some of the original file's metadata over to the new file
				$copyeditedFile->setName($copyeditingFile->getLocalizedName(), Locale::getLocale());
				$copyeditedFile->setMonographFileTypeId($copyeditingFile->getMonographFileTypeId());
				$monographFileDao->updateMonographFile($copyeditedFile);
			}

		}
		return $copyeditedFileId;
	}
}

?>
