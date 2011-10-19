<?php

/**
 * @file controllers/listbuilder/users/ChapterAuthorListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function ChapterAuthorListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
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
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$this->addPolicy(new OmpSubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Add locale keys
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));

		// Basic configuration
		$this->setTitle('submission.submit.addAuthor');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Multiselect

		// Fetch and authorize chapter
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$monograph =& $this->getMonograph();
		$chapter =& $chapterDao->getChapter(
			$request->getUserVar('chapterId'),
			$monograph->getId()
		);
		$this->setChapterId($chapter->getId());

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');
		// We can reuse the User cell provider because getFullName
		import('controllers.listbuilder.users/UserListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new UserListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'chapterId' => $this->getChapterId()
		);
	}

	/**
	 * @see GridHandler::getRowDataElement
	 * Get the data element that corresponds to the current request
	 * Allow for a blank $rowId for when creating a not-yet-persisted row
	 */
	function getRowDataElement(&$request, $rowId) {
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

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$monograph =& $this->getMonograph();
		$author =& $authorDao->getAuthor($id, $monograph->getId());
		return $author;
	}

	/**
	 * @see ListbuilderHandler::getOptions
	 */
	function getOptions() {
		// Initialize the object to return
		$items = array(
			array()
		);

		$monograph =& $this->getMonograph();
		$authors =& $monograph->getAuthors();

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
	 * @param PKPRequest $request
	 */
	function loadData(&$request, $filter) {
		$monograph =& $this->getMonograph();

		// Retrieve the contributors associated with this chapter to be displayed in the grid
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$chapterAuthors =& $chapterAuthorDao->getAuthors($monograph->getId(), $this->getChapterId());

		return $chapterAuthors;
	}

	//
	// Overridden template methods
	//
	//
	// Public AJAX-accessible functions
	//
	/**
	 * Persist a new entry insert.
	 * @param $request Request
	 * @param $newRowId mixed New entry with data to persist
	 * @return boolean
	 */
	function insertEntry(&$request, $newRowId) {
		$monograph =& $this->getMonograph();
		$monographId = $monograph->getId();
		$chapterId = $this->getChapterId();
		$authorId = (int) $newRowId['name'];

		// Create a new chapter author.
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		// FIXME: primary authors not set for chapter authors.
		return $chapterAuthorDao->insertChapterAuthor($authorId, $chapterId, $monographId);
	}

	/**
	 * Delete an entry.
	 * @param $request Request
	 * @param $rowId mixed ID of row to modify
	 * @return boolean
	 */
	function deleteEntry(&$request, $rowId) {
		$chapterId = $this->getChapterId();
		$authorId = (int) $rowId; // this is the authorId to remove and is already an integer
		if ($authorId) {
			// remove the chapter author.
			$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
			return $chapterAuthorDao->deleteChapterAuthorById($authorId, $chapterId);
		}
	}
}

?>
