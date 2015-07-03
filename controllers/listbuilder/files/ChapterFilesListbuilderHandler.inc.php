<?php

/**
 * @file controllers/listbuilder/files/ChapterFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterFilesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for selecting files to associate with chapters.
 */

import('lib.pkp.controllers.listbuilder.files.FilesListbuilderHandler');

class ChapterFilesListbuilderHandler extends FilesListbuilderHandler {
	/** @var int Chapter ID */
	var $_chapterId;

	/**
	 * Constructor
	 */
	function ChapterFilesListbuilderHandler() {
		// Get access to the monograph file constants.
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);
		import('classes.monograph.MonographFile');
		parent::FilesListbuilderHandler();

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR),
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}


	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
		$this->setTitle('submission.files');
		$this->_chapterId = $request->getUserVar('chapterId');
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 */
	function loadData($request) {
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapter = $chapterDao->getChapter($this->_chapterId, $monograph->getId());
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles = $submissionFileDao->getLatestRevisions($monograph->getId());
		$filteredFiles = array();
		if ($chapter) foreach ($monographFiles as $monographFile) {
			if ($monographFile->getData('chapterId') == $chapter->getId()) $filteredFiles[$monographFile->getFileId()] = $monographFile;
		}
		return $filteredFiles;
	}

	//
	// Implement methods from FilesListbuilderHandler
	//
	/**
	 * @see FilesListbuilderHandler::getOptions()
	 */
	function getOptions() {
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles = $submissionFileDao->getLatestRevisions($monograph->getId());

		return parent::getOptions($monographFiles);
	}

	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array('chapterId' => $this->_chapterId)
		);
	}

}

?>
