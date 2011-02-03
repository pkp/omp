<?php

/**
 * @file controllers/tab/settings/AccessSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AccessSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Access and Security page.
 */

// Import the base Handler.
import('classes.handler.Handler');

class AccessSettingsTabHandler extends Handler {

	/**
	 * Constructor
	 **/
	function AccessSettingsTabHandler() {
		parent::Handler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('users', 'roles', 'enrollment'));
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, $args = null) {
		parent::initialize($request, $args);

		// Load grid-specific translations
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));
	}


	//
	// Public handler methods
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Handle user management requests.
	 * @param $args
	 * @param $request PKPRequest
	 */
	function users($args, &$request) {
		$this->setupTemplate(true);
		$press =& $request->getPress();

		import('lib.pkp.classes.user.PKPUserDAO');
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_GRID));

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$userGroupOptions = array('' => Locale::translate('grid.user.allRoles'));
		while (!$userGroups->eof()) {
			$userGroup =& $userGroups->next();
			$userGroupOptions[$userGroup->getId()] = $userGroup->getLocalizedName();
		}

		$fieldOptions = array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);

		$matchOptions = array(
			'contains' => 'form.contains',
			'is' => 'form.is'
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('userGroupOptions', $userGroupOptions);
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('matchOptions', $matchOptions);
		$templateMgr->assign('currentPage', 'users');
		return $templateMgr->fetchJson('controllers/tab/settings/users.tpl');
	}

	/**
	 * Handle role management requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function roles($args, &$request) {
		$this->setupTemplate(true);
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$roleOptions = $roleDao->getPressRoleNames();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('roleOptions', $roleOptions);
		$templateMgr->assign('currentPage', 'roles');
		return $templateMgr->fetchJson('controllers/tab/settings/roles.tpl');
	}

	/**
	 * Handle user enrollment requests.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function enrollment($args, &$request) {
		$this->setupTemplate(true);

		import('lib.pkp.classes.user.PKPUserDAO');

		$fieldOptions = array(
			USER_FIELD_FIRSTNAME => 'user.firstName',
			USER_FIELD_LASTNAME => 'user.lastName',
			USER_FIELD_USERNAME => 'user.username',
			USER_FIELD_EMAIL => 'user.email'
		);

		$matchOptions = array(
			'contains' => 'form.contains',
			'is' => 'form.is'
		);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('fieldOptions', $fieldOptions);
		$templateMgr->assign('matchOptions', $matchOptions);
		$templateMgr->assign('currentPage', 'enrollment');
		return $templateMgr->fetchJson('controllers/tab/settings/enrollment.tpl');
	}
}
?>
