<?php

/**
 * @file EmailHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for email management functions. 
 */

// $Id$


class EmailHandler extends ManagerHandler {

	/**
	 * Display a list of the emails within the current press.
	 */
	function emails() {
		parent::validate();
		parent::setupTemplate(true);

		$rangeInfo = Handler::getRangeInfo('emails');

		$press =& Request::getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplates =& $emailTemplateDao->getEmailTemplates(Locale::getLocale(), $press->getPressId());

		import('core.ArrayItemIterator');
		$emailTemplates =& ArrayItemIterator::fromRangeInfo($emailTemplates, $rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'manager'), 'manager.pressManagement')));
		$templateMgr->assign_by_ref('emailTemplates', $emailTemplates);
		$templateMgr->assign('helpTopicId','press.managementPages.emails');
		$templateMgr->display('manager/emails/emails.tpl');
	}

	function createEmail($args = array()) {
		EmailHandler::editEmail($args);
	}

	/**
	 * Display form to create/edit an email.
	 * @param $args array optional, if set the first parameter is the key of the email template to edit
	 */
	function editEmail($args = array()) {
		parent::validate();
		parent::setupTemplate(true);

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'emails'), 'manager.emails'));

		$emailKey = !isset($args) || empty($args) ? null : $args[0];

		import('manager.form.EmailTemplateForm');

		$emailTemplateForm = &new EmailTemplateForm($emailKey);
		$emailTemplateForm->initData();
		$emailTemplateForm->display();
	}

	/**
	 * Save changes to an email.
	 */
	function updateEmail() {
		parent::validate();

		import('manager.form.EmailTemplateForm');

		$emailKey = Request::getUserVar('emailKey');

		$emailTemplateForm = &new EmailTemplateForm($emailKey);
		$emailTemplateForm->readInputData();

		if ($emailTemplateForm->validate()) {
			$emailTemplateForm->execute();
			Request::redirect(null, null, 'emails');

		} else {
			parent::setupTemplate(true);
			$emailTemplateForm->display();
		}
	}

	/**
	 * Delete a custom email.
	 * @param $args array first parameter is the key of the email to delete
	 */
	function deleteCustomEmail($args) {
		parent::validate();
		$press = &Request::getPress();
		$emailKey = array_shift($args);

		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		if ($emailTemplateDao->customTemplateExistsByKey($emailKey, $press->getPressId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getPressId());
		}

		Request::redirect(null, null, 'emails');
	}

	/**
	 * Reset an email to default.
	 * @param $args array first parameter is the key of the email to reset
	 */
	function resetEmail($args) {
		parent::validate();

		if (isset($args) && !empty($args)) {
			$press = &Request::getPress();

			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplateDao->deleteEmailTemplateByKey($args[0], $press->getPressId());
		}

		Request::redirect(null, null, 'emails');
	}

	/**
	 * resets all email templates associated with the press.
	 */
	function resetAllEmails() {
		parent::validate();

		$press = &Request::getPress();
		$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->deleteEmailTemplatesByPress($press->getPressId());

		Request::redirect(null, null, 'emails');
	}

	/**
	 * disables an email template.
	 * @param $args array first parameter is the key of the email to disable
	 */
	function disableEmail($args) {
		parent::validate();

		if (isset($args) && !empty($args)) {
			$press = &Request::getPress();

			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $press->getPressId());

			if (isset($emailTemplate)) {
				if ($emailTemplate->getCanDisable()) {
					$emailTemplate->setEnabled(0);

					if ($emailTemplate->getPressId() == null) {
						$emailTemplate->setPressId($press->getPressId());
					}

					if ($emailTemplate->getEmailId() != null) {
						$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
					} else {
						$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
					}
				}
			}
		}

		Request::redirect(null, null, 'emails');
	}

	/**
	 * enables an email template.
	 * @param $args array first parameter is the key of the email to enable
	 */
	function enableEmail($args) {
		parent::validate();

		if (isset($args) && !empty($args)) {
			$press = &Request::getPress();

			$emailTemplateDao = &DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $press->getPressId());

			if (isset($emailTemplate)) {
				if ($emailTemplate->getCanDisable()) {
					$emailTemplate->setEnabled(1);

					if ($emailTemplate->getEmailId() != null) {
						$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
					} else {
						$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
					}
				}
			}
		}

		Request::redirect(null, null, 'emails');
	}

}

?>
