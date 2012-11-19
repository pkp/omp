<?php

/**
 * @file pages/user/UserHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request, &$args) {
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_GRID);
		parent::initialize($request, $args);
	}

	/**
	 * Index page; redirect to profile
	 */
	function index($args, &$request) {
		$request->redirect(null, null, 'profile');
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
			$templateMgr =& TemplateManager::getManager($request);
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
		parent::validate();

		if (!Validation::isLoggedIn()) {
			Validation::redirectLogin();
		}

		// Get message with sanity check (for XSS or phishing)
		$authorizationMessage = $request->getUserVar('message');
		if (!preg_match('/^[a-zA-Z0-9.]+$/', $authorizationMessage)) {
			fatalError('Invalid locale key for auth message.');
		}

		$this->setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_USER);
		$templateMgr =& TemplateManager::getManager($request);
		$templateMgr->assign('message', $authorizationMessage);
		return $templateMgr->display('common/message.tpl');
	}
}

?>
