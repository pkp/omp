<?php

/**
 * @file EmailHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user emails.
 */

// $Id$

import('pages.user.UserHandler');

class EmailHandler extends UserHandler {
	/**
	 * Constructor
	 */
	function EmailHandler() {
		parent::UserHandler();
	}

	/**
	 * Determine whether the current user has access to the monograph in some form
	 * @param $monographId int
	 * @return boolean
	 */
	function _monographAccessChecks($monographId, $userId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$monograph =& $monographDao->getMonograph($monographId);

		// First, conditions where access is OK.
		// 1. User is submitter
		if ($monograph && $monograph->getUserId() == $userId) return true;
		// 2. User is series editor of monograph or full editor
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupId = $userGroupDao->getByRoleId($monograph->getPressId(), ROLE_ID_EDITOR);
		$editAssignments =& $editAssignmentDao->getByMonographId($monographId, null, $userGroupId);
		while ($editAssignment =& $editAssignments->next()) {
			if ($editAssignment->getEditorId() === $userId) return true;
		}
		if (Validation::isEditor($press->getId())) return true;

		// 3. User is reviewer
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		foreach ($reviewAssignmentDao->getBySubmissionId($monographId) as $reviewAssignment) {
			if ($reviewAssignment->getReviewerId() === $userId) return true;
		}
		// 4. User is a designer
		$designerAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		foreach ($designerAssignmentDao->getByMonographId($monographId) as $designAssignment) {
			if ($designAssignment->getDesignerId() === $userId) return true;
		}
		// 5. User is copyeditor
		$copyedSignoff =& $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monographId);
		if ($copyedSignoff && $copyedSignoff->getUserId() === $userId) return true;
		// 6. User is production editor
		$productionSignoff =& $signoffDao->getBySymbolic('SIGNOFF_PRODUCTION', ASSOC_TYPE_MONOGRAPH, $monographId);
		if ($productionSignoff && $productionSignoff->getUserId() === $userId) return true;
		// 7. User is proofreader
		$proofSignoff =& $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_MONOGRAPH, $monographId);
		if ($proofSignoff && $proofSignoff->getUserId() === $userId) return true;
		// 8. User is indexer
		$indexSignoff =& $signoffDao->getBySymbolic('SIGNOFF_INDEXING', ASSOC_TYPE_MONOGRAPH, $monographId);
		if ($indexSignoff && $indexSignoff->getUserId() === $userId) return true;

		// 9. User is director
		if (Validation::isDirector($press->getId())) return true;

		// Last, "deal-breakers" -- access is not allowed.
		if (!$monograph || ($monograph && $monograph->getPressId() !== $press->getId())) return false;

		return false;
	}

	function email($args) {
		$this->validate();

		$this->setupTemplate(true);

		$templateMgr =& TemplateManager::getManager();

		$userDao =& DAORegistry::getDAO('UserDAO');

		$press =& Request::getPress();
		$user =& Request::getUser();

		// See if this is the Editor or Manager and an email template has been chosen
		$template = Request::getUserVar('template');
		if (	!$press || empty($template) || (
			!Validation::isPressManager($press->getId()) &&
			!Validation::isEditor($press->getId()) &&
			!Validation::isSeriesEditor($press->getId())
		)) {
			$template = null;
		}

		// Determine whether or not this account is subject to
		// email sending restrictions.
		$canSendUnlimitedEmails = Validation::isSiteAdmin();
		$unlimitedEmailRoles = array(
			ROLE_ID_PRESS_MANAGER
		);
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		if ($press) {
			$roles =& $roleDao->getByUserId($user->getId(), $press->getId());
			foreach ($roles->toArray() as $role) {
				if (in_array($role->getId(), $unlimitedEmailRoles)) $canSendUnlimitedEmails = true;
			}
		}

		// Check when this user last sent an email, and if it's too
		// recent, make them wait.
		if (!$canSendUnlimitedEmails) {
			$dateLastEmail = $user->getDateLastEmail();
			if ($dateLastEmail && strtotime($dateLastEmail) + ((int) Config::getVar('email', 'time_between_emails')) > strtotime(Core::getCurrentDate())) {
				$templateMgr->assign('pageTitle', 'email.compose');
				$templateMgr->assign('message', 'email.compose.tooSoon');
				$templateMgr->assign('backLink', 'javascript:history.back()');
				$templateMgr->assign('backLinkLabel', 'email.compose');
				return $templateMgr->display('common/message.tpl');
			}
		}

		$email = null;
		if ($monographId = Request::getUserVar('monographId')) {
			$monographDao =& DAORegistry::getDAO('MonographDAO');
			// This message is in reference to a monograph.
			// Determine whether the current user has access
			// to the monograph in some form, and if so, use an
			// MonographMailTemplate.
			$hasAccess = $this->_monographAccessChecks($monographId, $user->getId());

			if ($hasAccess) {
				import('classes.mail.MonographMailTemplate');
				$email = new MonographMailTemplate($monographDao->getMonograph($monographId, $template));
			}
		}

		if ($email === null) {
			import('classes.mail.MailTemplate');
			$email = new MailTemplate($template);
		}

		if (Request::getUserVar('send') && !$email->hasErrors()) {
			$recipients = $email->getRecipients();
			$ccs = $email->getCcs();
			$bccs = $email->getBccs();

			// Make sure there aren't too many recipients (to
			// prevent use as a spam relay)
			$recipientCount = 0;
			if (is_array($recipients)) $recipientCount += count($recipients);
			if (is_array($ccs)) $recipientCount += count($ccs);
			if (is_array($bccs)) $recipientCount += count($bccs);

			if (!$canSendUnlimitedEmails && $recipientCount > ((int) Config::getVar('email', 'max_recipients'))) {
				$templateMgr->assign('pageTitle', 'email.compose');
				$templateMgr->assign('message', 'email.compose.tooManyRecipients');
				$templateMgr->assign('backLink', 'javascript:history.back()');
				$templateMgr->assign('backLinkLabel', 'email.compose');
				return $templateMgr->display('common/message.tpl');
			}
			$email->send();
			$redirectUrl = Request::getUserVar('redirectUrl');
			if (empty($redirectUrl)) $redirectUrl = Request::url(null, 'user');
			$user->setDateLastEmail(Core::getCurrentDate());
			$userDao->updateObject($user);
			Request::redirectUrl($redirectUrl);
		} else {
			$email->displayEditForm(Request::url(null, null, 'email'), array('redirectUrl' => Request::getUserVar('redirectUrl'), 'monographId' => $monographId), null, array('disableSkipButton' => true, 'monographId' => $monographId));
		}
	}
}

?>
