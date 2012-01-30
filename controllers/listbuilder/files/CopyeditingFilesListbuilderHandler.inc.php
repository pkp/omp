<?php

/**
 * @file controllers/listbuilder/files/CopyeditingFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingFilesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for selecting files to add a user to for copyediting.
 */

import('controllers.listbuilder.files.FilesListbuilderHandler');

class CopyeditingFilesListbuilderHandler extends FilesListbuilderHandler {
	/**
	 * Constructor
	 */
	function CopyeditingFilesListbuilderHandler() {
		// Get access to the monograph file constants.
		import('classes.monograph.MonographFile');
		parent::FilesListbuilderHandler(MONOGRAPH_FILE_COPYEDIT);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, &$args, $roleAssignments) {
		return parent::authorize($request, $args, $roleAssignments, WORKFLOW_STAGE_ID_EDITING);
	}


	//
	// Implement methods from FilesListbuilderHandler
	//
	/**
	 * @see FilesListbuilderHandler::initialize
	 */
	function initialize(&$request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);
		$this->setTitle('editor.monograph.selectCopyedingFiles');
	}

	/**
	 * @see controllers/listbuilder/files/FilesListbuilderHandler::getOptions()
	 */
	function getOptions() {
		import('classes.monograph.MonographFile');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles =& $submissionFileDao->getLatestRevisions($monograph->getId(), $this->getFileStage());

		return parent::getOptions($monographFiles);
	}
}

?>
