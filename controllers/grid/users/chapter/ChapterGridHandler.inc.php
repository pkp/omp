<?php

/**
 * @file controllers/grid/users/chapter/ChapterGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterGridHandler
 * @ingroup controllers_grid_users_chapter
 *
 * @brief Handle chapter grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.CategoryGridHandler');
import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

// import chapter grid specific classes
import('controllers.grid.users.submissionContributor.SubmissionContributorGridCellProvider');
import('controllers.grid.users.chapter.ChapterGridCategoryRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class ChapterGridHandler extends CategoryGridHandler {

	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function ChapterGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'fetchCategory', 'addChapter', 'editChapter', 'updateChapter', 'deleteChapter'));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the monograph associated with this chapter grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the monograph associated with this chapter grid.
	 * @param $monograph Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
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

	/**
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		assert(is_a($monograph, 'Monograph'));
		$this->setMonograph($monograph);

		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS, LOCALE_COMPONENT_OMP_SUBMISSION));
		// Basic grid configuration
		$this->setTitle('grid.chapters.title');

		// Set the category data
		$chapterDao =& DAORegistry::getDAO('ChapterDAO');
		$chapters =& $chapterDao->getChapters($monograph->getId());
		$this->setGridDataElements($chapters);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monograph->getId());
		$this->addAction(
			new LinkAction(
				'addChapter',
				new AjaxModal(
					$router->url($request, null, null, 'addChapter', null, $actionArgs),
					__('submission.chapter.addChapter'),
					'fileManagement'
				),
				__('grid.action.addItem'),
				'add_item'
			)
		);

		// Columns
		// reuse the cell providers for the SubmissionContributorGrid
		$cellProvider = new SubmissionContributorGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'email',
				'author.users.contributor.email',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'author.users.contributor.role',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'principalContact',
				'author.users.contributor.principalContact',
				null,
				'controllers/grid/users/submissionContributor/primaryContact.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return ChapterGridRow
	 */
	function &getCategoryRowInstance() {
		$row = new ChapterGridCategoryRow();
		return $row;
	}


	//
	// Implement template methods from CategoryGridHandler
	//
	/**
	 * @see CategoryGridHandler::getCategoryData()
	 */
	function getCategoryData(&$chapter) {
		$authorFactory =& $chapter->getAuthors(); /* @var $authorFactory DAOResultFactory */
		$authors = $authorFactory->toAssociativeArray();
		return $authors;
	}


	//
	// Public Chapter Grid Actions
	//
	/**
	 * Add a chapter.
	 * @param $args array
	 * @param $request Request
	 */
	function addChapter($args, &$request) {
		// Calling editChapter() with an empty row id will add
		// a new chapter.
		return $this->editChapter($args, $request);
	}

	/**
	 * Edit a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editChapter($args, &$request) {
		// Identify the chapter to be updated
		$chapterId = $request->getUserVar('chapterId');

		// Form handling
		import('controllers.grid.users.chapter.form.ChapterForm');
		$chapterForm = new ChapterForm($this->getMonograph(), $chapterId);
		if ($chapterForm->isLocaleResubmit()) {
			$chapterForm->readInputData();
		} else {
			$chapterForm->initData();
		}

		$json = new JSONMessage(true, $chapterForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Update a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateChapter($args, &$request) {
		// Identify the chapter to be updated
		$chapterId = $request->getUserVar('chapterId');

		// Form initialization
		import('controllers.grid.users.chapter.form.ChapterForm');
		$chapterForm = new ChapterForm($this->getMonograph(), $chapterId);
		$chapterForm->readInputData();

		// Form validation
		if ($chapterForm->validate()) {
			$chapterForm->execute();

			$chapter =& $chapterForm->getChapter();

			// Prepare the grid row data
			$categoryRow =& $this->getCategoryRowInstance();
			$categoryRow->setGridId($this->getId());
			$categoryRow->setId($chapter->getId());
			$categoryRow->setData($chapter);
			$categoryRow->initialize($request);

			// Render the row into a JSON response
			$chapterAuthorDao =& DAORegistry::getDAO('ChapterAuthorDAO');
			$monograph =& $this->getMonograph();
			$authors =& $chapterAuthorDao->getAuthors($monograph->getId(), $chapter->getId());
			$groupIterator = $chapter->getId() % 5;
			$json = new JSONMessage(true, $this->_renderCategoryInternally($request, $categoryRow, $groupIterator));
		} else {
			// Return an error
			$json = new JSONMessage(false);
		}

		// Return the serialized JSON response
		return $json->getString();
	}

	/**
	 * Delete a chapter
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteChapter($args, &$request) {
		// Identify the chapter to be deleted
		$chapter =& $this->_getChapterFromArgs($args);

		$chapterDAO = DAORegistry::getDAO('ChapterDAO');
		$result = $chapterDAO->deleteChapter($chapter);

		if ($result) {
			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, Locale::translate('submission.chapters.grid.errorDeletingChapter'));
		}
		return $json->getString();
	}
}

?>
