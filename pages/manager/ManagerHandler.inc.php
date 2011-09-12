<?php

/**
 * @file pages/manager/ManagerHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManagerHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for press management functions.
 */


import('classes.handler.Handler');

class ManagerHandler extends Handler {
	/**
	 * Constructor
	 */
	function ManagerHandler() {
		parent::Handler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array(
				'email',
				'index',
				'settings'
			)
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display press management index page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->setupTemplate($request);

		$press =& $request->getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$announcementsEnabled = $pressSettingsDao->getSetting($press->getId(), 'enableAnnouncements');
		$customSignoffInternal = $pressSettingsDao->getSetting($press->getId(), 'useCustomInternalReviewSignoff');
		$customSignoffExternal = $pressSettingsDao->getSetting($press->getId(), 'useCustomExternalReviewSignoff');

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('customSingoffEnabled', $customSignoffInternal || $customSignoffExternal );

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroups =& $userGroupDao->getByContextId($press->getId());
		$templateMgr->assign_by_ref('userGroups', $userGroups);

		$session =& $request->getSession();
		$session->unsetSessionVar('enrolmentReferrer');

		$templateMgr->assign('announcementsEnabled', $announcementsEnabled);
		$templateMgr->assign('helpTopicId','press.index');
		$templateMgr->display('manager/index.tpl');
	}

	/**
	 * Send an email to a user or group of users.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function email($args, &$request) {
		$this->setupTemplate($request, true);
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.users.emailUsers');

		$userDao =& DAORegistry::getDAO('UserDAO');

		$site =& $request->getSite();
		$press =& $request->getPress();
		$user =& $request->getUser();

		import('classes.mail.MailTemplate');
		$email = new MailTemplate($request->getUserVar('template'), $request->getUserVar('locale'));

		if ($request->getUserVar('send') && !$email->hasErrors()) {
			$email->send();
			$request->redirect(null, $request->getRequestedPage());
		} else {
			$email->assignParams(); // FIXME Forces default parameters to be assigned (should do this automatically in MailTemplate?)
			if (!$request->getUserVar('continued')) {
				if (($groupId = $request->getUserVar('toGroup')) != '') {
					// Special case for emailing entire groups:
					// Check for a group ID and add recipients.
					$groupDao =& DAORegistry::getDAO('GroupDAO');
					$group =& $groupDao->getById($groupId);
					if ($group && $group->getPressId() == $press->getId()) {
						$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');
						$memberships =& $groupMembershipDao->getMemberships($group->getId());
						$memberships =& $memberships->toArray();
						foreach ($memberships as $membership) {
							$user =& $membership->getUser();
							$email->addRecipient($user->getEmail(), $user->getFullName());
						}
					}
				}
				if (count($email->getRecipients())==0) $email->addRecipient($user->getEmail(), $user->getFullName());
			}
			$email->displayEditForm(
				$request->url(null, null, 'email'),
				array(),
				'manager/people/email.tpl'
			);
		}
	}

	/**
	 * Setup common template variables.
	 * @param $request PKPRequest
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();
		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER, LOCALE_COMPONENT_OMP_MANAGER));

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy',
			$subclass ? array(array($request->url(null, 'user'), 'navigation.user'), array($request->url(null, 'manager'), 'manager.pressManagement'))
				: array(array($request->url(null, 'user'), 'navigation.user'))
		);
	}
}

?>
