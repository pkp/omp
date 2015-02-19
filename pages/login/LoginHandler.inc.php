<?php

/**
 * @file pages/login/LoginHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
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
	 * Sign in as another user.
	 * @param $args array ($userId)
	 * @param $request PKPRequest
	 */
	function signInAsUser($args, $request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN, ROLE_ID_MANAGER)));
		$this->validate();

		if (isset($args[0]) && !empty($args[0])) {
			$userId = (int)$args[0];
			$user = $request->getUser();

			if (!Validation::canAdminister($userId, $user->getId())) {
				$this->setupTemplate($request);
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr = TemplateManager::getManager($request);
				$templateMgr->assign('pageTitle', 'manager.people');
				$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
				$templateMgr->assign('backLink', $request->url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}

			$userDao = DAORegistry::getDAO('UserDAO');
			$newUser = $userDao->getById($userId);
			$session =& $request->getSession();

			// FIXME Support "stack" of signed-in-as user IDs?
			if (isset($newUser) && $session->getUserId() != $newUser->getId()) {
				$session->setSessionVar('signedInAs', $session->getUserId());
				$session->setSessionVar('userId', $userId);
				$session->setUserId($userId);
				$session->setSessionVar('username', $newUser->getUsername());
				$request->redirect(null, 'user');
			}
		}
		$request->redirect(null, $request->getRequestedPage());
	}

	/**
	 * Restore original user account after signing in as a user.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function signOutAsUser($args, $request) {
		$this->validate();

		$session = $request->getSession();
		$signedInAs = $session->getSessionVar('signedInAs');

		if (isset($signedInAs) && !empty($signedInAs)) {
			$signedInAs = (int)$signedInAs;

			$userDao = DAORegistry::getDAO('UserDAO');
			$oldUser = $userDao->getById($signedInAs);

			$session->unsetSessionVar('signedInAs');

			if (isset($oldUser)) {
				$session->setSessionVar('userId', $signedInAs);
				$session->setUserId($signedInAs);
				$session->setSessionVar('username', $oldUser->getUsername());
			}
		}

		$request->redirect(null, 'user');
	}

	/**
	 * Get the log in URL.
	 * @param $request PKPRequest
	 */
	function _getLoginUrl($request) {
		return $request->url(null, 'login', 'signIn');
	}

	/**
	 * Helper Function - set mail from address
	 * @param $request PKPRequest
	 * @param MailTemplate $mail
	 */
	function _setMailFrom($request, &$mail) {
		$site = $request->getSite();
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
