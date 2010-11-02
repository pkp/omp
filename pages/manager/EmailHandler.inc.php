<?php

/**
 * @file EmailHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for email management functions.
 */


import('pages.manager.ManagerHandler');

class EmailHandler extends ManagerHandler {

	/**
	 * Constructor
	 */
	function EmailHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array('createEmail', 'deleteCustomEmail', 'disableEmail', 'editEmail', 'emails',
				'enableEmail', 'resetAllEmails', 'resetEmail', 'updateEmail'));
	}

	/**
	 * Display a list of the emails within the current press.
	 */
	function emails() {
		$this->setupTemplate(true);

		$rangeInfo = Handler::getRangeInfo('emails');

		$press =& Request::getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplates =& $emailTemplateDao->getEmailTemplates(Locale::getLocale(), $press->getId());

		import('lib.pkp.classes.core.ArrayItemIterator');
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
		$this->setupTemplate(true);

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->append('pageHierarchy', array(Request::url(null, 'manager', 'emails'), 'manager.emails'));

		$emailKey = !isset($args) || empty($args) ? null : $args[0];

		import('classes.manager.form.EmailTemplateForm');

		$emailTemplateForm = new EmailTemplateForm($emailKey, $press);
		$emailTemplateForm->initData();
		$emailTemplateForm->display();
	}

	/**
	 * Save changes to an email.
	 */
	function updateEmail() {
		$this->setupTemplate(true);
		$press =& Request::getPress();

		import('classes.manager.form.EmailTemplateForm');

		$emailKey = Request::getUserVar('emailKey');

		$emailTemplateForm = new EmailTemplateForm($emailKey, $press);
		$emailTemplateForm->readInputData();

		if ($emailTemplateForm->validate()) {
			$emailTemplateForm->execute();
			Request::redirect(null, null, 'emails');

		} else {
			$emailTemplateForm->display();
		}
	}

	/**
	 * Delete a custom email.
	 * @param $args array first parameter is the key of the email to delete
	 */
	function deleteCustomEmail($args) {
		$press =& Request::getPress();
		$emailKey = array_shift($args);

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		if ($emailTemplateDao->customTemplateExistsByKey($emailKey, $press->getId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getId());
		}

		Request::redirect(null, null, 'emails');
	}

	/**
	 * Reset an email to default.
	 * @param $args array first parameter is the key of the email to reset
	 */
	function resetEmail($args) {
		if (isset($args) && !empty($args)) {
			$press =& Request::getPress();

			$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplateDao->deleteEmailTemplateByKey($args[0], $press->getId());
		}

		Request::redirect(null, null, 'emails');
	}

	/**
	 * resets all email templates associated with the press.
	 */
	function resetAllEmails() {
		$press =& Request::getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->deleteEmailTemplatesByPress($press->getId());

		Request::redirect(null, null, 'emails');
	}

	/**
	 * disables an email template.
	 * @param $args array first parameter is the key of the email to disable
	 */
	function disableEmail($args) {
		if (isset($args) && !empty($args)) {
			$press =& Request::getPress();

			$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $press->getId());

			if (isset($emailTemplate)) {
				if ($emailTemplate->getCanDisable()) {
					$emailTemplate->setEnabled(0);

					if ($emailTemplate->getPressId() == null) {
						$emailTemplate->setPressId($press->getId());
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
		if (isset($args) && !empty($args)) {
			$press =& Request::getPress();

			$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($args[0], $press->getId());

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
