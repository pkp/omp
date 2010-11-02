<?php

/**
 * @file controllers/grid/files/authorCopyeditingFiles/form/AuthorCopyeditingFilesUploadForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesUploadForm
 * @ingroup controllers_grid_files_authorCopyeditingFiles_form
 *
 * @brief Form for adding/edditing a submission file
 */

import('controllers.grid.files.copyeditingFiles.form.CopyeditingFileForm');

class AuthorCopyeditingFilesUploadForm extends CopyeditingFileForm {


	/**
	 * Constructor.
	 */
	function AuthorCopyeditingFilesUploadForm($monographId, $signoffId = null) {
		parent::CopyeditingFileForm($monographId, $signoffId, 'controllers/grid/files/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, &$request) {
		$this->_data['monographId'] = $this->_monographId;

		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$user =& $request->getUser();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_COPYEDITING', ASSOC_TYPE_MONOGRAPH_FILE, null, $user->getId());

		$monographFileOptions = array();
		while($signoff =& $signoffs->next()) {
			$monographFile =& $monographFileDao->getMonographFile($signoff->getAssocId());
			$fileName = $monographFile->getLocalizedName() != '' ? $monographFile->getLocalizedName() : Locale::translate('common.untitled');
			$monographFileOptions[$signoff->getId()] = $fileName;
			unset($signoff, $monographFile);
		}


		$this->_data['monographFileOptions'] =& $monographFileOptions;

		$this->_data['fileStage'] = MONOGRAPH_FILE_COPYEDIT;
		$this->_data['isRevision'] = false;
	}

	/**
	 * Fetch
	 * @param $request PKPRequest
	 * @see Form::fetch()
	 */
	function fetch(&$request) {
		return parent::fetch($request);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('gridId', 'fileType'));
	}

}

?>
