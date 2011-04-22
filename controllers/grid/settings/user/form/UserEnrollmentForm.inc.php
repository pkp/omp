<?php

/**
 * @file controllers/grid/settings/user/form/UserEnrollmentForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserEnrollmentForm
 * @ingroup controllers_grid_settings_user_form
 *
 * @brief Form for enrolling users and editing user profiles.
 */

import('lib.pkp.classes.form.Form');

class UserEnrollmentForm extends Form {

	/**
	 * Constructor.
	 */
	function UserEnrollmentForm() {
		parent::Form('controllers/grid/settings/user/form/userEnrollmentForm.tpl');
	}

	/**
	 * Display the form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function display($args, &$request) {
		$userDao =& DAORegistry::getDAO('UserDAO');
		$users =& $userDao->getUsersByField();

		$userOptions[''] = Locale::translate('grid.user.pleaseSelectUser');
		while(!$users->eof()) {
			$user =& $users->next();
			$userOptions[$user->getId()] = $user->getFullName(true);
			unset($user);
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('userOptions', $userOptions);
		return $this->fetch($request);
	}
}

?>
