<?php

/**
 * @file controllers/grid/users/submissionContributor/SubmissionContributorGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionContributorGridHandler
 * @ingroup controllers_grid_users_submissionContributor
 *
 * @brief Handle submissionContributor grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import submissionContributor grid specific classes
import('controllers.grid.users.submissionContributor.SubmissionContributorGridCellProvider');
import('controllers.grid.users.submissionContributor.SubmissionContributorGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class SubmissionContributorGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function SubmissionContributorGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_AUTHOR, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_MANAGER),
				array('fetchGrid', 'addSubmissionContributor', 'editSubmissionContributor',
				'updateSubmissionContributor', 'deleteSubmissionContributor'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this submissionContributor grid.
	 * @return Monograph
	 */
	function &getMonograph() {
		return $this->_monograph;
	}

	/**
	 * Set the MonographId
	 * @param Monograph
	 */
	function setMonograph($monograph) {
		$this->_monograph =& $monograph;
	}


	//
	// Overridden methods from PKPHandler
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
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Retrieve the authorized monograph.
		$this->setMonograph($this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH));

		// Load submission-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));

		// Basic grid configuration
		$this->setTitle('submission.submit.addAuthor');

		// Get the monograph id
		$monograph =& $this->getMonograph();
		assert(is_a($monograph, 'Monograph'));
		$monographId = $monograph->getId();

		// Retrieve the submissionContributors associated with this monograph to be displayed in the grid
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$data =& $authorDao->getAuthorsByMonographId($monographId);
		$this->setGridDataElements($data);

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = array('monographId' => $monographId);
		$this->addAction(
			new LinkAction(
				'addSubmissionContributor',
				new AjaxModal(
					$router->url($request, null, null, 'addSubmissionContributor', null, $actionArgs),
					__('grid.action.addAuthor'),
					'fileManagement'
				),
				__('grid.action.addAuthor'),
				'add_item'
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
	function addSubmissionContributor($args, &$request) {
		// Calling editSubmissionContributor() with an empty row id will add
		// a new submissionContributor.
		return $this->editSubmissionContributor($args, $request);
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editSubmissionContributor($args, &$request) {
		// Identify the submissionContributor to be updated
		$submissionContributorId = $request->getUserVar('submissionContributorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$submissionContributor = $authorDao->getAuthor($submissionContributorId);

		// Form handling
		import('controllers.grid.users.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($this->getMonograph(), $submissionContributor);
		$submissionContributorForm->initData();

		$json = new JSONMessage(true, $submissionContributorForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateSubmissionContributor($args, &$request) {
		// Identify the submissionContributor to be updated
		$submissionContributorId = $request->getUserVar('submissionContributorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$submissionContributor =& $authorDao->getAuthor($submissionContributorId);

		// Form handling
		import('controllers.grid.users.submissionContributor.form.SubmissionContributorForm');
		$submissionContributorForm = new SubmissionContributorForm($this->getMonograph(), $submissionContributor);
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
				$additionalAttributes = array('script' => 'deleteElementById(\'#isPrimaryContact\')');
				$json = new JSONMessage(true, $this->_renderRowInternally($request, $row), true, null, $additionalAttributes);
			} else {
				$json = new JSONMessage(true, $this->_renderRowInternally($request, $row));
			}
		} else {
			$json = new JSONMessage(false, Locale::translate('editor.monograph.addUserError'));
		}
		return $json->getString();
	}

	/**
	 * Delete a submissionContributor
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteSubmissionContributor($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the submissionContributor to be deleted
		$submissionContributorId = $request->getUserVar('submissionContributorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($submissionContributorId, $monographId);

		if ($result) {
			$json = new JSONMessage(true);
		} else {
			$json = new JSONMessage(false, Locale::translate('submission.submit.errorDeletingSubmissionContributor'));
		}
		return $json->getString();
	}
}

?>
