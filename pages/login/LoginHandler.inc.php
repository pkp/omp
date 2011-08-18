<?php

/**
 * @file LoginHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function signInAsUser($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->addCheck(new HandlerValidatorRoles($this, true, null, null, array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER)));
		$this->validate();

		if (isset($args[0]) && !empty($args[0])) {
			$userId = (int)$args[0];
			$user =& $request->getUser();

			if (!Validation::canAdminister($userId, $user->getId())) {
				$this->setupTemplate();
				// We don't have administrative rights
				// over this user. Display an error.
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign('pageTitle', 'manager.people');
				$templateMgr->assign('errorMsg', 'manager.people.noAdministrativeRights');
				$templateMgr->assign('backLink', Request::url(null, null, 'people', 'all'));
				$templateMgr->assign('backLinkLabel', 'manager.people.allUsers');
				return $templateMgr->display('common/error.tpl');
			}

			$userDao =& DAORegistry::getDAO('UserDAO');
			$newUser =& $userDao->getUser($userId);
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
		$request->redirect(null, Request::getRequestedPage());
	}

	/**
	 * Restore original user account after signing in as a user.
	 */
	function signOutAsUser() {
		$this->validate();

		$session =& Request::getSession();
		$signedInAs = $session->getSessionVar('signedInAs');

		if (isset($signedInAs) && !empty($signedInAs)) {
			$signedInAs = (int)$signedInAs;

			$userDao =& DAORegistry::getDAO('UserDAO');
			$oldUser =& $userDao->getUser($signedInAs);

			$session->unsetSessionVar('signedInAs');

			if (isset($oldUser)) {
				$session->setSessionVar('userId', $signedInAs);
				$session->setUserId($signedInAs);
				$session->setSessionVar('username', $oldUser->getUsername());
			}
		}

		Request::redirect(null, 'user');
	}

	/**
	 * Get the log in URL.
	 */
	function _getLoginUrl() {
		return Request::url(null, 'login', 'signIn');
	}
	
	/**
	 * Helper Function - set mail from address
	 * @param MailTemplate $mail 
	 */
	function _setMailFrom(&$mail) {
		$site =& Request::getSite();
		$press =& Request::getPress();
		
		// Set the sender based on the current context
		if ($press && $press->getSetting('supportEmail')) {
			$mail->setFrom($press->getSetting('supportEmail'), $press->getSetting('supportName'));
		} else { 
			$mail->setFrom($site->getLocalizedContactEmail(), $site->getLocalizedContactName());
		}
	}

	/**
	 * Configure the template for display.
	 */
	function setupTemplate() {
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER));
		parent::setupTemplate();
	}
}

?>
