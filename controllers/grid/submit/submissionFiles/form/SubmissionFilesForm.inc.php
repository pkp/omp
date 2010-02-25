<?php

/**
 * @file controllers/grid/submit/submissionFiles/form/SubmissionFilesForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFilesForm
 * @ingroup controllers_grid_file_form
 *
 * @brief Form for adding/edditing a submission file
 */

import('form.Form');

class SubmissionFilesForm extends Form {
//	/** the type of file being uploaded */
//	var $fileType;
	
	/** the id of the file being edited */
	var $fileId; 
	
	/**
	 * Constructor.
	 */
	function SubmissionFilesForm($fileId = null) {
		$this->fileId = $fileId;		
		parent::Form('controllers/grid/submissionFiles/form/fileForm.tpl');

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
		if ( isset($this->fileId) ) {
			$this->_data['fileId'] = $this->fileId;
		}
		
//		$this->_data['fileType'] = $this->fileType;
		// grid related data
		$this->_data['gridId'] = $args['gridId'];
		if ( isset($this->fileId) ) {
			$this->_data['rowId'] = $this->fileId;
		}
	}

	/**
	 * Display
	 */
	function display() {
		parent::display();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('gridId'));
	}

	function uploadFile(&$args, &$request) {
		$router =& $request->getRouter();
		$context =& $router->getContext($request);
	//	import('file.LibraryFileManager');
	//	$libraryFileManager = new LibraryFileManager($context->getId());
	//	return $libraryFileManager->handleUpload($this->fileType, 'libraryFile', $this->fileId);
	}

	/**
	 * Save submission file
	 */
	function execute(&$args, &$request) {
		
	}
}

?>
