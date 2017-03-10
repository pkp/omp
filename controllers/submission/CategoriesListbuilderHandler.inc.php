<?php

/**
 * @file controllers/submission/CategoriesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoriesListbuilderHandler
 * @ingroup controllers_submission
 *
 * @brief Class for assigning categories to submissions.
 */

import('lib.pkp.classes.controllers.listbuilder.ListbuilderHandler');

class CategoriesListbuilderHandler extends ListbuilderHandler {
	/** @var $press Press */
	var $_press;

	/** @var The group ID for this listbuilder */
	var $submissionId;

	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_MANAGER, ROLE_ID_AUTHOR),
			array('fetch', 'fetchRow', 'fetchOptions')
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.ContextAccessPolicy');
		$this->addPolicy(new ContextAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Set the submission ID
	 * @param $submissionId int
	 */
	function setSubmissionId($submissionId) {
		$this->submissionId = $submissionId;
	}

	/**
	 * Get the submission ID
	 * @return int
	 */
	function getSubmissionId() {
		return $this->submissionId;
	}

	/**
	 * Set the current press
	 * @param $press Press
	 */
	function setPress($press) {
		$this->_press = $press;
	}

	/**
	 * Get the current press
	 * @return Press
	 */
	function getPress() {
		return $this->_press;
	}

	/**
	 * Load the list from an external source into the grid structure
	 * @param $request PKPRequest
	 */
	function loadData($request) {
		$press = $this->getPress();
		$submissionId = $this->getSubmissionId();

		if ($submissionId) {
			$submissionDao = DAORegistry::getDAO('MonographDAO');
			$assignedCategories = $submissionDao->getCategories($submissionId, $press->getId());
			return $assignedCategories;
		}

		return array();
	}

	/**
	 * Get possible items to populate autosuggest list with
	 */
	function getOptions() {
		$press = $this->getPress();
		$submissionId = $this->getSubmissionId();

		if ($submissionId) {
			// Preexisting submission
			$submissionDao = DAORegistry::getDAO('MonographDAO');
			$availableCategories = $submissionDao->getUnassignedCategories($submissionId, $press->getId());
		} else {
			// New submission
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$availableCategories = $categoryDao->getByPressId($press->getId());
		}

		$itemList = array(0 => array());
		while ($category = $availableCategories->next()) {
			$itemList[0][$category->getId()] = $category->getLocalizedTitle();
		}
		return $itemList;
	}

	/**
	 * Preserve the submission ID for internal listbuilder requests.
	 * @see GridHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$args = parent::getRequestArgs();
		$args['submissionId'] = $this->getSubmissionId();
		return $args;
	}


	/**
	 * @copydoc GridHandler::getRowDataElement
	 */
	function getRowDataElement($request, &$rowId) {
		// fallback on the parent if a rowId is found
		if ( !empty($rowId) ) {
			return parent::getRowDataElement($request, $rowId);
		}

		// Otherwise return from the $newRowId
		$newRowId = $this->getNewRowId($request);
		$categoryId = $newRowId['name'];
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$press = $request->getPress();
		$category = $categoryDao->getById($categoryId, $press->getId());
		return $category;
	}

	//
	// Overridden template methods
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);
		AppLocale::requireComponents(
			LOCALE_COMPONENT_PKP_MANAGER, 
			LOCALE_COMPONENT_APP_SUBMISSION
		);

		// Basic configuration
		$this->setPress($request->getPress());
		$this->setTitle('submission.submit.placement.categories');
		$this->setSourceType(LISTBUILDER_SOURCE_TYPE_SELECT);
		$this->setSaveType(LISTBUILDER_SAVE_TYPE_EXTERNAL);
		$this->setSaveFieldName('categories');

		$this->setSubmissionId($request->getUserVar('submissionId'));

		// Name column
		$nameColumn = new ListbuilderGridColumn($this, 'name', 'common.name');

		import('controllers.listbuilder.categories.CategoryListbuilderGridCellProvider');
		$nameColumn->setCellProvider(new CategoryListbuilderGridCellProvider());
		$this->addColumn($nameColumn);
	}
}

?>
