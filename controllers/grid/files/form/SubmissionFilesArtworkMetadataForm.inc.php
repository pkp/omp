<?php

/**
 * @file controllers/grid/files/submissionFiles/form/SubmissionFilesArtworkMetadataForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesArtworkMetadataForm
 * @ingroup controllers_grid_files_submissionFiles_form
 *
 * @brief Form for editing artwork file metadata.
 */

import('controllers.grid.files.form.SubmissionFilesMetadataForm');

class SubmissionFilesArtworkMetadataForm extends SubmissionFilesMetadataForm {
	/**
	 * Constructor.
	 */
	function SubmissionFilesArtworkMetadataForm(&$submissionFile, $additionalActionArgs = array()) {
		parent::SubmissionFilesMetadataForm(&$submissionFile, $additionalActionArgs);
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('artworkCaption', 'artworkCredit', 'artworkCopyrightOwner',
				'artworkCopyrightOwnerContact', 'artworkPermissionTerms', 'artworkPlacement'));
		parent::readInputData();
	}

	/**
	 * @see Form::execute()
	 */
	function execute($args, $request) {
		//
		// FIXME: Should caption, credit, or any other fields be localized?
		// FIXME: How to upload a permissions file?
		// FIXME: How to assign a chapter to the artwork file?
		// FIXME: How to select a contact author from the submission author list?
		// All, see #6416.
		//

		// Update the sumbission file by reference.
		$submissionFile =& $this->getSubmissionFile();
		$submissionFile->setCaption($this->getData('artworkCaption'));
		$submissionFile->setCredit($this->getData('artworkCredit'));
		$submissionFile->setCopyrightOwner($this->getData('artworkCopyrightOwner'));
		$submissionFile->setCopyrightOwnerContactDetails($this->getData('artworkCopyrightOwnerContact'));
		$submissionFile->setPermissionTerms($this->getData('artworkPermissionTerms'));
		$submissionFile->setPlacement($this->getData('artworkPlacement'));

		// Persist the submission file.
		parent::execute($args, $request);
	}

}

?>
