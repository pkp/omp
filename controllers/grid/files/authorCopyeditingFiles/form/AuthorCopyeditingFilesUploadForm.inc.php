<?php

/**
 * @file controllers/grid/files/authorCopyeditingFiles/form/AuthorCopyeditingFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorCopyeditingFilesUploadForm
 * @ingroup controllers_grid_files_authorCopyeditingFiles_form
 *
 * @brief Form for adding/editing a submission file
 */

import('controllers.grid.files.copyeditingFiles.form.CopyeditingFileForm');

class AuthorCopyeditingFilesUploadForm extends CopyeditingFileForm {
	/**
	 * Constructor.
	 * @param $monograph Monograph
	 * @param $signoffId integer
	 */
	function AuthorCopyeditingFilesUploadForm($monograph, $signoffId = null) {
		parent::CopyeditingFileForm($monograph, $signoffId, 'controllers/grid/files/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Implement template methods from Form
	//
	/**
	 * @see Form::initData()
	 */
	function initData($args, &$request) {
		// Retrieve the monograph id.
		$monograph =& $this->getMonograph();
		$this->setData('monographId', $monograph->getId());

		// Retrieve signoffs.
		$user =& $request->getUser();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, null, $user->getId());

		// Retrieve monograph files.
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFileOptions = array();
		while($signoff =& $signoffs->next()) {
			$monographFile =& $monographFileDao->getMonographFile($signoff->getAssocId());
			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
			$monographFileOptions[$signoff->getId()] = $fileName;
			unset($signoff, $monographFile);
		}
		$this->setData('monographFileOptions', $monographFileOptions);

		// Set other data.
		$this->setData('fileStage', MONOGRAPH_FILE_COPYEDIT);
		$this->setData('isRevision', false);
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		return parent::fetch($request);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'fileType'));
	}
}

?>
