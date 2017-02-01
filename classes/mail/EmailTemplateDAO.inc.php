<?php

/**
 * @file classes/mail/EmailTemplateDAO.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailTemplateDAO
 * @ingroup mail
 * @see EmailTemplate
 *
 * @brief Operations for retrieving and modifying Email Template objects.
 */

import('lib.pkp.classes.mail.PKPEmailTemplateDAO');
import('lib.pkp.classes.mail.EmailTemplate');

class EmailTemplateDAO extends PKPEmailTemplateDAO {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Retrieve a base email template by key.
	 * @param $emailKey string Email key
	 * @param $pressId int Press ID
	 * @return BaseEmailTemplate Email template
	 */
	function getBaseEmailTemplate($emailKey, $pressId) {
		return parent::getBaseEmailTemplate($emailKey, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Retrieve localized email template by key.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return LocaleEmailTemplate
	 */
	function getLocaleEmailTemplate($emailKey, $pressId) {
		return parent::getLocaleEmailTemplate($emailKey, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Retrieve an email template by key.
	 * @param $emailKey string
	 * @param $locale string
	 * @param $pressId int
	 * @return EmailTemplate
	 */
	function getEmailTemplate($emailKey, $locale, $pressId) {
		return parent::getEmailTemplate($emailKey, $locale, ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Delete an email template by key.
	 * @param $emailKey string
	 * @param $pressId int optional
	 */
	function deleteEmailTemplateByKey($emailKey, $pressId = null) {
		return parent::deleteEmailTemplateByKey($emailKey, $pressId !== null?ASSOC_TYPE_PRESS:null, $pressId);
	}

	/**
	 * Retrieve all email templates.
	 * @param $locale string
	 * @param $pressId int
	 * @param $rangeInfo object optional
	 * @return array Email templates
	 */
	function getEmailTemplates($locale, $pressId, $rangeInfo = null) {
		return parent::getEmailTemplates($locale, ASSOC_TYPE_PRESS, $pressId, $rangeInfo);
	}

	/**
	 * Delete all email templates for a specific press.
	 * @param $pressId int
	 */
	function deleteEmailTemplatesByContext($pressId) {
		return parent::deleteEmailTemplatesByAssoc(ASSOC_TYPE_PRESS, $pressId);
	}

	/**
	 * Check if a template exists with the given email key for a press.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return boolean
	 */
	function templateExistsByKey($emailKey, $pressId = null) {
		return parent::templateExistsByKey($emailKey, $pressId?ASSOC_TYPE_PRESS:null, $pressId);
	}

	/**
	 * Check if a custom template exists with the given email key for a press.
	 * @param $emailKey string
	 * @param $pressId int
	 * @return boolean
	 */
	function customTemplateExistsByKey($emailKey, $pressId) {
		return parent::customTemplateExistsByKey($emailKey, ASSOC_TYPE_PRESS, $pressId);
	}
}

?>
