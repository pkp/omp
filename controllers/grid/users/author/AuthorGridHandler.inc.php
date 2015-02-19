<?php

/**
 * @file controllers/grid/users/author/AuthorGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorGridHandler
 * @ingroup controllers_grid_users_author
 *
 * @brief Handle author grid requests.
 */

// import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');
import('lib.pkp.controllers.grid.users.author.PKPAuthorGridHandler');


// import author grid specific classes
import('controllers.grid.users.author.AuthorGridRow');

// Link action & modal classes
import('lib.pkp.classes.linkAction.request.AjaxModal');

class AuthorGridHandler extends PKPAuthorGridHandler {

	/**
	 * Constructor
	 */
	function AuthorGridHandler() {
		parent::PKPAuthorGridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_AUTHOR),
				array('fetchGrid', 'fetchRow', 'addAuthor', 'editAuthor',
				'updateAuthor', 'deleteAuthor', 'saveSequence'));
		$this->addRoleAssignment(ROLE_ID_REVIEWER, array('fetchGrid', 'fetchRow'));
		$this->addRoleAssignment(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT), array('addUser'));
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
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.SubmissionAccessPolicy');
		$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize($request) {
		// Load submission-specific translations
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_SUBMISSION,
			LOCALE_COMPONENT_APP_DEFAULT
		);

		parent::initialize($request);
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * Determines if there should be administration actions on this grid.
	 * @return boolean
	 */
	function canAdminister() {
		$monograph = $this->getSubmission();
		$userRoles = $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES);
		// N.B.: $monograph will not be defined yet for feature
		// initialization, which happens prior to fetching.
		if (!$monograph || $monograph->getDateSubmitted() == null || array_intersect(array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR), $userRoles))
			return true;
		else
			return false;
	}
}

?>
