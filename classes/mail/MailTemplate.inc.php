<?php

/**
 * @file classes/mail/MailTemplate.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MailTemplate
 * @ingroup mail
 *
 * @brief Subclass of PKPMailTemplate for mailing a template email.
 */


import('lib.pkp.classes.mail.PKPMailTemplate');

class MailTemplate extends PKPMailTemplate {
	/**
	 * Constructor.
	 * @param $emailKey string unique identifier for the template
	 * @param $locale string locale of the template
	 * @param $enableAttachments boolean optional Whether or not to enable monograph attachments in the template
	 * @param $press object optional The press this message relates to
	 * @param $includeSignature boolean optional Whether or not to include the Press signature
	 */
	function MailTemplate($emailKey = null, $locale = null, $enableAttachments = null, $press = null, $includeSignature = true) {
		parent::PKPMailTemplate($emailKey, $locale, $enableAttachments, $press, $includeSignature);
	}

	/**
	 * Assigns values to e-mail parameters.
	 * @param $paramArray array
	 * @return void
	 */
	function assignParams($paramArray = array()) {
		// Add commonly-used variables to the list
		if (isset($this->press)) {
			// FIXME Include affiliation, title, etc. in signature?
			$paramArray['pressName'] = $this->press->getLocalizedName();
		}
		if (!isset($paramArray['pressUrl'])) $paramArray['pressUrl'] = Request::url(Request::getRequestedPressPath());

		return parent::assignParams($paramArray);
	}
}

?>
