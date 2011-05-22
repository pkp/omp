<?php

/**
 * @file controllers/tab/settings/appearance/form/AppearanceForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form to edit press appearance settings.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class AppearanceForm extends PressSettingsForm {

	/** @var array */
	var $_images;

	/** @var array */
	var $_imageSettings;


	/**
	 * Constructor.
	 */
	function AppearanceForm() {
		$this->setImages(array(
			'homeHeaderTitleImage',
			'homeHeaderLogoImage',
			'homepageImage',
			'pageHeaderTitleImage',
			'pageHeaderLogoImage'
		));

		$this->setImageSettings(array(
			'homeHeaderTitleImage' => 'homeHeaderTitleImageAltText',
			'homeHeaderLogoImage' => 'homeHeaderLogoImageAltText',
			'homepageImage' => 'homepageImageAltText',
			'pageHeaderTitleImage' => 'pageHeaderTitleImageAltText',
			'pageHeaderLogoImage' => 'pageHeaderLogoImageAltText'
		));

		$settings = array(
			'homeHeaderTitleType' => 'int',
			'homeHeaderTitle' => 'string'
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/appearance/form/appearanceForm.tpl');
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the images data.
	 * @return array
	 */
	function getImages() {
		return $this->_images;
	}

	/**
	 * Set the images data.
	 * @param array $images
	 * @return array
	 */
	function setImages($images) {
		$this->_images = $images;
	}

	/**
	 * Get the images settings data.
	 * @return array
	 */
	function getImageSettings() {
		return $this->_imageSettings;
	}

	/**
	 * Set the image settings data.
	 * @param array $imageSettings
	 * @return array
	 */
	function setImageSettings($imageSettings) {
		$this->_imageSettings = $imageSettings;
	}

	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('homeHeaderTitle');
	}


	//
	// Extend methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::fetch()
	 */
	function fetch(&$request) {
		$press =& Request::getPress();
		$params = array(
			'homeHeaderTitleImage' => $press->getSetting('homeHeaderTitleImage'),
			'homeHeaderLogoImage'=> $press->getSetting('homeHeaderLogoImage'),
			'pageHeaderTitleImage' => $press->getSetting('pageHeaderTitleImage'),
			'pageHeaderLogoImage' => $press->getSetting('pageHeaderLogoImage'),
			'homepageImage' => $press->getSetting('homepageImage'),
			'pressStyleSheet' => $press->getSetting('pressStyleSheet'),
			'locale' => Locale::getLocale()
		);

		return parent::fetch(&$request, $params);
	}

	/**
	 * @see PressSettingsForm::execute()
	 */
	function execute() {
		// Save alt text for images
		$press =& Request::getPress();
		$pressId = $press->getId();
		$locale = $this->getFormLocale();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$images = $this->getImages();

		foreach($images as $settingName) {
			$value = $press->getSetting($settingName);
			if (!empty($value)) {
				$imageSettings = $this->getImageSettings();
				$imageAltText = $this->getData($imageSettings[$settingName]);
				$value[$locale]['altText'] = $imageAltText[$locale];
				$settingsDao->updateSetting($pressId, $settingName, $value, 'object', true);
			}
		}

		// Save remaining settings
		return parent::execute();
	}


	//
	// Public methods
	//
	/**
	 * Uploads a press image.
	 * @param $settingName string setting key associated with the file
	 * @param $locale string
	 */
	function uploadImage($settingName, $locale) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$type = $fileManager->getUploadedFileType($settingName);
			$extension = $fileManager->getImageExtension($type);
			if (!$extension) {
				return false;
			}
			$uploadName = $settingName . '_' . $locale . $extension;
			if ($fileManager->uploadPressFile($press->getId(), $settingName, $uploadName)) {
				// Get image dimensions
				$filePath = $fileManager->getPressFilesPath($press->getId());
				list($width, $height) = getimagesize($filePath . '/' . $uploadName);

				$value = $press->getSetting($settingName);
				$value[$locale] = array(
					'name' => $fileManager->getUploadedFileName($settingName, $locale),
					'uploadName' => $uploadName,
					'width' => $width,
					'height' => $height,
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
	function deleteImage($settingName, $locale = null) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$setting = $settingsDao->getSetting($press->getId(), $settingName);

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->removePressFile($press->getId(), $locale !== null ? $setting[$locale]['uploadName'] : $setting['uploadName'] )) {
			$returner = $settingsDao->deleteSetting($press->getId(), $settingName, $locale);
			// Ensure page header is refreshed
			if ($returner) {
				$templateMgr =& TemplateManager::getManager();
				$templateMgr->assign(array(
					'displayPageHeaderTitle' => $press->getPressPageHeaderTitle(),
					'displayPageHeaderLogo' => $press->getPressPageHeaderLogo()
				));
			}
			return $returner;
		} else {
			return false;
		}
	}
}

?>