<?php

/**
 * @file classes/core/PageRouter.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PageRouter
 * @ingroup core
 *
 * @brief Class providing OMP-specific page routing.
 */

// $Id$


import('lib.pkp.classes.core.PKPPageRouter');

class PageRouter extends PKPPageRouter {
	/**
	 * get the cacheable pages
	 * @return array
	 */
	function getCacheablePages() {
		return array('about', 'announcement', 'help', 'index', 'information', 'rt', '');
	}

	/**
	 * Redirect to user home page (or the user group home page if the user has one user group).
	 * @param $request PKPRequest the request to be routed
	 */
	function redirectHome(&$request) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$user = $request->getUser();
		$userId = $user->getId();

		if ($press =& $this->getContext($request, 1)) {
			// The user is in the press context, see if they have one role only
			$userGroups =& $userGroupDao->getByUserId($userId, $press->getId());
			if($userGroups->getCount() == 1) {
				$userGroup =& $userGroups->next();
				if ($userGroup->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
				$request->redirect(null, $userGroup->getPath());
			} else {
				$request->redirect(null, 'user');
			}
		} else {
			// The user is at the site context, check to see if they are
			// only registered in one place w/ one role
			$userGroups =& $userGroupDao->getByUserId($userId);

			if($userGroups->getCount() == 1) {
				$pressDao =& DAORegistry::getDAO('PressDAO');
				$userGroup =& $userGroups->next();
				$press =& $pressDao->getPress($userGroup->getPressId());
				if (!isset($press)) $request->redirect('index', 'user');;
				if ($userGroup->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
				$request->redirect($press->getPath(), $userGroup->getPath());
			} else $request->redirect('index', 'user');
		}
	}
}

?>
