<?php

/**
 * @file controllers/tab/settings/appearance/form/CssUploadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CssUploadForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form for upload an css file.
 */

import('controllers.tab.settings.form.SettingsFileUploadForm');

class CssUploadForm extends SettingsFileUploadForm {

	/**
	 * Constructor.
	 * @param $imageSettingName string
	 */
	function CssUploadForm($cssSettingName) {
		parent::SettingsFileUploadForm();
		$this->setFileSettingName($cssSettingName);
	}


	//
	// Extend methods from SettingsFileUploadForm.
	//
	/**
	 * @see SettingsFileUploadForm::fetch()
	 */
	function fetch($request) {
		$params = array('formName' => 'CssUploadForm');
		return parent::fetch($request, $params);
	}


	//
	// Extend methods from Form.
	//
	/**
	 * Save the new image file.
	 * @param $request Request.
	 */
	function execute($request) {
		$temporaryFile = $this->fetchTemporaryFile($request);

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();

		if (is_a($temporaryFile, 'TemporaryFile')) {
			$type = $temporaryFile->getFileType();
			if ($type != 'text/plain' && $type != 'text/css') {
				return false;
			}

			$settingName = $this->getFileSettingName();
			$uploadName = $settingName . '.css';
			$press = $request->getPress();
			if($fileManager->copyPressFile($press->getId(), $temporaryFile->getFilePath(), $uploadName)) {
				$value = array(
					'name' => $temporaryFile->getOriginalFileName(),
					'uploadName' => $uploadName,
					'dateUploaded' => Core::getCurrentDate()
				);

				$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
				$settingsDao->updateSetting($press->getId(), $settingName, $value, 'object');
				return true;
			}

			// Clean up the temporary file
			$this->removeTemporaryFile($request);

			return true;
		}
		return false;
	}
}

?>
