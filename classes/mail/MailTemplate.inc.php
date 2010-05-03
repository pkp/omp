<?php

/**
 * @file classes/mail/MailTemplate.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MailTemplate
 * @ingroup mail
 *
 * @brief Subclass of PKPMailTemplate for mailing a template email.
 */

// $Id$


import('lib.pkp.classes.mail.PKPMailTemplate');

class MailTemplate extends PKPMailTemplate {
	/** @var $press object The press this message relates to */
	var $press;

	/**
	 * Constructor.
	 * @param $emailKey string unique identifier for the template
	 * @param $locale string locale of the template
	 * @param $enableAttachments boolean optional Whether or not to enable monograph attachments in the template
	 * @param $press object optional The press this message relates to
	 */
	function MailTemplate($emailKey = null, $locale = null, $enableAttachments = null, $press = null) {
		parent::PKPMailTemplate($emailKey, $locale, $enableAttachments);

		// If a press wasn't specified, use the current request.
		if ($press === null) $press =& Request::getPress();

		if (isset($this->emailKey)) {
			$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
			$emailTemplate =& $emailTemplateDao->getEmailTemplate($this->emailKey, $this->locale, $press == null ? 0 : $press->getId());
		}

		$userSig = '';
		$user =& Request::getUser();
		if ($user) {
			$userSig = $user->getLocalizedSignature();
			if (!empty($userSig)) $userSig = "\n" . $userSig;
		}

		if (isset($emailTemplate) && Request::getUserVar('subject')==null && Request::getUserVar('body')==null) {
			$this->setSubject($emailTemplate->getSubject());
			$this->setBody($emailTemplate->getBody() . $userSig);
			$this->enabled = $emailTemplate->getEnabled();

			if (Request::getUserVar('usePostedAddresses')) {
				$to = Request::getUserVar('to');
				if (is_array($to)) {
					$this->setRecipients($this->processAddresses ($this->getRecipients(), $to));
				}
				$cc = Request::getUserVar('cc');
				if (is_array($cc)) {
					$this->setCcs($this->processAddresses ($this->getCcs(), $cc));
				}
				$bcc = Request::getUserVar('bcc');
				if (is_array($bcc)) {
					$this->setBccs($this->processAddresses ($this->getBccs(), $bcc));
				}
			}
		} else {
			$this->setSubject(Request::getUserVar('subject'));
			$body = Request::getUserVar('body');
			if (empty($body)) $this->setBody($userSig);
			else $this->setBody($body);
			$this->skip = (($tmp = Request::getUserVar('send')) && is_array($tmp) && isset($tmp['skip']));
			$this->enabled = true;

			if (is_array($toEmails = Request::getUserVar('to'))) {
				$this->setRecipients($this->processAddresses ($this->getRecipients(), $toEmails));
			}
			if (is_array($ccEmails = Request::getUserVar('cc'))) {
				$this->setCcs($this->processAddresses ($this->getCcs(), $ccEmails));
			}
			if (is_array($bccEmails = Request::getUserVar('bcc'))) {
				$this->setBccs($this->processAddresses ($this->getBccs(), $bccEmails));
			}
		}

		// Default "From" to user if available, otherwise site/press principal contact
		$user =& Request::getUser();
		if ($user) {
			$this->setFrom($user->getEmail(), $user->getFullName());
		} elseif ($press == null) {
			$site =& Request::getSite();
			$this->setFrom($site->getLocalizedContactEmail(), $site->getLocalizedContactName());

		} else {
			$this->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
		}

		if ($press && !Request::getUserVar('continued')) {
			$this->setSubject('[' . $press->getLocalizedSetting('initials') . '] ' . $this->getSubject());
		}

		$this->press =& $press;
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
			$paramArray['principalContactSignature'] = $this->press->getSetting('contactName');
		} else {
			$site =& Request::getSite();
			$paramArray['principalContactSignature'] = $site->getLocalizedContactName();
		}
		if (!isset($paramArray['pressUrl'])) $paramArray['pressUrl'] = Request::url(Request::getRequestedPressPath());

		return parent::assignParams($paramArray);
	}

	/**
	 * Displays an edit form to customize the email.
	 * @param $formActionUrl string
	 * @param $hiddenFormParams array
	 * @return void
	 */
	function displayEditForm($formActionUrl, $hiddenFormParams = null, $alternateTemplate = null, $additionalParameters = array()) {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('helpTopicId', 'press.managementPages.emails');

		parent::displayEditForm($formActionUrl, $hiddenFormParams, $alternateTemplate, $additionalParameters);
	}

	/**
	 * Send the email.
	 * Aside from calling the parent method, this actually attaches
	 * the persistent attachments if they are used.
	 * @param $clearAttachments boolean Whether to delete attachments after
	 */
	function send($clearAttachments = true) {
		if (isset($this->press)) {
			//If {$templateSignature} exists in the body of the
			// message, replace it with the press signature;
			// otherwise just append it. This is here to
			// accomodate MIME-encoded messages or other cases
			// where the signature cannot just be appended.
			$searchString = '{$templateSignature}';
			if (strstr($this->getBody(), $searchString) === false) {
				$this->setBody($this->getBody() . "\n" . $this->press->getSetting('emailSignature'));
			} else {
				$this->setBody(str_replace($searchString, $this->press->getSetting('emailSignature'), $this->getBody()));
			}

			$envelopeSender = $this->press->getSetting('envelopeSender');
			if (!empty($envelopeSender) && Config::getVar('email', 'allow_envelope_sender')) $this->setEnvelopeSender($envelopeSender);
		}

		return parent::send($clearAttachments);
	}
}

?>
