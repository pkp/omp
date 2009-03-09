<?php

/**
 * @file EmailHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for user emails.
 */

// $Id$


class EmailHandler extends UserHandler {
	function email($args) {
		parent::validate();

		parent::setupTemplate(true);

		$templateMgr = &TemplateManager::getManager();

		$userDao = &DAORegistry::getDAO('UserDAO');

		$press = &Request::getPress();
		$user = &Request::getUser();

		// See if this is the Editor or Manager and an email template has been chosen
		$template = Request::getUserVar('template');
		if (	!$press || empty($template) || (
			!Validation::isPressManager($press->getId()) &&
			!Validation::isEditor($press->getId()) &&
			!Validation::isSectionEditor($press->getId())
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
			$roles =& $roleDao->getRolesByUserId($user->getUserId(), $press->getId());
			foreach ($roles as $role) {
				if (in_array($role->getRoleId(), $unlimitedEmailRoles)) $canSendUnlimitedEmails = true;
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
		if ($articleId = Request::getUserVar('articleId')) {
			// This message is in reference to an article.
			// Determine whether the current user has access
			// to the article in some form, and if so, use an
			// ArticleMailTemplate.
			$articleDao =& DAORegistry::getDAO('ArticleDAO');

			$article =& $articleDao->getArticle($articleId);
			$hasAccess = false;

			// First, conditions where access is OK.
			// 1. User is submitter
			if ($article && $article->getUserId() == $user->getUserId()) $hasAccess = true;
			// 2. User is section editor of article or full editor
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getEditAssignmentsByArticleId($articleId);
			while ($editAssignment =& $editAssignments->next()) {
				if ($editAssignment->getEditorId() === $user->getUserId()) $hasAccess = true;
			}
			if (Validation::isEditor($press->getId())) $hasAccess = true;

			// 3. User is reviewer
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			foreach ($reviewAssignmentDao->getReviewAssignmentsByArticleId($articleId) as $reviewAssignment) {
				if ($reviewAssignment->getReviewerId() === $user->getUserId()) $hasAccess = true;
			}
			// 4. User is copyeditor
			$copyAssignmentDao =& DAORegistry::getDAO('CopyAssignmentDAO');
			$copyAssignment =& $copyAssignmentDao->getCopyAssignmentByArticleId($articleId);
			if ($copyAssignment && $copyAssignment->getCopyeditorId() === $user->getUserId()) $hasAccess = true;
			// 5. User is layout editor
			$layoutAssignmentDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
			$layoutAssignment =& $layoutAssignmentDao->getLayoutAssignmentByArticleId($articleId);
			if ($layoutAssignment && $layoutAssignment->getEditorId() === $user->getUserId()) $hasAccess = true;
			// 6. User is proofreader
			$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
			$proofAssignment =& $proofAssignmentDao->getProofAssignmentByArticleId($articleId);
			if ($proofAssignment && $proofAssignment->getProofreaderId() === $user->getUserId()) $hasAccess = true;

			// Last, "deal-breakers" -- access is not allowed.
			if (!$article || ($article && $article->getPressId() !== $press->getId())) $hasAccess = false;

			if ($hasAccess) {
				import('mail.ArticleMailTemplate');
				$email =& new ArticleMailTemplate($articleDao->getArticle($articleId, $template));
			}
		}

		if ($email === null) {
			import('mail.MailTemplate');
			$email = &new MailTemplate($template);
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
			$userDao->updateUser($user);
			Request::redirectUrl($redirectUrl);
		} else {
			$email->displayEditForm(Request::url(null, null, 'email'), array('redirectUrl' => Request::getUserVar('redirectUrl'), 'articleId' => $articleId), null, array('disableSkipButton' => true, 'articleId' => $articleId));
		}
	}
}

?>
