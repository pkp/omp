<?php

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<press_id>/<page_name>/<operation_name>/<arguments...>
 * <press_id> is assumed to be "index" for top-level site requests.
 */

// $Id$


import('core.PKPRequest');

class Request extends PKPRequest {
	/**
	 * Redirect to the specified page within OMP. Shorthand for a common call to Request::redirect(Request::url(...)).
	 * @param $pressPath string The path of the Press to redirect to.
	 * @param $page string The name of the op to redirect to.
	 * @param $op string optional The name of the op to redirect to.
	 * @param $path mixed string or array containing path info for redirect.
	 * @param $params array Map of name => value pairs for additional parameters
	 * @param $anchor string Name of desired anchor on the target page
	 */
	function redirect($pressPath = null, $page = null, $op = null, $path = null, $params = null, $anchor = null) {
		Request::redirectUrl(Request::url($pressPath, $page, $op, $path, $params, $anchor));
	}

	/**
	 * Get the Press path requested in the URL ("index" for top-level site requests).
	 * @return string 
	 */
	function getRequestedPressPath() {
		static $press;

		if (!isset($press)) {
			if (Request::isPathInfoEnabled()) {
				$press = '';
				if (isset($_SERVER['PATH_INFO'])) {
					$vars = explode('/', $_SERVER['PATH_INFO']);
					if (count($vars) >= 2) {
						$press = Core::cleanFileVar($vars[1]);
					}
				}
			} else {
				$press = Request::getUserVar('press');
			}

			$press = empty($press) ? 'index' : $press;
			HookRegistry::call('Request::getRequestedPressPath', array(&$press));
		}

		return $press;
	}

	/**
	 * Get the Press associated with the current request.
	 * @return Press
	 */
	function &getPress() {
		static $press;

		if (!isset($press)) {
			$path = Request::getRequestedPressPath();
			if ($path != 'index') {
				$pressDao =& DAORegistry::getDAO('PressDAO');
				$press = $pressDao->getPressByPath(Request::getRequestedPressPath());
			}
		}

		return $press;
	}

	/**
	 * A Generic call to a context-defined path (e.g. a Press or a Conference's path) 
	 * @param $contextLevel int (optional) the number of levels of context to return in the path
	 * @return array of String (each element the path to one context element)
	 */
	function getRequestedContextPath($contextLevel = null) {
		//there is only one $contextLevel, so no need to check
		return array(Request::getRequestedPressPath());
	}
	
	/**
	 * A Generic call to a context defining object (e.g. a Press, a Conference, or a SchedConf)
	 * @return Press
	 * @param $level int (optional) the desired context level
	 */
	function &getContext($level = 1) {
		$returner = false;
		switch ($level) {
			case 1:
				$returner =& Request::getPress();
				break;
		}
		return $returner;	
	}	
	
	/**
	 * Get the object that represents the desired context (e.g. Conference or Press)
	 * @param $contextName String specifying the page context 
	 * @return Press
	 */
	function &getContextByName($contextName) {
		$returner = false;
		switch ($contextName) {
			case 'press':
				$returner =& Request::getPress();
				break;
		}
		return $returner;
	}
	/**
	 * Build a URL into OMP.
	 * @param $pressPath string Optional path for press to use
	 * @param $page string Optional name of page to invoke
	 * @param $op string Optional name of operation to invoke
	 * @param $path mixed Optional string or array of args to pass to handler
	 * @param $params array Optional set of name => value pairs to pass as user parameters
	 * @param $anchor string Optional name of anchor to add to URL
	 * @param $escape boolean Whether or not to escape ampersands for this URL; default false.
	 */
	function url($pressPath = null, $page = null, $op = null, $path = null, 
			$params = null, $anchor = null, $escape = false) {
		return parent::url(array($pressPath), $page, $op, $path, $params, $anchor, $escape);
	}

	/**
	 * Redirect to user home page (or the role home page if the user has one role).
	 */
	function redirectHome() {
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$user = Request::getUser();
		$userId = $user->getId();

		if ($press =& Request::getPress()) { 
			// The user is in the press context, see if they have one role only
			$roles =& $roleDao->getRolesByUserId($userId, $press->getId());
			if(count($roles) == 1) {
				$role = array_shift($roles);
				Request::redirect(null, $role->getRolePath());
			} else {
				Request::redirect(null, 'user');
			}
		} else {
			// The user is at the site context, check to see if they are
			// only registered in one place w/ one role
			$roles = $roleDao->getRolesByUserId($userId);
			
			if(count($roles) == 1) {
				$pressDao =& DAORegistry::getDAO('PressDAO');
				$role = array_shift($roles);
				$press = $pressDao->getPress($role->getId());
				isset($press) ? Request::redirect($press->getPath(), $role->getRolePath()) :
								  Request::redirect('index', 'user');
			} else Request::redirect('index', 'user');
		}
	}
}

?>
