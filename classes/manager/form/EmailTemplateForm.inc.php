<?php

/**
 * @file classes/manager/form/EmailTemplateForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateForm
 * @ingroup manager_form
 * @see EmailTemplateDAO
 *
 * @brief Form for creating and modifying email templates.
 */



import('lib.pkp.classes.form.Form');

class EmailTemplateForm extends Form {

	/** The key of the email template being edited */
	var $emailKey;

	/** The conference of the email template being edited */
	var $press;

	/**
	 * Constructor.
	 * @param $emailKey string
	 */
	function EmailTemplateForm($emailKey, &$press) {
		parent::Form('manager/emails/emailTemplateForm.tpl');

		$this->press = $press;
		$this->emailKey = $emailKey;

		// Validation checks for this form
		$this->addCheck(new FormValidatorArray($this, 'subject', 'required', 'manager.emails.form.subjectRequired'));
		$this->addCheck(new FormValidatorArray($this, 'body', 'required', 'manager.emails.form.bodyRequired'));
		$this->addCheck(new FormValidator($this, 'emailKey', 'required', 'manager.emails.form.emailKeyRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		$press =& Request::getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getBaseEmailTemplate($this->emailKey, $press->getId());
		$templateMgr->assign('canDisable', $emailTemplate?$emailTemplate->getCanDisable():false);
		$templateMgr->assign('supportedLocales', $press->getSupportedLocaleNames());
		$templateMgr->assign('helpTopicId','press.managementPages.emails');
		parent::display();
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		$press =& Request::getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $press->getId());
		$thisLocale = Locale::getLocale();

		if ($emailTemplate) {
			$subject = array();
			$body = array();
			$description = array();
			foreach ($emailTemplate->getLocales() as $locale) {
				$subject[$locale] = $emailTemplate->getSubject($locale);
				$body[$locale] = $emailTemplate->getBody($locale);
				$description[$locale] = $emailTemplate->getDescription($locale);
			}

			if ($emailTemplate != null) {
				$this->_data = array(
					'emailId' => $emailTemplate->getEmailId(),
					'emailKey' => $emailTemplate->getEmailKey(),
					'subject' => $subject,
					'body' => $body,
					'description' => isset($description[$thisLocale])?$description[$thisLocale]:null,
					'enabled' => $emailTemplate->getEnabled()
				);
			}
		} else {
			$this->_data = array('isNewTemplate' => true);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('emailId', 'subject', 'body', 'enabled', 'pressId', 'emailKey'));

		$pressId = $this->press->getPressId();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $pressId);
		if (!$emailTemplate) $this->_data['isNewTemplate'] = true;
	}

	/**
	 * Save email template.
	 */
	function execute() {
		$press =& Request::getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->emailKey, $press->getId());

		if (!$emailTemplate) {
			$emailTemplate = new LocaleEmailTemplate();
			$emailTemplate->setCustomTemplate(true);
			$emailTemplate->setCanDisable(false);
			$emailTemplate->setEnabled(true);
			$emailTemplate->setEmailKey($this->getData('emailKey'));
		} else {
			$emailTemplate->setEmailId($this->getData('emailId'));
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled($this->getData('enabled'));
			}

		}

		$emailTemplate->setAssocType(ASSOC_TYPE_PRESS);
		$emailTemplate->setAssocId($press->getPressId());

		$supportedLocales = $press->getSupportedLocaleNames();
		if (!empty($supportedLocales)) {
			foreach ($press->getSupportedLocaleNames() as $localeKey => $localeName) {
				$emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
				$emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
			}
		} else {
			$localeKey = Locale::getLocale();
			$emailTemplate->setSubject($localeKey, $this->_data['subject'][$localeKey]);
			$emailTemplate->setBody($localeKey, $this->_data['body'][$localeKey]);
		}

		if ($emailTemplate->getEmailId() != null) {
			$emailTemplateDao->updateLocaleEmailTemplate($emailTemplate);
		} else {
			$emailTemplateDao->insertLocaleEmailTemplate($emailTemplate);
		}
	}
}

?>
