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
	 * Redirect to the specified page within OJS. Shorthand for a common call to Request::redirect(Request::url(...)).
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
				$pressDao = &DAORegistry::getDAO('PressDAO');
				$press = $pressDao->getPressByPath(Request::getRequestedPressPath());
			}
		}

		return $press;
	}

	/**
	 * Build a URL into OMP.
	 * @param $pressPath string Optional path for Press to use
	 * @param $page string Optional name of page to invoke
	 * @param $op string Optional name of operation to invoke
	 * @param $path mixed Optional string or array of args to pass to handler
	 * @param $params array Optional set of name => value pairs to pass as user parameters
	 * @param $anchor string Optional name of anchor to add to URL
	 * @param $escape boolean Whether or not to escape ampersands for this URL; default false.
	 */
	function url($pressPath = null, $page = null, $op = null, $path = null, $params = null, $anchor = null, $escape = false) {
		$pathInfoDisabled = !Request::isPathInfoEnabled();

		$amp = $escape?'&amp;':'&';
		$prefix = $pathInfoDisabled?$amp:'?';

		// Establish defaults for page and op
		$defaultPage = Request::getRequestedPage();
		$defaultOp = Request::getRequestedOp();

		// If a Press has been specified, don't supply default
		// page or op.
		if ($pressPath) {
			$pressPath = rawurlencode($pressPath);
			$defaultPage = null;
			$defaultOp = null;
		} else {
			$press =& Request::getPress();
			if ($press) $pressPath = $press->getPath();
			else $pressPath = 'index';
		}

		// Get overridden base URLs (if available).
		$overriddenBaseUrl = Config::getVar('general', "base_url[$pressPath]");

		// If a page has been specified, don't supply a default op.
		if ($page) {
			$page = rawurlencode($page);
			$defaultOp = null;
		} else {
			$page = $defaultPage;
		}

		// Encode the op.
		if ($op) $op = rawurlencode($op);
		else $op = $defaultOp;

		// Process additional parameters
		$additionalParams = '';
		if (!empty($params)) foreach ($params as $key => $value) {
			if (is_array($value)) foreach($value as $element) {
				$additionalParams .= $prefix . $key . '%5B%5D=' . rawurlencode($element);
				$prefix = $amp;
			} else {
				$additionalParams .= $prefix . $key . '=' . rawurlencode($value);
				$prefix = $amp;
			}
		}

		// Process anchor
		if (!empty($anchor)) $anchor = '#' . rawurlencode($anchor);
		else $anchor = '';

		if (!empty($path)) {
			if (is_array($path)) $path = array_map('rawurlencode', $path);
			else $path = array(rawurlencode($path));
			if (!$page) $page = 'index';
			if (!$op) $op = 'index';
		}

		$pathString = '';
		if ($pathInfoDisabled) {
			$joiner = $amp . 'path%5B%5D=';
			if (!empty($path)) $pathString = $joiner . implode($joiner, $path);
			if (empty($overriddenBaseUrl)) $baseParams = "?press=$pressPath";
			else $baseParams = '';

			if (!empty($page) || !empty($overriddenBaseUrl)) {
				$baseParams .= empty($baseParams)?'?':$amp . "page=$page";
				if (!empty($op)) {
					$baseParams .= $amp . "op=$op";
				}
			}
		} else {
			if (!empty($path)) $pathString = '/' . implode('/', $path);
			if (empty($overriddenBaseUrl)) $baseParams = "/$pressPath";
			else $baseParams = '';

			if (!empty($page)) {
				$baseParams .= "/$page";
				if (!empty($op)) {
					$baseParams .= "/$op";
				}
			}
		}

		return ((empty($overriddenBaseUrl)?Request::getIndexUrl():$overriddenBaseUrl) . $baseParams . $pathString . $additionalParams . $anchor);
	}
}

?>
