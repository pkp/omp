<?php

/**
 * @file controllers/grid/settings/library/form/FileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/edditing a file
 * stores/retrieves from an associative array
 */

import('lib.pkp.classes.form.Form');

class FileForm extends Form {
	/** the type of file being uploaded */
	var $fileType;

	/** the id of the file being edited */
	var $fileId;

	/**
	 * Constructor.
	 */
	function FileForm($fileType, $fileId = null, $isUploading = false) {
		$this->fileType = $fileType;
		$this->fileId = $fileId;
		parent::Form('controllers/grid/settings/library/form/fileForm.tpl');

		if (!$isUploading) {
			$this->addCheck(new FormValidator($this, 'name', 'required', 'submission.nameRequired'));
		}
		$this->addCheck(new FormValidatorPost($this));
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

		$this->_data['fileType'] = $this->fileType;
		// grid related data
		$this->_data['gridId'] = $args['gridId'];
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

		if ($this->fileId) {
			$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
			$libraryFile =& $libraryFileDao->getById($this->fileId);
			assert(!is_null($libraryFile));
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign_by_ref('libraryFile', $libraryFile);
			$templateMgr->assign_by_ref('libraryFileName', $libraryFile->getLocalizedName());
		}
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'rowId', 'name'));
	}

	/**
	 * Upload library file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function uploadFile($args, &$request) {
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
		import('classes.file.LibraryFileManager');
		$libraryFileManager = new LibraryFileManager($context->getId());

		if ($libraryFileManager->uploadedFileExists('libraryFile')) {
			return $libraryFileManager->handleUpload($this->fileType, 'libraryFile', $this->fileId);
		}
	}

	/**
	 * Save name for library file
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function execute($args, &$request) {
		$name = $this->getData('name');
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFile =& $libraryFileDao->getById($this->fileId);
		$libraryFile->setName($name, Locale::getLocale());
		$libraryFileDao->updateObject($libraryFile);

		return $libraryFile;
	}
}

?>
