<?php

/**
 * @file classes/handler/Handler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Handler
 * @ingroup core
 *
 * @brief Base request handler application class
 */

import('lib.pkp.classes.handler.PKPHandler');

class Handler extends PKPHandler {
	/**
	 * Returns a "best-guess" press, based in the request data, if
	 * a request needs to have one in its context but may be in a site-level
	 * context as specified in the URL.
	 * @param $request Request
	 * @param $bestGuess true iff the function should make a best guess if no single context is appropriate
	 * @return mixed Either a Press or null if none could be determined.
	 */
	function getTargetContext($request, $bestGuess = true) {
		// Get the requested path.
		$router = $request->getRouter();
		$requestedPath = $router->getRequestedContextPath($request);

		if ($requestedPath === 'index' || $requestedPath === '') {
			// No press requested. Check how many presses has the site.
			$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
			$presses = $pressDao->getAll(true);
			$pressesCount = $presses->getCount();
			$press = null;
			if ($pressesCount === 1) {
				// Return the unique press.
				$press = $presses->next();
			}
			if (!$press && $pressesCount > 1) {
				// Decide which press to return.
				$user = $request->getUser();
				if ($user && $bestGuess) {
					// We have a user (private access).
					$press = $this->getFirstUserContext($user, $presses->toArray());
				}
				if (!$press) {
					// Get the site redirect.
					$press = $this->getSiteRedirectContext($request);
				}
			}
		} else {
			// Return the requested press.
			$press = $router->getContext($request);
		}
		if (is_a($press, 'Press')) {
			return $press;
		}
		return null;
	}
}


