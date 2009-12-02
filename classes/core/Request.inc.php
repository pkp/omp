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
		$_this =& PKPRequest::_checkThis();
		$_this->redirectUrl($_this->url($pressPath, $page, $op, $path, $params, $anchor));
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getRequestedContextPath()
	 */
	function getRequestedPressPath() {
		static $press;
		$_this =& PKPRequest::_checkThis();

		if (!isset($press)) {
			$pressArray = $_this->_delegateToRouter('getRequestedContextPath', 1);
			$press = $pressArray[0];

			// call legacy hook
			HookRegistry::call('Request::getRequestedPressPath', array(&$press));
		}

		return $press;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContext()
	 */
	function &getPress() {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('getContext', 1);
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getRequestedContextPath()
	 */
	function getRequestedContextPath($contextLevel = null) {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('getRequestedContextPath', $contextLevel);
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContext()
	 */
	function &getContext($level = 1) {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('getContext', $level);
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContextByName()
	 */
	function &getContextByName($contextName) {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('getContextByName', $contextName);
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::url()
	 */
	function url($pressPath = null, $page = null, $op = null, $path = null,
			$params = null, $anchor = null, $escape = false) {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('url', $pressPath, $page, $op, $path,
			$params, $anchor, $escape);
	}

	/**
	 * Deprecated
	 * @see OMPPageRouter::redirectHome()
	 */
	function redirectHome() {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('redirectHome');
	}
}

?>
