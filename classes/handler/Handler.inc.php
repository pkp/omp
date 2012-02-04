<?php

/**
 * @file classes/handler/Handler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup core
 *
 * @brief Base request handler application class
 */


import('lib.pkp.classes.handler.PKPHandler');
import('classes.handler.validation.HandlerValidatorPress');

class Handler extends PKPHandler {
	function Handler() {
		parent::PKPHandler();
	}

	/**
	 * Returns a "best-guess" press, based in the request data, if
	 * a request needs to have one in its context but may be in a site-level
	 * context as specified in the URL.
	 * @param $request Request
	 * @return mixed Either a Press or null if none could be determined.
	 */
	function getTargetPress($request) {

		// Get the requested path.
		$router =& $request->getRouter();
		$requestedPath = $router->getRequestedContextPath($request);
		$press = null;

		if ($requestedPath == 'index') {
			// No press requested. Check how many presses has the site.
			$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
			$presses =& $pressDao->getPresses();
			$pressesCount = $presses->getCount();
			if ($pressesCount === 1) {
				// Return the unique press.
				$press =& $presses->next();
			} elseif ($pressesCount > 1) {
				// Decide wich press to return.
				$user =& $request->getUser();
				if ($user) {
					// We have a user (private access).
					$press =& $this->_getFirstUserPress($user, $presses->toArray());
				} else {
					// Get the site redirect.
					$press =& $this->_getSiteRedirectPress($request);
				}
			}
		} else {
			// Return the requested press.
			$press =& $router->getContext($request);
		}
		if (is_a($press, 'Press')) {
			return $press;
		}
		return null;
	}

	/**
	 * Return the first press that user is enrolled with.
	 * @param $user User
	 * @param $presses Array
	 * @return mixed Either Press or null
	 */
	function _getFirstUserPress($user, $presses) {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$press = null;
		foreach($presses as $workingPress) {
			$userIsEnrolled = $userGroupDao->userInAnyGroup($user->getId(), $workingPress->getId());
			if ($userIsEnrolled) {
				$press = $workingPress;
				break;
			}
		}
		return $press;
	}

	/**
	 * Return the press that is configured in site redirect setting.
	 * @param $request Request
	 * @return mixed Either Press or null
	 */
	function _getSiteRedirectPress($request) {
		$pressDao =& DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$site =& $request->getSite();
		$press = null;
		if ($site) {
			if($site->getRedirect()) {
				$press = $pressDao->getById($site->getRedirect());
			}
		}
		return $press;
	}
}

?>
