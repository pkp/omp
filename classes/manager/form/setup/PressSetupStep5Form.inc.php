<?php

/**
 * @file classes/manager/form/setup/PressSetupStep5Form.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep5Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 5 of the press setup.
 */



import('classes.manager.form.setup.PressSetupForm');

class PressSetupStep5Form extends PressSetupForm {
	var $images;
	var $image_settings;

	/**
	 * Constructor.
	 */
	function PressSetupStep5Form() {
		$this->images = array(
			'homeHeaderTitleImage',
			'homeHeaderLogoImage',
			'homepageImage',
			'pageHeaderTitleImage',
			'pageHeaderLogoImage'
		);

		$this->image_settings = array(
			'homeHeaderTitleImage' => 'homeHeaderTitleImageAltText',
			'homeHeaderLogoImage' => 'homeHeaderLogoImageAltText',
			'homepageImage' => 'homepageImageAltText',
			'pageHeaderTitleImage' => 'pageHeaderTitleImageAltText',
			'pageHeaderLogoImage' => 'pageHeaderLogoImageAltText'
		);

		parent::PressSetupForm(
			5,
			array(
				'homeHeaderTitleType' => 'int',
				'homeHeaderTitle' => 'string',
				'pageHeaderTitleType' => 'int',
				'pageHeaderTitle' => 'string',
				'readerInformation' => 'string',
				'authorInformation' => 'string',
				'librarianInformation' => 'string',
				'pressPageHeader' => 'string',
				'pressPageFooter' => 'string',
				'numRecentTitlesOnHomepage' => 'int',
				'additionalHomeContent' => 'string',
				'description' => 'string',
				'navItems' => 'object',
				'itemsPerPage' => 'int',
				'customAboutItems' => 'object',
				'numPageLinks' => 'int',
				'pressTheme' => 'string'
			)
		);
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('homeHeaderTitleType', 'customAboutItems', 'homeHeaderTitle', 'pageHeaderTitleType', 'pageHeaderTitle', 'readerInformation', 'authorInformation', 'librarianInformation', 'pressPageHeader', 'pressPageFooter', 'homepageImage', 'additionalHomeContent', 'description', 'navItems');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array_values($this->image_settings));
		parent::readInputData();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();

		$allThemes =& PluginRegistry::loadCategory('themes');
		$pressThemes = array();
		foreach ($allThemes as $key => $junk) {
			$plugin =& $allThemes[$key]; // by ref
			$pressThemes[basename($plugin->getPluginPath())] =& $plugin;
			unset($plugin);
		}

		// Ensure upload file settings are reloaded when the form is displayed.
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign(array(
			'homeHeaderTitleImage' => $press->getSetting('homeHeaderTitleImage'),
			'homeHeaderLogoImage'=> $press->getSetting('homeHeaderLogoImage'),
			'pageHeaderTitleImage' => $press->getSetting('pageHeaderTitleImage'),
			'pageHeaderLogoImage' => $press->getSetting('pageHeaderLogoImage'),
			'homepageImage' => $press->getSetting('homepageImage'),
			'pressStyleSheet' => $press->getSetting('pressStyleSheet'),
			'readerInformation' => $press->getSetting('readerInformation'),
			'authorInformation' => $press->getSetting('authorInformation'),
			'librarianInformation' => $press->getSetting('librarianInformation'),
			'pressThemes' => $pressThemes
		));

		// Make lists of the sidebar blocks available.
		$templateMgr->initialize();
		$leftBlockPlugins = $disabledBlockPlugins = $rightBlockPlugins = array();
		$plugins =& PluginRegistry::getPlugins('blocks');
		foreach ($plugins as $key => $junk) {
			if (!$plugins[$key]->getEnabled() || $plugins[$key]->getBlockContext() == '') {
				if (count(array_intersect($plugins[$key]->getSupportedContexts(), array(BLOCK_CONTEXT_LEFT_SIDEBAR, BLOCK_CONTEXT_RIGHT_SIDEBAR))) > 0) $disabledBlockPlugins[] =& $plugins[$key];
			} else switch ($plugins[$key]->getBlockContext()) {
				case BLOCK_CONTEXT_LEFT_SIDEBAR:
					$leftBlockPlugins[] =& $plugins[$key];
					break;
				case BLOCK_CONTEXT_RIGHT_SIDEBAR:
					$rightBlockPlugins[] =& $plugins[$key];
					break;
			}
		}
		$templateMgr->assign(array(
			'disabledBlockPlugins' => &$disabledBlockPlugins,
			'leftBlockPlugins' => &$leftBlockPlugins,
			'rightBlockPlugins' => &$rightBlockPlugins
		));

		$templateMgr->setCacheability(CACHEABILITY_MUST_REVALIDATE);
		parent::display();
	}

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

	/**
	 * Uploads press custom stylesheet.
	 * @param $settingName string setting key associated with the file
	 */
	function uploadStyleSheet($settingName) {
		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->uploadedFileExists($settingName)) {
			$type = $fileManager->getUploadedFileType($settingName);
			if ($type != 'text/plain' && $type != 'text/css') {
				return false;
			}

			$uploadName = $settingName . '.css';
			if($fileManager->uploadPressFile($press->getId(), $settingName, $uploadName)) {
				$value = array(
					'name' => $fileManager->getUploadedFileName($settingName),
					'uploadName' => $uploadName,
					'dateUploaded' => date("Y-m-d g:i:s")
				);

				$settingsDao->updateSetting($press->getId(), $settingName, $value, 'object');
				return true;
			}
		}

		return false;
	}

	function execute() {
		// Save the block plugin layout settings.
		$blockVars = array('blockSelectLeft', 'blockUnselected', 'blockSelectRight');
		foreach ($blockVars as $varName) {
			$$varName = split(' ', Request::getUserVar($varName));
		}

		$plugins =& PluginRegistry::loadCategory('blocks');
		foreach ($plugins as $key => $junk) {
			$plugin =& $plugins[$key]; // Ref hack
			$plugin->setEnabled(!in_array($plugin->getName(), $blockUnselected));
			if (in_array($plugin->getName(), $blockSelectLeft)) {
				$plugin->setBlockContext(BLOCK_CONTEXT_LEFT_SIDEBAR);
				$plugin->setSeq(array_search($key, $blockSelectLeft));
			}
			else if (in_array($plugin->getName(), $blockSelectRight)) {
				$plugin->setBlockContext(BLOCK_CONTEXT_RIGHT_SIDEBAR);
				$plugin->setSeq(array_search($key, $blockSelectRight));
			}
			unset($plugin);
		}

		// Save alt text for images
		$press =& Request::getPress();
		$pressId = $press->getId();
		$locale = $this->getFormLocale();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$images = $this->images;

		foreach($images as $settingName) {
			$value = $press->getSetting($settingName);
			if (!empty($value)) {
				$imageAltText = $this->getData($this->image_settings[$settingName]);
				$value[$locale]['altText'] = $imageAltText[$locale];
				$settingsDao->updateSetting($pressId, $settingName, $value, 'object', true);
			}
		}

		// Save remaining settings
		return parent::execute();
	}
}

?>
