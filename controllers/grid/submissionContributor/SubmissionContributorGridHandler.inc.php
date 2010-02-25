<?php

/**
 * @file controllers/grid/submissionContributor/SubmissionContributorGridHandler.inc.php
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
import('controllers.grid.GridHandler');


// import submissionContributor grid specific classes
import('controllers.grid.submissionContributor.SubmissionContributorGridCellProvider');
import('controllers.grid.submissionContributor.SubmissionContributorGridRow');

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
		return array_merge(parent::getRemoteOperations(), array('addSubmissionContributor', 'editSubmissionContributor', 'updateSubmissionConstributor', 'deleteSubmissionContributor'));
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
	 * Validate that the user is the assigned section editor for
	 * the submissionContributor's monograph, or is a managing editor. Raises a
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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_AUTHOR, LOCALE_COMPONENT_PKP_SUBMISSION));

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
				'grid.action.addItem'
			)
		);

		// Columns
		$emptyColumnActions = array();
		$cellProvider = new SubmissionContributorGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'author.submit.contributor.name',
				$emptyColumnActions,
				'controllers/grid/gridCellInSpan.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'email',
				'author.submit.contributor.email',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'role',
				'author.submit.contributor.role',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);
		$this->addColumn(
			new GridColumn(
				'principalContact',
				'author.submit.contributor.principalContact',
				$emptyColumnActions,
				'controllers/grid/gridCell.tpl',
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
		$this->editSubmissionContributor($args, $request);
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editSubmissionContributor(&$args, &$request) {
		// Identify the submissionContributor to be updated
		$submissionContributor =& $this->_getSubmissionContributorFromArgs($args);

		// Form handling
		import('controllers.grid.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($submissionContributor);
		$submissionContributorForm->initData();
		$submissionContributorForm->display($request);

		// The form has already been displayed.
		return '';
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function updateSubmissionContributor(&$args, &$request) {
		// Identify the submissionContributor to be updated
		$submissionContributor =& $this->_getSubmissionContributorFromArgs($args);

		// Form handling
		import('controllers.grid.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($submissionContributor);
		$submissionContributorForm->readInputData();
		if ($submissionContributorForm->validate()) {
			$submissionContributorForm->execute();
			$json = new JSON('true');
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
		// Identify the submissionContributor to be deleted
		$submissionContributor =& $this->_getSubmissionContributorFromArgs($args);

		$submissionContributorDAO = DAORegistry::getDAO('SubmissionContributorDAO');
		$result = $submissionContributorDAO->deleteSubmissionContributor($submissionContributor);

		if ($result) {
			$json = new JSON('true');
		} else {
			$json = new JSON('false', Locale::translate('author.submit.errorDeletingSubmissionContributor'));
		}
		return $json->getString();
	}

	//
	// Private helper function
	//
	/**
	 * This will retrieve a submissionContributor object from the
	 * grids data source based on the request arguments.
	 * If no submissionContributor can be found then this will raise
	 * a fatal error.
	 * @param $args array
	 * @param $createIfMissing boolean If this is set to true
	 *  then a submissionContributor object will be instantiated if no
	 *  submissionContributor id is in the request.
	 * @return SubmissionContributor
	 */
	function &_getSubmissionContributorFromArgs(&$args) {
		// Identify the submissionContributor id and retrieve the
		// corresponding element from the grid's data source.
		if (!isset($args['rowId'])) {
			$submissionContributor = false;
		} else {
			$submissionContributor =& $this->getRowDataElement($args['rowId']);
			if (is_null($submissionContributor)) fatalError('Invalid submissionContributor id!');
		}
		return $submissionContributor;
	}
}