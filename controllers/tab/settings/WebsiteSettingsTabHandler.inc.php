<?php

/**
 * @file controllers/tab/settings/WebsiteSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebsiteSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Website settings page.
 */

// Import the base Handler.
import('controllers.tab.settings.ManagerSettingsTabHandler');
// Import form to upload images.
import('controllers.tab.settings.appearance.form.ImageUploadForm');
// Import form to upload css.
import('controllers.tab.settings.appearance.form.CssUploadForm');

class WebsiteSettingsTabHandler extends ManagerSettingsTabHandler {


	/**
	 * Constructor
	 */
	function WebsiteSettingsTabHandler() {
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'showFileUploadForm',
					'uploadFile',
					'saveFile',
					'deleteFile',
					'fetchFile',
					'reloadLocalizedDefaultSettings'
				)
		);
		parent::ManagerSettingsTabHandler();
		$pageTabs = array(
			'homepage' => 'controllers.tab.settings.homepage.form.HomepageForm',
			'appearance' => 'controllers.tab.settings.appearance.form.AppearanceForm',
			'languages' => 'controllers.tab.settings.languages.form.LanguagesForm'
		);
		$this->setPageTabs($pageTabs);
	}


	//
	// Public methods.
	//
	/**
	 * Show the upload image form.
	 * @param $request Request
	 * @param $args array
	 * @return string JSON message
	 */
	function showFileUploadForm($args, &$request) {
		$settingName = $request->getUserVar('fileSettingName');
		$fileUploadFormName = $request->getUserVar('fileUploadForm');

		$fileUploadForm = new $fileUploadFormName($settingName);
		$fileUploadForm->initData();

		$json = new JSONMessage(true, $fileUploadForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Upload a new file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile($args, &$request) {
		$user =& $request->getUser();

		import('classes.file.TemporaryFileManager');
		$temporaryFileManager = new TemporaryFileManager();
		$temporaryFile = $temporaryFileManager->handleUpload('uploadedFile', $user->getId());
		if ($temporaryFile) {
			$json = new JSONMessage(true);
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFile->getId()
			));
		} else {
			$json = new JSONMessage(false, Locale::translate('common.uploadFailed'));
		}

		return $json->getString();
	}

	/**
	 * Save an uploaded file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function saveFile($args, &$request) {
		$settingName = $request->getUserVar('fileSettingName');
		$formName = $request->getUserVar('formName');

		$fileForm = new $formName($settingName);
		$fileForm->readInputData();

		if ($fileForm->validate()) {
			if ($fileForm->execute($request)) {
				// Generate a JSON message with an event
				return DAO::getDataChangedEvent($settingName);
			}
		}
		return new JSONMessage(false);
	}

	/**
	 * Deletes a press image.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function deleteFile($args, &$request) {
		$settingName = $request->getUserVar('fileSettingName');
		$tabFormName = $request->getUserVar('formName');

		$tabForm = $this->getTabFormByName($tabFormName);
		$tabForm->initData();

		if ($tabForm->deleteFile($settingName, $request)) {
			return DAO::getDataChangedEvent($settingName);
		} else {
			return new JSONMessage(false);
		}
	}

	/**
	 * Fetch a file that have been uploaded.
	 *
	 * @param $args array
	 * @param $request Request
	 * @return string
	 */
	function fetchFile($args, &$request) {
		// Get the setting name.
		$settingName = $args['settingName'];

		// Try to fetch the file.
		$tabForm = $this->getTabForm();
		$tabForm->initData();

		$renderedElement = $tabForm->renderFileView($settingName, $request);

		$json = new JSONMessage();
		if ($renderedElement == false) {
			$json->setAdditionalAttributes(array('noData' => $settingName));
		} else {
			$json->setElementId($settingName);
			$json->setContent($renderedElement);
		}
		return $json->getString();
	}

	/**
	 * Reload the default localized settings for the press
	 * @param $args array
	 * @param $request object
	 */
	function reloadLocalizedDefaultSettings($args, &$request) {
		// make sure the locale is valid
		$locale = $request->getUserVar('localeToLoad');
		if ( !Locale::isLocaleValid($locale) ) {
			$json = new JSONMessage(false);
			return $json->getString();
		}

		$press =& $request->getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->reloadLocalizedDefaultSettings(
			$press->getId(), 'registry/pressSettings.xml',
			array(
				'indexUrl' => $request->getIndexUrl(),
				'pressPath' => $press->getData('path'),
				'primaryLocale' => $press->getPrimaryLocale(),
				'pressName' => $press->getName($press->getPrimaryLocale())
			),
			$locale
		);

		// also reload the user group localizable data
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupDao->installLocale($locale, $press->getId());

		return DAO::getDataChangedEvent();
	}
}


?>
