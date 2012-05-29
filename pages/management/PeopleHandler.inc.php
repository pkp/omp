<?php

/**
 * @file pages/manager/PeopleHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PeopleHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for people functions.
 */


import('pages.management.ManagementHandler');

class PeopleHandler extends ManagementHandler {
	/**
	 * Constructor
	 **/
	function PeopleHandler() {
		parent::ManagementHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER, 'userProfile');
	}


	/**
	 * Display a user's profile.
	 * @param $args array first parameter is the ID or username of the user to display
	 */
	function userProfile($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$templateMgr =& TemplateManager::getManager();

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userId = isset($args[0]) ? $args[0] : 0;
		if (is_numeric($userId)) {
			$user = $userDao->getUser($userId);
		} else {
			$user = $userDao->getUserByUsername($userId);
		}

		if ($user != null) {
			$templateMgr->assign('currentUrl', Request::url(null, null, 'userProfile', $user->getId()));

			$site =& $request->getSite();
			$press =& $request->getPress();

			$countryDao =& DAORegistry::getDAO('CountryDAO');
			$country = null;
			if ($user->getCountry() != '') {
				$country = $countryDao->getCountry($user->getCountry());
			}
			$templateMgr->assign('country', $country);

			$templateMgr->assign('userInterests', $user->getInterestString());

			$templateMgr->assign_by_ref('user', $user);
			$templateMgr->assign('localeNames', AppLocale::getAllLocales());
			$templateMgr->display('management/people/userProfile.tpl');
		}
	}
}

?>
