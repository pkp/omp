<?php

/**
 * @file controllers/grid/EmailGridHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailGridHandler
 * @ingroup controllers_grid
 *
 * @brief Handle grid requests for email translation.
 */

import('plugins.generic.translator.controllers.grid.BaseLocaleFileGridHandler');
import('plugins.generic.translator.controllers.grid.EmailGridRow');

class EmailGridHandler extends BaseLocaleFileGridHandler {

	/**
	 * Constructor
	 */
	function EmailGridHandler() {
		parent::BaseLocaleFileGridHandler();
	}


	//
	// Overridden template methods
	//
	/**
	 * @copydoc Gridhandler::initialize()
	 */
	function initialize($request, $args = null) {
		parent::initialize($request);

		// Set the grid details.
		$this->setTitle('plugins.generic.translator.emails');
		$this->setInstructions('plugins.generic.translator.emailDescription');
		$emails = self::_getEmailTemplates($this->locale);
		ksort($emails);
		$this->setGridDataElements($emails);
	}

	/**
	 * Add columns for this grid.
	 */
	function addColumns() {
		// Key column
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);
		$this->addColumn(new GridColumn(
			'id',
			'manager.emails.emailTemplate'
		));

		// Subject
		$this->addColumn(new GridColumn(
			'subject',
			'common.subject',
			null,
			'controllers/grid/gridCell.tpl', // Default null not supported in OMP 1.1
			null,
			array('width' => 60, 'alignment' => COLUMN_ALIGNMENT_LEFT)
		));
	}

	/**
	 * @copydoc Gridhandler::getRowInstance()
	 */
	function getRowInstance() {
		return new EmailGridRow($this->tabsSelector, $this->locale);
	}

	//
	// Public Grid Actions
	//
	/**
	 * Display the edit form.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function edit($args, $request) {
		$emailData = $referenceEmailData = null; // Avoid scrutinizer warning
		$emailKey = $this->_getEmailData($request, $emailData, $referenceEmailData);

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'locale' => $this->locale,
			'emailKey' => $emailKey,
			'referenceSubject' => isset($referenceEmailData['subject'])?$referenceEmailData['subject']:'',
			'referenceBody' => isset($referenceEmailData['body'])?$referenceEmailData['body']:'',
			'referenceDescription' => isset($referenceEmailData['description'])?$referenceEmailData['description']:'',
			'emailSubject' => isset($emailData['subject'])?$emailData['subject']:'',
			'emailBody' => isset($emailData['body'])?$emailData['body']:'',
			'emailDescription' => isset($emailData['description'])?$emailData['description']:'',
		));
		return $templateMgr->fetchJson(self::$plugin->getTemplatePath() . 'editEmail.tpl');
	}

	/**
	 * Display the grid's containing page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function save($args, $request) {
		$emailData = $referenceEmailData = null; // Avoid scrutinizer warning
		$emailKey = $this->_getEmailData($request, $emailData, $referenceEmailData);

		$notificationManager = new NotificationManager();
		$user = $request->getUser();

		$targetFilename = str_replace(MASTER_LOCALE, $this->locale, $referenceEmailData['templateDataFile']); // FIXME: Ugly.

		if (!isset($emailData[$emailKey]['templateDataFile'])) {
			// If it's a reference email but not a translated one,
			// create a blank file. FIXME: This is ugly.
			if (!file_exists($targetFilename)) {
				$dir = dirname($targetFilename);
				if (!file_exists($dir)) mkdir($dir);
				file_put_contents($targetFilename, '<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE email_texts SYSTEM "../../../../../lib/pkp/dtd/emailTemplateData.dtd">
<!--
  * emailTemplateData.xml
  *
  * Copyright (c) 2003-2014 John Willinsky
  * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
  *
  * Localized email templates XML file.
  -->
<email_texts locale="' . $this->locale . '">
</email_texts>');
			}
		}

		import('lib.pkp.classes.file.EditableEmailFile');
		$file = new EditableEmailFile($this->locale, $targetFilename);

		$subject = $this->correctCr($request->getUserVar('emailSubject'));
		$body = $this->correctCr($request->getUserVar('emailBody'));
		$description = $this->correctCr($request->getUserVar('emailDescription'));

		$message = new JSONMessage(true);
		if (!$file->update($emailKey, $subject, $body, $description)) {
			if (!$file->insert($emailKey, $subject, $body, $description)) {
				// Some kind of error occurred (probably garbled formatting)
				$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.translator.errorEditingFile', array('filename' => $targetFilename))));
				return $message->getString();
			}
		}

		if ($file->write()) {
			$notificationManager->createTrivialNotification($user->getId());
		} else {
			// Could not write the file
			$notificationManager->createTrivialNotification($user->getId(), NOTIFICATION_TYPE_ERROR, array('contents' => __('plugins.generic.translator.couldNotWriteFile', array('filename' => $targetFilename))));
		}
		return $message->getString();
	}

	/**
	 * Get the (validated) email key for the current request.
	 * @param $request PKPRequest
	 * @param $emailData Reference to receive email data, if found
	 * @param $referenceEmailData Reference to receive reference locale email data, if found
	 * @return string Email key
	 */
	protected function _getEmailData($request, &$emailData, &$referenceEmailData) {
		$emailKey = $request->getUserVar('emailKey');
		$emails = self::_getEmailTemplates($this->locale);
		$referenceEmails = self::_getEmailTemplates(MASTER_LOCALE);
		if (isset($referenceEmails[$emailKey])) {
			$referenceEmailData = $referenceEmails[$emailKey];
			if (isset($emails[$emailKey])) {
				$emailData = $emails[$emailKey];
			}
			return $emailKey;
		}
		fatalError('Invalid email key specified!');
	}

	/**
	 * Get a list of email templates for the supplied locale. Returns data in the
	 * following data structure:
	 * array(
	 *   'emailKey' => array(
	 *     'subject' => 'Localized Subject',
	 *     'body' => 'Localized Body',
	 *     'description' => 'Localized Description',
	 *     'templateFile' => '/path/to/template-file.xml',
	 *     'templateDataFile' => '/path/to/localized/data-file.xml'
	 *   ), ...
	 * )
	 * @param $locale string Locale code
	 * @return array Email template data
	 */
	static function _getEmailTemplates($locale) {
		$files = TranslatorAction::getEmailFileMap($locale);

		$returner = array();
		foreach ($files as $templateFile => $templateDataFile) {
			$xmlParser = new XMLParser();
			$data = $xmlParser->parse($templateFile);
			foreach ($data->getChildren() as $emailNode) {
				$returner[$emailNode->getAttribute('key')] = array(
					'subject' => null,
					'body' => null,
					'description' => null,
					'templateFile' => $templateFile,
					'statusCode' => 'doesNotExist',
				);
			}
			$localeData = $xmlParser->parse($templateDataFile);
			if ($localeData) foreach ($localeData->getChildren() as $emailNode) {
				$key = $emailNode->getAttribute('key');
				if (isset($returner[$key])) $returner[$key] = array_merge($returner[$key], array(
					'subject' => $emailNode->getChildValue('subject'),
					'body' => $emailNode->getChildValue('body'),
					'description' => $emailNode->getChildValue('description'),
					'templateDataFile' => $templateDataFile,
					'statusCode' => 'exists',
				));
			}
		}

		// Fill in status
		if ($locale != MASTER_LOCALE) $masterLocale = self::_getEmailTemplates(MASTER_LOCALE);
		foreach ($returner as $key => &$emailData) {
			switch($emailData['statusCode']) {
				case 'doesNotExist':
					$emailData['status'] = __('plugins.generic.translator.email.doesNotExist');
					break;
				case 'exists':
					$emailData['status'] = __('plugins.generic.translator.email.complete');
					if ($locale != MASTER_LOCALE) {
						$referenceSubject = $masterLocale[$key]['subject'];
						$referenceBody = $masterLocale[$key]['body'];

						if (0 != count(array_diff(
							AppLocale::getParameterNames($emailData['subject']),
							AppLocale::getParameterNames($referenceSubject)
						))) {
							$emailData['status'] = __('plugins.generic.translator.errors.EMAIL_ERROR_DIFFERING_PARAMS.title');
						}
						if (0 != count(array_diff(
							AppLocale::getParameterNames($emailData['body']),
							AppLocale::getParameterNames($referenceBody)
						))) {
							$emailData['status'] = __('plugins.generic.translator.errors.LOCALE_ERROR_DIFFERING_PARAMS.title');
						}
					}
					break;
			}
		}

		return $returner;
	}
}

?>
