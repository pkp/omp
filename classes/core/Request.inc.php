<?php

/**
 * @file classes/core/Request.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Request
 * @ingroup core
 *
 * @brief Class providing operations associated with HTTP requests.
 * Requests are assumed to be in the format http://host.tld/index.php/<press_id>/<page_name>/<operation_name>/<arguments...>
 * <press_id> is assumed to be "index" for top-level site requests.
 */


import('lib.pkp.classes.core.PKPRequest');

class Request extends PKPRequest {
	/**
	 * Deprecated
	 * @see PKPPageRouter::getRequestedContextPath()
	 */
	function getRequestedPressPath() {
		static $press;
		$_this =& PKPRequest::_checkThis();

		if (!isset($press)) {
			$press = $_this->_delegateToRouter('getRequestedContextPath', 1);
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
		$returner = $_this->_delegateToRouter('getContext', 1);
		return $returner;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getRequestedContextPath()
	 */
	function getRequestedContextPath($contextLevel = null) {
		$_this =& PKPRequest::_checkThis();

		// Emulate the old behavior of getRequestedContextPath for
		// backwards compatibility.
		if (is_null($contextLevel)) {
			return $_this->_delegateToRouter('getRequestedContextPaths');
		} else {
			return array($_this->_delegateToRouter('getRequestedContextPath', $contextLevel));
		}
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContext()
	 */
	function &getContext($level = 1) {
		$_this =& PKPRequest::_checkThis();
		$returner = $_this->_delegateToRouter('getContext', $level);
		return $returner;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContextByName()
	 */
	function &getContextByName($contextName) {
		$_this =& PKPRequest::_checkThis();
		$returner = $_this->_delegateToRouter('getContextByName', $contextName);
		return $returner;
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
	 * @see PageRouter::redirectHome()
	 */
	function redirectHome() {
		$_this =& PKPRequest::_checkThis();
		return $_this->_delegateToRouter('redirectHome');
	}

	/**
	 * @see PKPRequest::getUserAgent()
	 */
	function getUserAgent() {
		static $userAgent;
		$userAgent = parent::getUserAgent();

		if (strpos($userAgent, 'Shockwave Flash')) {
			$userAgent = $_SERVER['HTTP_BROWSER_USER_AGENT'];
		}

		return $userAgent;
	}
}


