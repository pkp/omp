<?php

/**
 * @file controllers/grid/users/author/AuthorGridHandler.inc.php
 *
 * Copyright (c) 2000-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridHandler
 * @ingroup controllers_grid_users_author
 *
 * @brief Handle author grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');


// import author grid specific classes
import('controllers.grid.users.author.AuthorGridCellProvider');
import('controllers.grid.users.author.AuthorGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class AuthorGridHandler extends GridHandler {
	/** @var Monograph */
	var $_monograph;

	/**
	 * Constructor
	 */
	function AuthorGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_PRESS_ASSISTANT, ROLE_ID_AUTHOR),
				array('fetchGrid', 'fetchRow', 'addAuthor', 'editAuthor',
				'updateAuthor', 'deleteAuthor'));
	}


	//
	// Getters/Setters
	//
	/**
	 * Get the monograph associated with this author grid.
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
		$this->setTitle('submission.contributors');

		// Grid actions
		$router =& $request->getRouter();
		$actionArgs = $this->getRequestArgs();
		$this->addAction(
			new LinkAction(
				'addAuthor',
				new AjaxModal(
					$router->url($request, null, null, 'addAuthor', null, $actionArgs),
					__('listbuilder.contributors.addContributor'),
					'addUser'
				),
				__('listbuilder.contributors.addContributor'),
				'add_item'
			)
		);

		// Columns
		$cellProvider = new AuthorGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'name',
				'author.users.contributor.name',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider,
				array('width' => 50, 'alignment' => COLUMN_ALIGNMENT_LEFT)
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
				'controllers/grid/users/author/primaryContact.tpl',
				$cellProvider
			)
		);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return AuthorGridRow
	 */
	function &getRowInstance() {
		$monograph =& $this->getMonograph();
		$row = new AuthorGridRow($monograph);
		return $row;
	}

	/**
	 * Get the arguments that will identify the data in the grid
	 * In this case, the monograph.
	 * @return array
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId()
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function &loadData($request, $filter = null) {
		$monograph =& $this->getMonograph();
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$data =& $authorDao->getAuthorsBySubmissionId($monograph->getId(), true);
		return $data;
	}

	//
	// Public Author Grid Actions
	//
	/**
	 * An action to manually add a new author
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function addAuthor($args, &$request) {
		// Calling editAuthor() with an empty row id will add
		// a new author.
		return $this->editAuthor($args, $request);
	}

	/**
	 * Edit a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author = $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->initData();

		$json = new JSONMessage(true, $authorForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Edit a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateAuthor($args, &$request) {
		// Identify the author to be updated
		$authorId = $request->getUserVar('authorId');
		$monograph =& $this->getMonograph();

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$author =& $authorDao->getAuthor($authorId, $monograph->getId());

		// Form handling
		import('controllers.grid.users.author.form.AuthorForm');
		$authorForm = new AuthorForm($monograph, $author);
		$authorForm->readInputData();
		if ($authorForm->validate()) {
			$authorId = $authorForm->execute();

			if(!isset($author)) {
				// This is a new contributor
				$author =& $authorDao->getAuthor($authorId, $monograph->getId());
				// New added author action notification content.
				$notificationContent = 'notification.addedAuthor';
			} else {
				// Author edition action notification content.
				$notificationContent = 'notification.editedAuthor';
			}

			// Create trivial notification.
			$currentUser =& $request->getUser();
			$notificationMgr = new NotificationManager();
			$notificationMgr->createTrivialNotification($currentUser->getId(), $notificationContent);

			// Prepare the grid row data
			$row =& $this->getRowInstance();
			$row->setGridId($this->getId());
			$row->setId($authorId);
			$row->setData($author);
			$row->initialize($request);

			// Render the row into a JSON response
			if($author->getPrimaryContact()) {
				// If this is the primary contact, redraw the whole grid
				// so that it takes the checkbox off other rows.
				return DAO::getDataChangedEvent();
			} else {
				return DAO::getDataChangedEvent($authorId);
			}
		} else {
			$json = new JSONMessage(true, $authorForm->fetch($request));
			return $json->getString();
		}
	}

	/**
	 * Delete a author
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteAuthor($args, &$request) {
		// Identify the submission Id
		$monographId = $request->getUserVar('monographId');
		// Identify the author to be deleted
		$authorId = $request->getUserVar('authorId');

		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$result = $authorDao->deleteAuthorById($authorId, $monographId);

		if ($result) {
			return DAO::getDataChangedEvent($authorId);
		} else {
			$json = new JSONMessage(false, Locale::translate('submission.submit.errorDeletingAuthor'));
			return $json->getString();
		}

	}
}

?>
