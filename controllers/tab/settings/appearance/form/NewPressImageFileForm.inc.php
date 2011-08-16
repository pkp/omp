<?php

/**
 * @file controllers/tab/settings/appearance/form/NewPressImageFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewPressImageFileForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form for upload an image.
 */

import('controllers.tab.settings.form.SettingsFileUploadForm');

class NewPressImageFileForm extends SettingsFileUploadForm {

	/**
	 * Constructor.
	 * @param $imageSettingName string
	 */
	function NewPressImageFileForm($imageSettingName) {
		parent::SettingsFileUploadForm('controllers/tab/settings/form/newImageFileUploadForm.tpl');
		$this->setFileSettingName($imageSettingName);
	}


	//
	// Extend methods from SettingsFileUploadForm.
	//
	/**
	 * @see SettingsFileUploadForm::fetch()
	 */
	function fetch(&$request) {
		$params = array('fileType' => 'image');
		return parent::fetch($request, $params);
	}


	//
	// Extend methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('imageAltText');
	}

	/**
	 * @see Form::initData()
	 */
	function initData(&$request) {
		$press =& $request->getPress();
		$fileSettingName = $this->getFileSettingName();

		$image = $press->getSetting($fileSettingName);
		$imageAltText = array();

		$supportedLocales = Locale::getSupportedLocales();
		foreach ($supportedLocales as $key => $locale) {
			$imageAltText[$key] = $image[$key]['altText'];
		}

		$this->setData('imageAltText', $imageAltText);
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('imageAltText'));

		parent::readInputData();
	}

	/**
	 * Save the new image file.
	 * @param $request Request.
	 */
	function execute(&$request) {
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
			if($fileManager->copyPressFile($press->getId(), $temporaryFile->getFilePath(), $uploadName)) {

				// Get image dimensions
				$filePath = $fileManager->getPressFilesPath($press->getId());
				list($width, $height) = getimagesize($filePath . '/' . $uploadName);

				$value = $press->getSetting($this->getFileSettingName());
				$imageAltText = $this->getData('imageAltText');

				$value[$locale] = array(
					'name' => $temporaryFile->getOriginalFileName(),
					'uploadName' => $uploadName,
					'width' => $width,
					'height' => $height,
					'dateUploaded' => Core::getCurrentDate(),
					'altText' => $imageAltText[$locale]
				);

				$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
				$settingsDao->updateSetting($press->getId(), $this->getFileSettingName(), $value, 'object', true);

				// Clean up the temporary file.
				$this->removeTemporaryFile($request);

				return true;
			}
		}
		return false;
	}
}

?>
