<?php

/**
 * @file controllers/grid/files/form/LibraryFileForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/editing a file
 */

import('lib.pkp.classes.form.Form');
import('classes.file.LibraryFileManager');

class LibraryFileForm extends Form {
	/** the id of the press this library file is attached to */
	var $pressId;

	/** the library file manager instantiated in this form. */
	var $libraryFileManager;

	/**
	 * Constructor.
	 * @param $template string
	 * @param $pressId int
	 */
	function LibraryFileForm($template, $pressId) {
		$this->pressId = $pressId;

		parent::Form($template);
		$this->libraryFileManager = new LibraryFileManager($pressId);

		$this->addCheck(new FormValidatorLocale($this, 'libraryFileName', 'required', 'settings.libraryFiles.nameRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'fileType', 'required', 'settings.libraryFiles.typeRequired',
				create_function('$type, $form, $libraryFileManager', 'return is_numeric($type) && $libraryFileManager->getNameFromType($type);'), array(&$this, $this->libraryFileManager)));

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		$press =& $request->getPress();
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_MANAGER);

		// load the file types for the selector on the form.
		$templateMgr =& TemplateManager::getManager();
		$libraryFileManager =& $this->libraryFileManager;
		$fileTypeKeys = $libraryFileManager->getTypeTitleKeyMap();
		$templateMgr->assign('fileTypes', $fileTypeKeys);

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('libraryFileName', 'fileType'));
	}
}

?>
