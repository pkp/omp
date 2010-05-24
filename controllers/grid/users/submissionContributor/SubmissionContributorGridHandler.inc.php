<?php

/**
 * @file controllers/grid/users/submissionContributor/SubmissionContributorGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorGridHandler
 * @ingroup controllers_grid_submissionContributor
 *
 * @brief Handle submissionContributor grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import submissionContributor grid specific classes
import('controllers.grid.users.submissionContributor.SubmissionContributorGridCellProvider');
import('controllers.grid.users.submissionContributor.SubmissionContributorGridRow');

class SubmissionContributorGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function SubmissionContributorGridHandler() {
		parent::GridHandler();
	}

	//
	// Getters/Setters
	//
	/**
	 * @see PKPHandler::getRemoteOperations()
	 * @return array
	 */
	function getRemoteOperations() {
		return array_merge(parent::getRemoteOperations(), array('addSubmissionContributor', 'editSubmissionContributor', 'updateSubmissionContributor', 'deleteSubmissionContributor'));
	}

	/**
	 * Get the monograph associated with this submissionContributor grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	//
	// Overridden methods from PKPHandler
	//
	/**
	 * Validate that ...
	 * fatal error if validation fails.
	 * @param $requiredContexts array
	 * @param $request PKPRequest
	 * @return boolean
	 */
	function validate($requiredContexts, $request) {
		// FIXME: implement validation
		// Retrieve and validate the monograph id
		$monographId =& $request->getUserVar('monographId');
		if (!is_numeric($monographId)) return false;

		// Retrieve the monograph associated with this citation grid
		$monographDAO =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDAO->getMonograph($monographId);

		// Monograph and editor validation
		if (!is_a($monograph, 'Monograph')) return false;

		// Validation successful
		$this->_monograph =& $monograph;
		return true;
	}

	/*
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		// Basic grid configuration
		$this->setTitle('author.submit.addAuthor');

		// Get the monograph id
		$monograph =& $this->getMonograph();
		assert(is_a($monograph, 'Monograph'));
		$monographId = $monograph->getId();

		// Retrieve the submissionContributors associated with this monograph to be displayed in the grid
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$data =& $authorDao->getAuthorsByMonographId($monographId);
		$this->setData($data);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monographId);
		$this->addAction(
			new GridAction(
				'addSubmissionContributor',
				GRID_ACTION_MODE_MODAL,
				GRID_ACTION_TYPE_APPEND,
				$router->url($request, null, null, 'addSubmissionContributor', null, $actionArgs),
				'grid.action.addAuthor'
			)
		);

		// Columns
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
	 * @return SubmissionContributorGridRow
	 */
	function &getRowInstance() {
		// Return a submissionContributor row
		$row = new SubmissionContributorGridRow();
		return $row;
	}


	//
	// Public SubmissionContributor Grid Actions
	//
	/**
	 * An action to manually add a new submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addSubmissionContributor(&$args, &$request) {
		// Calling editSubmissionContributor() with an empty row id will add
		// a new submissionContributor.
		return $this->editSubmissionContributor($args, $request);
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editSubmissionContributor(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the submissionContributor to be updated
		$submissionContributorId = $request->getUserVar('submissionContributorId');
		//$submissionContributor =& $this->_getSubmissionContributorFromArgs($args);
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$submissionContributor = $authorDao->getAuthor($submissionContributorId);

		// Form handling
		import('controllers.grid.users.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($monographId, $submissionContributor);
		$submissionContributorForm->initData();

		$json = new JSON('true', $submissionContributorForm->display($request));
		return $json->getString();
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateSubmissionContributor(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the submissionContributor to be updated
		$submissionContributorId = $request->getUserVar('submissionContributorId');
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$submissionContributor =& $authorDao->getAuthor($submissionContributorId);

		// Form handling
		import('controllers.grid.users.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($monographId, $submissionContributor);
		$submissionContributorForm->readInputData();
		if ($submissionContributorForm->validate()) {
			$authorId = $submissionContributorForm->execute();

			if(!isset($submissionContributor)) {
				// This is a new contributor
				$submissionContributor =& $authorDao->getAuthor($authorId);
			}

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($authorId);
			$row->setData($submissionContributor);
			$row->initialize($request);

			// Render the row into a JSON response
			if($submissionContributor->getPrimaryContact()) {
				$additionalAttributes = array('script' => 'updateItem(\'remove\', \'isPrimaryContact\')');
				$json = new JSON('true', $this->_renderRowInternally($request, $row), 'true', null, $additionalAttributes);
			} else {
				$json = new JSON('true', $this->_renderRowInternally($request, $row));
			}
		} else {
			$json = new JSON('false', Locale::translate('author.submit.errorUpdatingSubmissionContributor'));
		}
		return $json->getString();
	}

	/**
	 * Delete a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteSubmissionContributor(&$args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the submissionContributor to be deleted
		$submissionContributorId = $request->getUserVar('submissionContributorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($submissionContributorId, $monographId);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('author.submit.errorDeletingSubmissionContributor'));
		}
		return $json->getString();
	}
}