<?php

/**
 * @file classes/manager/form/setup/PressSetupStep3Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep3Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 3 of press setup.
 */

// $Id$


import('manager.form.setup.PressSetupForm');

class PressSetupStep3Form extends PressSetupForm {

	var $files;
	/**
	 * Constructor.
	 */
	function PressSetupStep3Form() {
		$this->files = array(
			'pageHeaderTitleImage',
		);
		parent::PressSetupForm(
			3,
			array(
				'authorGuidelines' => 'string',
				'submissionChecklist' => 'object',
				'metaDiscipline' => 'bool',
				'metaDisciplineExamples' => 'string',
				'metaSubjectClass' => 'bool',
				'metaSubjectClassTitle' => 'string',
				'metaSubjectClassUrl' => 'string',
				'metaSubject' => 'bool',
				'metaSubjectExamples' => 'string',
				'metaCoverage' => 'bool',
				'metaCoverageGeoExamples' => 'string',
				'metaCoverageChronExamples' => 'string',
				'metaCoverageResearchSampleExamples' => 'string',
				'metaType' => 'bool',
				'metaTypeExamples' => 'string',
				'copySubmissionAckPrimaryContact' => 'bool',
				'copySubmissionAckSpecified' => 'bool',
				'copySubmissionAckAddress' => 'string'
			)
		);

		$this->addCheck(new FormValidatorEmail($this, 'copySubmissionAckAddress', 'optional', 'user.profile.form.emailRequired'));
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('authorGuidelines', 'submissionChecklist', 'metaDisciplineExamples', 'metaSubjectClassTitle', 'metaSubjectClassUrl', 'metaSubjectExamples', 'metaCoverageGeoExamples', 'metaCoverageChronExamples', 'metaCoverageResearchSampleExamples', 'metaTypeExamples');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('newBookFileType', 'bookFileTypeSelect'));
		parent::readInputData();
	}

	/**
	 * Display the form
	 */
	function display() {
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();

		$templateMgr->assign(array('uploadedProspectus' => $press->getSetting('uploadedProspectus')));
		$templateMgr->assign_by_ref('bookFileTypes', $press->getSetting('bookFileTypes'));

		import('mail.MailTemplate');
		$mail = new MailTemplate('SUBMISSION_ACK');
		if ($mail->isEnabled()) {
			$templateMgr->assign('submissionAckEnabled', true);
		}

		parent::display();
	}

	/**
	 * Uploads a prospectus document.
	 * @param $settingName string setting key associated with the file
	 * @param $locale string
	 */
	function uploadProspectus($settingName, $locale) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		import('file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$extension = $fileManager->getExtension($_FILES[$settingName]['name']);
			if (!$extension) {
				return false;
			}
			$uploadName = $settingName . '_' . $locale . '.' . $extension;

			$setting = $settingsDao->getSetting($press->getId(), $settingName);


			if ($fileManager->uploadPressFile($press->getId(), $settingName, $uploadName)) {

				if (isset($setting)) {
					$fileManager->removePressFile(
								$press->getId(),
								$locale !== null ? $setting[$locale]['uploadName'] : $setting['uploadName']
								);
				}

				$filePath = $fileManager->getPressFilesPath($press->getId());
				$size = $fileManager->getNiceFileSize(filesize($filePath . '/' . $uploadName));

				$value = $press->getSetting($settingName);
				$value[$locale] = array(
					'name' => $fileManager->getUploadedFileName($settingName, $locale),
					'uploadName' => $uploadName,
					'size' => $size,
					'dateUploaded' => Core::getCurrentDate()
				);

				$settingsDao->updateSetting($press->getId(), $settingName, $value, 'object', true);
				return true;
			}
		}

		return false;
	}

	/**
	 * Deletes a press image.
	 * @param $settingName string setting key associated with the file
	 * @param $locale string
	 */
	function deleteProspectus($settingName, $locale = null) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$setting = $settingsDao->getSetting($press->getId(), $settingName);

		import('file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->removePressFile($press->getId(), $locale !== null ? $setting[$locale]['uploadName'] : $setting['uploadName'] )) {
			$returner = $settingsDao->deleteSetting($press->getId(), $settingName, $locale);
			return $returner;
		} else {
			return false;
		}
	}
}

?>
