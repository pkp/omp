<?php

/**
 * @file controllers/grid/settings/library/form/LibraryFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LibraryFileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/editing a file
 */

import('lib.pkp.classes.form.Form');

class LibraryFileForm extends Form {
	/** the type of file being uploaded LIBRARY_FILE_TYPE_... */
	var $fileType;

	/** the id of the press this library file is attached to */
	var $pressId;

	/**
	 * Constructor.
	 * @param $pressId int
	 * @param $fileType int LIBRARY_FILE_TYPE_...
	 */
	function LibraryFileForm($template, $pressId, $fileType) {
		$this->pressId = $pressId;
		$this->fileType = $fileType;

		parent::Form($template);

		$this->addCheck(new FormValidator($this, 'libraryFileName', 'required', 'settings.libraryFiles.nameRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER));

		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('libraryFileName'));
	}
}

?>
