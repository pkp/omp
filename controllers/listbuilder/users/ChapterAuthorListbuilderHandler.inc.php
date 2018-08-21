<?php

/**
 * @file controllers/listbuilder/users/ChapterAuthorListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterAuthorListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding contributors to a chapter
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class ChapterAuthorListbuilderHandler extends ListbuilderHandler {
	/** @var integer The chapter ID that we'll filter stage participants on **/
	var $_chapterId;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array('fetch', 'fetchRow', 'fetchOptions', 'save')
		);
	}

	//
	// Getters/Setters
	//
	/**
	 * Get the authorized monograph.
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
	}

	/**
	 * Set the user group id
	 * @param $chapterId int
	 */
	function setChapterId($chapterId) {
		$this->_chapterId = $chapterId;
	}

	/**
	 * Get the user group id
	 * @return int
	 */
	function getChapterId() {
		return $this->_chapterId;
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * @copydoc ListbuilderHandler::initialize
	 */
	function initialize($request, $args = null) {
		parent::initialize($request, $args);

		// Add locale keys
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION);

		// Basic configuration
		$this->setTitle('submission.submit.addAuthor');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('authors');

		// Fetch and authorize chapter
		$chapterDao = DAORegistry::getDAO('ChapterDAO');
		$monograph = $this->getMonograph();
		$chapter = $chapterDao->getChapter(
			$request->getUserVar('chapterId'),
			$monograph->getId()
		);
		if ($chapter) {
			// This is an existing chapter
			$this->setChapterId($chapter->getId());
		} else {
			// This is a new chapter
			$this->setChapterId(null);
		}

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		// We can reuse the User cell provider because getFullName
		import('lib.pkp.controllers.listbuilder.users.UserListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new UserListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}

	/**
	 * @see GridHandler::initFeatures()
	 */
	function initFeatures($request, $args) {
		import('lib.pkp.classes.controllers.grid.feature.OrderListbuilderItemsFeature');
		return array(new OrderListbuilderItemsFeature());
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph = $this->getMonograph();
		return array(
			'submissionId' => $monograph->getId(),
			'chapterId' => $this->getChapterId()
		);
	}

	/**
	 * @copydoc GridHandler::getRowDataElement
	 */
	function getRowDataElement($request, &$rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}
		$id = 0;
		// Otherwise return from the newRowId
		$authorId = $this->getNewRowId($request); // this is an array:  Example: $authorId['name'] => 25
		if (isset($authorId['name'])) {
			$id = (int) $authorId['name'];
		}

		$authorDao = DAORegistry::getDAO('AuthorDAO');
		$monograph = $this->getMonograph();
		return $authorDao->getById($id, $monograph->getId());
	}

	/**
	 * @see ListbuilderHandler::getOptions
	 */
	function getOptions() {
		// Initialize the object to return
		$items = array(
			array()
		);

		$monograph = $this->getMonograph();
		$authors = $monograph->getAuthors();

		foreach ($authors as $author) {
			$items[0][$author->getId()] = $author->getFullName();
		}
		unset($authors);

		return $items;
	}


	//
	// Public methods
	//
	/*
	 * Load the data for the list builder
	 * @see ListbuilderHandler::loadData
	 */
	function loadData($request, $filter) {
		$monograph = $this->getMonograph();

		// If it's a new chapter, it has no authors.
		if (!$this->getChapterId()) return array();

		// Retrieve the contributors associated with this chapter to be displayed in the grid
		$chapterAuthorDao = DAORegistry::getDAO('ChapterAuthorDAO');
		$chapterAuthors = $chapterAuthorDao->getAuthors($monograph->getId(), $this->getChapterId());

		return $chapterAuthors;
	}
}


