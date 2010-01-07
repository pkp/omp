<?php

/**
 * @file classes/core/PageRouter.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PageRouter
 * @ingroup core
 *
 * @brief Class providing OMP-specific page routing.
 */

// $Id$


import('core.PKPPageRouter');

class PageRouter extends PKPPageRouter {
	/**
	 * get the cacheable pages
	 * @return array
	 */
	function getCacheablePages() {
		return array('about', 'announcement', 'help', 'index', 'information', 'rt', '');
	}

	/**
	 * Redirect to user home page (or the role home page if the user has one role).
	 * @param $request PKPRequest the request to be routed
	 */
	function redirectHome(&$request) {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user = $request->getUser();
		$userId = $user->getId();

		if ($press =& $this->getContext($request, 1)) {
			// The user is in the press context, see if they have one role only
			$roles =& $roleDao->getRolesByUserId($userId, $press->getId());
			if(count($roles) == 1) {
				$role = array_shift($roles);
				if ($role->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
				$request->redirect(null, $role->getRolePath());
			} else {
				$request->redirect(null, 'user');
			}
		} else {
			// The user is at the site context, check to see if they are
			// only registered in one place w/ one role
			$roles = $roleDao->getRolesByUserId($userId);

			if(count($roles) == 1) {
				$pressDao =& DAORegistry::getDAO('PressDAO');
				$role = array_shift($roles);
				$press = $pressDao->getPress($role->getId());
				if (!isset($press)) $request->redirect('index', 'user');;
				if ($role->getRoleId() == ROLE_ID_READER) $request->redirect(null, 'index');
				$request->redirect($press->getPath(), $role->getRolePath());
			} else $request->redirect('index', 'user');
		}
	}
}

?>
