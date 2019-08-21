<?php

/**
 * @file controllers/listbuilder/files/ChapterFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
	function __construct() {
		// Get access to the monograph file constants.
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);
		import('lib.pkp.classes.submission.SubmissionFile');
		parent::__construct();

		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR),
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}
	/**
	 * @copydoc PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments, $stageId = null) {
		import('lib.pkp.classes.security.authorization.WorkflowStageAccessPolicy'); // context-specific.
		import('lib.pkp.classes.security.authorization.PublicationAccessPolicy'); // context-specific.
		if ($stageId !== null) $this->addPolicy(new WorkflowStageAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId), true);
		else $this->addPolicy(new PublicationAccessPolicy($request, $args, $roleAssignments));

		return parent::authorize($request, $args, $roleAssignments);
	}


	/**
	 * @copydoc FilesListbuilderHandler::initialize
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
		$this->setTitle('submission.files');
		$this->_chapterId = $request->getUserVar('chapterId');
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @see FilesListbuilderHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$publication = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION);
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$chapter = $chapterDao->getChapter($this->_chapterId, $publication->getId());
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFiles = $submissionFileDao->getLatestRevisions($submission->getId());
		$filteredFiles = array();
		if ($chapter) foreach ($submissionFiles as $submissionFile) {
			if ($submissionFile->getData('chapterId') == $chapter->getId()) $filteredFiles[$submissionFile->getFileId()] = $submissionFile;
		}
		return $filteredFiles;
	}

	//
	// Implement methods from FilesListbuilderHandler
	//
	/**
	 * @copydoc FilesListbuilderHandler::getOptions()
	 */
	function getOptions($request, $submissionFiles = null) {
		assert($submissionFiles === null);

		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFiles = $submissionFileDao->getLatestRevisions($submission->getId());

		return parent::getOptions($request, $submissionFiles);
	}

	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$publication = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION);
		return array_merge(
			parent::getRequestArgs(),
			array('publicationId' => $publication->getId(), 'chapterId' => $this->_chapterId)
		);
	}

}


