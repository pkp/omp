<?php

/**
 * @file pages/login/LoginHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LoginHandler
 * @ingroup pages_login
 *
 * @brief Handle login/logout requests.
 */

import('lib.pkp.pages.login.PKPLoginHandler');

class LoginHandler extends PKPLoginHandler {
	/**
	 * Get the log in URL.
	 * @param $request PKPRequest
	 */
	function _getLoginUrl($request) {
		return $request->url(null, 'login', 'signIn');
	}

	/**
	 * @copydoc PKPLoginhandler::_setMailFrom
	 */
	function _setMailFrom($request, $mail, $site) {
		$press = $request->getPress();

		// Set the sender based on the current context
		if ($press && $press->getSetting('supportEmail')) {
			$mail->setReplyTo($press->getSetting('supportEmail'), $press->getSetting('supportName'));
		} else {
			$mail->setReplyTo($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
		}
	}

	/**
	 * After a login has completed, direct the user somewhere.
	 * @param $request PKPRequest
	 */
	function _redirectAfterLogin($request) {
		$press = $this->getTargetContext($request);
		// If there's a press, send them to the dashboard after login.
		if ($press && $request->getUserVar('source') == '' && array_intersect(
			array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_ASSISTANT),
			(array) $this->getAuthorizedContextObject(ASSOC_TYPE_USER_ROLES)
		)) {
			return $request->redirect($press->getPath(), 'dashboard');
		}
		// Fall back on the parent otherwise.
		return parent::_redirectAfterLogin($request);
	}

	/**
	 * Configure the template for display.
	 */
	function setupTemplate($request) {
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER);
		parent::setupTemplate($request);
	}
}

?>
