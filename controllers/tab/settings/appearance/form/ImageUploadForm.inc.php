<?php

/**
 * @file controllers/tab/settings/appearance/form/ImageUploadForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ImageUploadForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form for upload an image.
 */

import('controllers.tab.settings.form.SettingsFileUploadForm');

class ImageUploadForm extends SettingsFileUploadForm {

	/**
	 * Constructor.
	 * @param $imageSettingName string
	 */
	function ImageUploadForm($imageSettingName) {
		parent::SettingsFileUploadForm();
		$this->setFileSettingName($imageSettingName);
	}


	//
	// Extend methods from SettingsFileUploadForm.
	//
	/**
	 * @see SettingsFileUploadForm::fetch()
	 */
	function fetch($request) {
		$params = array('formName' => 'ImageUploadForm');
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
			$extension = $fileManager->getImageExtension($type);
			if (!$extension) {
				return false;
			}
			$locale = Locale::getLocale();
			$press = $request->getPress();

			$uploadName = $this->getFileSettingName() . '_' . $locale . $extension;
			$fileManager->copyPressFile($press->getId(), $temporaryFile->getFilePath(), $uploadName);

			// Get image dimensions
			$filePath = $fileManager->getPressFilesPath($press->getId());
			list($width, $height) = getimagesize($filePath . '/' . $uploadName);

			$value = $press->getSetting($this->getFileSettingName());
			$value[$locale] = array(
				'name' => $temporaryFile->getOriginalFileName(),
				'uploadName' => $uploadName,
				'width' => $width,
				'height' => $height,
				'dateUploaded' => Core::getCurrentDate()
			);

			$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$settingsDao->updateSetting($press->getId(), $this->getFileSettingName(), $value, 'object', true);

			// Clean up the temporary file
			$this->removeTemporaryFile($request);

			return true;
		}
		return false;
	}
}

?>
