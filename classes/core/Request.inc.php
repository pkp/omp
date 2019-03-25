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
	public function getRequestedPressPath() {
		$press = $this->_delegateToRouter('getRequestedContextPath', 1);
		HookRegistry::call('Request::getRequestedPressPath', array(&$press));
		return $press;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContext()
	 */
	public function &getPress() {
		$returner = $this->_delegateToRouter('getContext', 1);
		return $returner;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getRequestedContextPath()
	 */
	public function getRequestedContextPath($contextLevel = null) {
		// Emulate the old behavior of getRequestedContextPath for
		// backwards compatibility.
		if (is_null($contextLevel)) {
			return $this->_delegateToRouter('getRequestedContextPaths');
		} else {
			return array($this->_delegateToRouter('getRequestedContextPath', $contextLevel));
		}
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContext()
	 */
	public function &getContext($level = 1) {
		$returner = $this->_delegateToRouter('getContext', $level);
		return $returner;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::getContextByName()
	 */
	public function &getContextByName($contextName) {
		$returner = $this->_delegateToRouter('getContextByName', $contextName);
		return $returner;
	}

	/**
	 * Deprecated
	 * @see PKPPageRouter::url()
	 */
	public function url($pressPath = null, $page = null, $op = null, $path = null,
			$params = null, $anchor = null, $escape = false) {
		return $this->_delegateToRouter('url', $pressPath, $page, $op, $path,
			$params, $anchor, $escape);
	}

	/**
	 * Deprecated
	 * @see PageRouter::redirectHome()
	 */
	public function redirectHome() {
		return $this->_delegateToRouter('redirectHome');
	}

	/**
	 * @see PKPRequest::getUserAgent()
	 */
	public function getUserAgent() {
		static $userAgent;
		$userAgent = parent::getUserAgent();

		if (strpos($userAgent, 'Shockwave Flash')) {
			$userAgent = $_SERVER['HTTP_BROWSER_USER_AGENT'];
		}

		return $userAgent;
	}
}


