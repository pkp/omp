<?php

/**
 * @file controllers/grid/settings/library/form/EditLibraryFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditLibraryFileForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for editing a library file
 */

import('controllers.grid.settings.library.form.LibraryFileForm');

class EditLibraryFileForm extends LibraryFileForm {
	/** the type of file being uploaded LIBRARY_FILE_TYPE_... */
	var $fileType;

	/** the file being edited, or null for new */
	var $libraryFile;

	/** the id of the press this library file is attached to */
	var $pressId;

	/**
	 * Constructor.
	 * @param $pressId int
	 * @param $fileType int LIBRARY_FILE_TYPE_...
	 * @param $fileId int optional
	 */
	function EditLibraryFileForm($pressId, $fileType, $fileId) {
		parent::LibraryFileForm('controllers/grid/settings/library/form/editFileForm.tpl', $pressId, $fileType);
		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$this->libraryFile =& $libraryFileDao->getById($fileId);
		if (!$this->libraryFile || $this->libraryFile->getPressId() !== $this->pressId || (int) $this->libraryFile->getType() !== (int) $this->fileType) {
			fatalError('Invalid library file!');
		}
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$this->_data = array(
			'libraryFileName' => $this->libraryFile->getName(null), // Localized
			'libraryFile' => $this->libraryFile // For read-only info
		);
	}

	/**
	 * Save name for library file
	 */
	function execute() {
		$this->libraryFile->setName($this->getData('libraryFileName'), null); // Localized

		$libraryFileDao =& DAORegistry::getDAO('LibraryFileDAO');
		$libraryFileDao->updateObject($this->libraryFile);
	}
}

?>
