<?php

/**
 * @file controllers/tab/settings/appearance/form/NewSiteImageFileForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NewSiteImageFileForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form for upload an image.
 */

import('controllers.tab.settings.form.SettingsFileUploadForm');

class NewSiteImageFileForm extends SettingsFileUploadForm {

	/**
	 * Constructor.
	 * @param $imageSettingName string
	 */
	function NewSiteImageFileForm($imageSettingName) {
		parent::SettingsFileUploadForm('controllers/tab/settings/form/newImageFileUploadForm.tpl');
		$this->setFileSettingName($imageSettingName);
	}


	//
	// Extend methods from Form.
	//
	/**
	 * @see Form::initData()
	 */
	function initData(&$request) {
		$site =& $request->getSite();
		$fileSettingName = $this->getFileSettingName();

		$image = $site->getSetting($fileSettingName);
		$imageAltText = array();

		$supportedLocales = Locale::getSupportedLocales();
		foreach ($supportedLocales as $key => $locale) {
			$imageAltText[$key] = $image[$key]['altText'];
		}

		$this->setData('imageAltText', $imageAltText);
	}

	//
	// Extend methods from SettingsFileUploadForm.
	//
	/**
	 * @see SettingsFileUploadForm::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('imageAltText'));

		parent::readInputData();
	}

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
			if ($fileManager->copyFile($temporaryFile->getFilePath(), $fileManager->getSiteFilesPath() . '/' . $uploadName)) {

				// Get image dimensions
				$filePath = $fileManager->getSiteFilesPath();
				list($width, $height) = getimagesize($filePath . '/' . $uploadName);

				$site =& $request->getSite();
				$siteDao =& DAORegistry::getDAO('SiteDAO');
				$value = $site->getSetting($this->getFileSettingName());
				$imageAltText = $this->getData('imageAltText');

				$value[$locale] = array(
					'originalFilename' => $temporaryFile->getOriginalFileName(),
					'uploadName' => $uploadName,
					'width' => $width,
					'height' => $height,
					'dateUploaded' => Core::getCurrentDate(),
					'altText' => $imageAltText[$locale]
				);

				$site->updateSetting($this->getFileSettingName(), $value, 'object', true);

				// Clean up the temporary file
				$this->removeTemporaryFile($request);

				return true;
			}
		}
		return false;
	}
}

?>
