<?php

/**
 * @file controllers/grid/files/submissionDocuments/form/EditLibraryFileForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditLibraryFileForm
 * @ingroup controllers_grid_files_submissionDocuments_form
 *
 * @brief Form for editing a library file
 */

import('controllers.grid.files.form.LibraryFileForm');

class EditLibraryFileForm extends LibraryFileForm {
	/** the file being edited, or null for new */
	var $libraryFile;

	/** the id of the monograph for this library file */
	var $monographId;

	/**
	 * Constructor.
	 * @param $pressId int
	 * @param $fileType int LIBRARY_FILE_TYPE_...
	 * @param $fileId int optional
	 */
	function EditLibraryFileForm($pressId, $fileId, $monographId) {
		parent::LibraryFileForm('controllers/grid/files/submissionDocuments/form/editFileForm.tpl', $pressId);

		$this->monographId = $monographId;
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$this->libraryFile =& $libraryFileDao->getById($fileId);

		if (!$this->libraryFile || $this->libraryFile->getPressId() !== $this->pressId || $this->libraryFile->getMonographId() !== $this->getMonographId()) {
			fatalError('Invalid library file!');
		}
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$this->_data = array(
			'monographId' => $this->libraryFile->getMonographId(),
			'libraryFileName' => $this->libraryFile->getName(null), // Localized
			'libraryFile' => $this->libraryFile // For read-only info
		);
	}

	/**
	 * Save name for library file
	 */
	function execute() {
		$this->libraryFile->setName($this->getData('libraryFileName'), null); // Localized
		$this->libraryFile->setType($this->getData('fileType'));

		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFileDao->updateObject($this->libraryFile);
	}

	/**
	 * return the monograph ID for this library file.
	 * @return int
	 */
	function getMonographId() {
		return $this->monographId;
	}
}

?>
