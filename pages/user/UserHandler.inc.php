<?php

/**
 * @file pages/user/UserHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user functions.
 */


import('classes.handler.Handler');

class UserHandler extends Handler {
	/**
	 * Constructor
	 */
	function UserHandler() {
		parent::Handler();
	}

	/**
	 * Display user index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->display('user/index.tpl');
	}

	/**
	 * Change the locale for the current user.
	 * @param $args array first parameter is the new locale
	 */
	function setLocale($args, &$request) {
		$setLocale = isset($args[0]) ? $args[0] : null;

		$site =& $request->getSite();
		$press =& $request->getPress();
		if ($press != null) {
			$pressSupportedLocales = $press->getSetting('supportedLocales');
			if (!is_array($pressSupportedLocales)) {
				$pressSupportedLocales = array();
			}
		}

		if (AppLocale::isLocaleValid($setLocale) && (!isset($pressSupportedLocales) || in_array($setLocale, $pressSupportedLocales)) && in_array($setLocale, $site->getSupportedLocales())) {
			$session =& $request->getSession();
			$session->setSessionVar('currentLocale', $setLocale);
		}

		if(isset($_SERVER['HTTP_REFERER'])) {
			$request->redirectUrl($_SERVER['HTTP_REFERER']);
		}

		$source = $request->getUserVar('source');
		if (isset($source) && !empty($source)) {
			$request->redirectUrl(
				$request->getProtocol() . '://' . $request->getServerHost() . $source,
				false
			);
		}

		$request->redirect(null, 'index');
	}

	/**
	 * Become a given role.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function become($args, &$request) {
		parent::validate(true);

		$press =& $request->getPress();
		$user =& $request->getUser();

		switch (array_shift($args)) {
			case 'author':
				$roleId = ROLE_ID_AUTHOR;
				$setting = 'allowRegAuthor';
				$deniedKey = 'user.noRoles.submitMonographRegClosed';
				break;
			case 'reviewer':
				$roleId = ROLE_ID_REVIEWER;
				$setting = 'allowRegReviewer';
				$deniedKey = 'user.noRoles.regReviewerClosed';
				break;
			default:
				$request->redirect(null, null, 'index');
		}

		if ($press->getSetting($setting)) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), $roleId);
			$userGroupDao->assignUserToGroup($user->getId(), $userGroup->getId());
			$request->redirectUrl($request->getUserVar('source'));
		} else {
			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign('message', $deniedKey);
			return $templateMgr->display('common/message.tpl');
		}
	}

	/**
	 * Display an authorization denied message.
	 * @param $args array
	 * @param $request Request
	 */
	function authorizationDenied($args, &$request) {
		$this->validate(true);

		// Get message with sanity check (for XSS or phishing)
		$authorizationMessage = $request->getUserVar('message');
		if (!preg_match('/^[a-zA-Z0-9.]+$/', $authorizationMessage)) {
			fatalError('Invalid locale key for auth message.');
		}

		$this->setupTemplate($request, true);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_USER);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('message', $authorizationMessage);
		return $templateMgr->display('common/message.tpl');
	}

	/**
	 * Validate that user is logged in.
	 * Redirects to login form if not logged in.
	 * @param $loginCheck boolean check if user is logged in
	 */
	function validate($loginCheck = true) {
		parent::validate();
		if ($loginCheck && !Validation::isLoggedIn()) {
			Validation::redirectLogin();
		}
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();
		$templateMgr =& TemplateManager::getManager();
		if ($subclass) {
			$templateMgr->assign('pageHierarchy', array(array($request->url(null, 'user'), 'navigation.user')));
		}
	}

	//
	// Captcha
	//

	/**
	 * View a CAPTCHA test.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewCaptcha($args, &$request) {
		$captchaId = (int) array_shift($args);
		import('lib.pkp.classes.captcha.CaptchaManager');
		$captchaManager = new CaptchaManager();
		if ($captchaManager->isEnabled()) {
			$captchaDao =& DAORegistry::getDAO('CaptchaDAO');
			$captcha =& $captchaDao->getCaptcha($captchaId);
			if ($captcha) {
				$captchaManager->generateImage($captcha);
				exit();
			}
		}
		$request->redirect(null, 'user');
	}

	/**
	 * View the public user profile for a user, specified by user ID,
	 * if that user should be exposed for public view.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function viewPublicProfile($args, &$request) {
		$this->validate(false);
		$templateMgr =& TemplateManager::getManager();
		$userId = (int) array_shift($args);

		$accountIsVisible = false;

		// Ensure that the user's profile info should be exposed:

		$commentDao =& DAORegistry::getDAO('CommentDAO');
		if ($commentDao->attributedCommentsExistForUser($userId)) {
			// At least one comment is attributed to the user
			$accountIsVisible = true;
		}

		if (!$accountIsVisible) $request->redirect(null, 'index');

		$userDao =& DAORegistry::getDAO('UserDAO');
		$user =& $userDao->getUser($userId);

		$templateMgr->assign_by_ref('user', $user);
		$templateMgr->display('user/publicProfile.tpl');
	}
}

?>
