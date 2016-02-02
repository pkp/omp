<?php

/**
 * @file controllers/tab/settings/WebsiteSettingsTabHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebsiteSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Website settings page.
 */

// Import the base Handler.
import('lib.pkp.controllers.tab.settings.ManagerSettingsTabHandler');

class WebsiteSettingsTabHandler extends ManagerSettingsTabHandler {


	/**
	 * Constructor
	 */
	function WebsiteSettingsTabHandler() {
		$this->addRoleAssignment(ROLE_ID_MANAGER,
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
		$this->setPageTabs(array(
			'appearance' => 'controllers.tab.settings.appearance.form.AppearanceForm',
			'information' => 'lib.pkp.controllers.tab.settings.information.form.InformationForm',
			'languages' => 'controllers/tab/settings/languages/languages.tpl',
			'plugins' => 'controllers/tab/settings/plugins/plugins.tpl',
			'announcements' => 'lib.pkp.controllers.tab.settings.announcements.form.AnnouncementSettingsForm',
		));
	}

	/**
	 * @see SettingsTabHandler::showTab
	 */
	function showTab($args, $request) {
		$workingContexts = $this->getWorkingContexts($request);

		if ($workingContexts && $workingContexts->getCount() > 1) {
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->assign('multipleContexts', true);
		}
		return parent::showTab($args, $request);
	}

	//
	// Public methods.
	//
	/**
	 * Show the upload image form.
	 * @param $request Request
	 * @param $args array
	 * @return JSONMessage JSON object
	 */
	function showFileUploadForm($args, $request) {
		$fileUploadForm = $this->_getFileUploadForm($request);
		$fileUploadForm->initData($request);

		return new JSONMessage(true, $fileUploadForm->fetch($request));
	}

	/**
	 * Upload a new file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function uploadFile($args, $request) {
		$fileUploadForm = $this->_getFileUploadForm($request);
		$temporaryFileId = $fileUploadForm->uploadFile($request);

		if ($temporaryFileId !== false) {
			$json = new JSONMessage();
			$json->setAdditionalAttributes(array(
				'temporaryFileId' => $temporaryFileId
			));
			return $json;
		} else {
			return new JSONMessage(false, __('common.uploadFailed'));
		}
	}

	/**
	 * Save an uploaded file.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function saveFile($args, $request) {
		$fileUploadForm = $this->_getFileUploadForm($request);
		$fileUploadForm->readInputData();

		if ($fileUploadForm->validate()) {
			if ($fileUploadForm->execute($request)) {
				// Generate a JSON message with an event
				$settingName = $request->getUserVar('fileSettingName');
				return DAO::getDataChangedEvent($settingName);
			}
		}
		return new JSONMessage(false, __('common.invalidFileType'));
	}

	/**
	 * Deletes a press image.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function deleteFile($args, $request) {
		$settingName = $request->getUserVar('fileSettingName');

		$tabForm = $this->getTabForm();
		$tabForm->initData($request);

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
	 * @return JSONMessage JSON object
	 */
	function fetchFile($args, $request) {
		// Get the setting name.
		$settingName = $args['settingName'];

		// Try to fetch the file.
		$tabForm = $this->getTabForm();
		$tabForm->initData($request);

		$renderedElement = $tabForm->renderFileView($settingName, $request);

		$json = new JSONMessage();
		if ($renderedElement == false) {
			$json->setAdditionalAttributes(array('noData' => $settingName));
		} else {
			$json->setElementId($settingName);
			$json->setContent($renderedElement);
		}
		return $json;
	}

	/**
	 * Reload the default localized settings for the press
	 * @param $args array
	 * @param $request object
	 * @return JSONMessage JSON object
	 */
	function reloadLocalizedDefaultSettings($args, $request) {
		// make sure the locale is valid
		$locale = $request->getUserVar('localeToLoad');
		if ( !AppLocale::isLocaleValid($locale) ) {
			return new JSONMessage(false);
		}

		$press = $request->getPress();
		$pressSettingsDao = DAORegistry::getDAO('PressSettingsDAO');
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
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroupDao->installLocale($locale, $press->getId());

		return DAO::getDataChangedEvent();
	}


	//
	// Private helper methods.
	//
	/**
	 * Returns a file upload form.
	 * @param $request Request
	 * @return Form
	 */
	function &_getFileUploadForm($request) {
		$settingName = $request->getUserVar('fileSettingName');
		$fileType = $request->getUserVar('fileType');

		switch ($fileType) {
			case 'image':
				import('lib.pkp.controllers.tab.settings.appearance.form.NewContextImageFileForm');
				$fileUploadForm = new NewContextImageFileForm($settingName);
				break;
			case 'css':
				import('lib.pkp.controllers.tab.settings.appearance.form.NewContextCssFileForm');
				$fileUploadForm = new NewContextCssFileForm($settingName);
				break;
			default:
				$fileUploadForm = null;
				assert(false);
				break;
		}

		return $fileUploadForm;
	}
}

?>
