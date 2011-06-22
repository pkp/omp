<?php

/**
 * @file controllers/grid/settings/preparedEmails/form/PreparedEmailForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PreparedEmailForm
 * @ingroup controllers_modals_preparedEmails_form
 * @see EmailTemplateDAO
 *
 * @brief Form for creating and modifying prepared emails.
 */


import('lib.pkp.classes.form.Form');

class PreparedEmailForm extends Form {

	/** The key of the email template being edited */
	var $_emailKey;

	/** The conference of the email template being edited */
	var $_press;

	/**
	 * Constructor.
	 * @param $emailKey string
	 */
	function PreparedEmailForm($emailKey = null, &$press) {
		parent::Form('controllers/grid/settings/preparedEmails/form/emailTemplateForm.tpl');

		$this->_press =& $press;
		$this->setEmailKey($emailKey);

		// Validation checks for this form
		$this->addCheck(new FormValidatorArray($this, 'subject', 'required', 'manager.emails.form.subjectRequired'));
		$this->addCheck(new FormValidatorArray($this, 'body', 'required', 'manager.emails.form.bodyRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Set the email key
	 * @param $emailKey string
	 */
	function setEmailKey($emailKey) {
		$this->_emailKey = $emailKey;
	}

	/**
	 * Get the email key
	 * @return string
	 */
	function getEmailKey() {
		return $this->_emailKey;
	}

	/**
	 * Get the press
	 * @return Press
	 */
	function &getPress() {
		return $this->_press;
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$request) {
		$press =& $this->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->getEmailKey(), $press->getId());

		if ($emailTemplate) {
			$subject = array();
			$body = array();
			foreach ($emailTemplate->getLocales() as $locale) {
				$subject[$locale] = $emailTemplate->getSubject($locale);
				$body[$locale] = $emailTemplate->getBody($locale);
			}

			$this->_data = array(
				'emailKey' => $emailTemplate->getEmailKey(),
				'subject' => $subject,
				'body' => $body,
				'description' => $emailTemplate->getDescription(Locale::getLocale())
			);

		} else {
			$this->setData('isNewTemplate', true);
		}

		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_MANAGER));
		$this->setData('supportedLocales', $press->getSupportedLocaleNames());
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('subject', 'body', 'description'));

		$press =& $this->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->getEmailKey(), $press->getId());
		if (!$emailTemplate) $this->setData('isNewTemplate', true);
	}

	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('subject', 'body');
	}

	/**
	 * Save email template.
	 */
	function execute() {
		$press =& $this->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplate =& $emailTemplateDao->getLocaleEmailTemplate($this->getEmailKey(), $press->getId());

		if (!$emailTemplate) {
			$emailTemplate = new LocaleEmailTemplate();
			$emailTemplate->setCustomTemplate(true);
			$emailTemplate->setCanDisable(false);
			$emailTemplate->setEnabled(true);
			$emailTemplate->setEmailKey($this->getEmailKey());
		}

		$emailTemplate->setAssocType(ASSOC_TYPE_PRESS);
		$emailTemplate->setAssocId($press->getId());

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
