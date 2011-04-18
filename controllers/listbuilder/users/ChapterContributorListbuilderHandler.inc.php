<?php

/**
 * @file controllers/listbuilder/users/ChapterContributorListbuilderHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterContributorListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for adding contributors to a chapter
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class ChapterContributorListbuilderHandler extends ListbuilderHandler {
	/**
	 * Constructor
	 */
	function ChapterContributorListbuilderHandler() {
		parent::ListbuilderHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetch', 'addItem', 'deleteItems'));
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
		// Basic configuration
		$this->setTitle('listbuilder.contributors.addContributor');
		$this->setSourceTitle('common.name');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT); // Multiselect
		$this->setListTitle('submission.submit.currentContributors');

		$this->loadList($request);
		$this->loadPossibleItemList($request);

		$this->addColumn(new GridColumn('item', 'common.name'));
	}


	//
	// Public methods
	//
	/* Load the list from an external source into the grid structure */
	function loadList(&$request) {
		$monographId = $request->getUserVar('monographId');
		$chapterId = $request->getUserVar('chapterId');

		// Retrieve the contributors associated with this chapter to be displayed in the grid
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$chapterContributors =& $chapterAuthorDao->getAuthors($monographId, $chapterId);

		$items = array();
		if(isset($chapterContributors)) {
			while($item =& $chapterContributors->next()) {
				$id = $item->getId();
				$items[$id] = array('item' => $item->getFullName());
				unset($item);
			}
		}
		$this->setGridDataElements($items);
	}

	/* Load possible items to populate drop-down list with */
	function loadPossibleItemList(&$request) {
		$monographId = $request->getUserVar('monographId');
		$chapterId = $request->getUserVar('chapterId');

		// Retrieve all submissionContributors associated with this monograph to be displayed in the drop-down list
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$submissionContributors =& $authorDao->getAuthorsByMonographId($monographId);
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$contributorIds = $chapterAuthorDao->getAuthorIdsByChapterId($chapterId, $monographId);

		$itemList = array();
		while($submissionContributor =& $submissionContributors->next()) {
			$id = $submissionContributor->getId();
			if(!in_array($id, $contributorIds)) {
				$itemList[$id] = $item->getFullName();
			}
			unset($item);
		}
		$this->setPossibleItemList($itemList);
	}

	//
	// Overridden template methods
	//
	/**
	 * Need to add additional data to the template via the fetch method
	 * @see ListbuilderHandler::fetch()
	 */
	function fetch($args, &$request) {
		$router =& $request->getRouter();

		$monographId = $request->getUserVar('monographId');
		$chapterId = $request->getUserVar('chapterId');
		$additionalVars = array(
			'addUrl' => $router->url($request, array(), null, 'addItem', null, array('monographId' => $monographId, 'chapterId' => $chapterId)),
			'deleteUrl' => $router->url($request, array(), null, 'deleteItems', null, array('monographId' => $monographId, 'chapterId' => $chapterId))
		);

		return parent::fetch($args, &$request, $additionalVars);
	}

	/**
	 * @see PKPHandler::setupTemplate()
	 */
	function setupTemplate() {
		parent::setupTemplate();

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION));
	}

	//
	// Public AJAX-accessible functions
	//

	/*
	 * Handle adding an item to the list
	 */
	function addItem($args, &$request) {
		$monographId = $request->getUserVar('monographId');
		$chapterId = $request->getUserVar('chapterId');
		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');

		$rowId = "selectList-" . $this->getId();
		$contributorId = (int) $args[$rowId];

		if(!isset($contributorId)) {
			$json = new JSONMessage(false);
			return $json->getString();
		} else {
			// Make sure the item doesn't already exist
			$contributorIds = $chapterAuthorDao->getAuthorIdsByChapterId($chapterId, $monographId);
			if(in_array($contributorId, $contributorIds)) {
				$json = new JSONMessage(false, Locale::translate('common.listbuilder.itemExists'));
				return $json->getString();
				return false;
			}

			$contributor =& $authorDao->getAuthor($contributorId);
			$chapterAuthorDao->insertChapterAuthor($contributorId, $chapterId, $monographId);

			// Return JSON with formatted HTML to insert into list
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($contributorId);
			$rowData = array('item' => $contributor->getFullName());
			$row->setData($rowData);
			$row->initialize($request);

			$json = new JSONMessage(true, $this->_renderRowInternally($request, $row));
			return $json->getString();
		}
	}


	/*
	 * Handle deleting items from the list
	 */
	function deleteItems($args, &$request) {
		$monographId = array_shift($args);
		$chapterId = array_shift($args);

		$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');

		foreach($args as $item) {
				$chapterAuthorDao->deleteChapterAuthorById($item);
		}

		$json = new JSONMessage(true);
		return $json->getString();
	}
}
?>
