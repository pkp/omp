<?php

/**
 * @file SetupHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SetupHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for press setup functions.
 */

import('lib.pkp.classes.core.JSON');
import('pages.manager.ManagerHandler');

class SetupHandler extends ManagerHandler {
	/**
	 * Constructor
	 */
	function SetupHandler() {
		parent::ManagerHandler();
		$this->addRoleAssignment(
			ROLE_ID_PRESS_MANAGER,
			array (
				'setup',
				'saveSetup',
				'downloadLayoutTemplate'
			)
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display press setup form for the selected step.
	 * Displays setup index page if a valid step is not specified.
	 * @param $args array optional, if set the first parameter is the step to display
	 * @param $request PKPRequest
	 */
	function setup(&$args, &$request) {
		$this->setupTemplate(true);

		$step = isset($args[0]) ? (int) $args[0] : 1;

		if (!($step >= 1 && $step <= 5)) {
			$step = 1;
		}

		$dispatcher =& $this->getDispatcher();
		switch ($step) {
			case 3:
				// import the file type constants
				import('classes.press.LibraryFile');
				break;
		}

		$formClass = "PressSetupStep{$step}Form";
		import("classes.manager.form.setup.$formClass");

		$setupForm = new $formClass();
		$setupForm->initData();
		$setupForm->display();
	}

	/**
	 * Save changes to press settings.
	 * @param $args array first parameter is the step being saved
	 * @param $request PKPRequest
	 */
	function saveSetup(&$args, &$request) {
		$step = isset($args[0]) ? (int) $args[0] : 0;

		if ($step >= 1 && $step <= 5) {

			$this->setupTemplate(true);

			$formClass = "PressSetupStep{$step}Form";
			import("classes.manager.form.setup.$formClass");

			$setupForm = new $formClass();
			$setupForm->readInputData();
			$formLocale = $setupForm->getFormLocale();

			// Check for any special cases before trying to save
			switch ($step) {
				case 4:
					$press =& $request->getPress();
					$templates = $press->getSetting('templates');
					import('classes.file.PressFileManager');
					$pressFileManager = new PressFileManager($press);
					if ($request->getUserVar('addTemplate')) {
						// Add a layout template
						$editData = true;
						if (!is_array($templates)) $templates = array();
						$templateId = count($templates);
						$originalFilename = $_FILES['template-file']['name'];
						$fileType = $_FILES['template-file']['type'];
						$filename = "template-$templateId." . $pressFileManager->parseFileExtension($originalFilename);
						$pressFileManager->uploadFile('template-file', $filename);
						$templates[$templateId] = array(
							'originalFilename' => $originalFilename,
							'fileType' => $fileType,
							'filename' => $filename,
							'title' => $request->getUserVar('template-title')
						);
						$press->updateSetting('templates', $templates);
					} else if (($delTemplate = $request->getUserVar('delTemplate')) && count($delTemplate) == 1) {
						// Delete a template
						$editData = true;
						list($delTemplate) = array_keys($delTemplate);
						$delTemplate = (int) $delTemplate;
						$template = $templates[$delTemplate];
						$filename = "template-$delTemplate." . $pressFileManager->parseFileExtension($template['originalFilename']);
						$pressFileManager->deleteFile($filename);
						array_splice($templates, $delTemplate, 1);
						$press->updateSetting('templates', $templates);
					}

					$setupForm->setData('templates', $templates);
					break;
				case 5:
					if ($request->getUserVar('uploadHomeHeaderTitleImage')) {
						if ($setupForm->uploadImage('homeHeaderTitleImage', $formLocale)) {
							$editData = true;
						} else {
							$setupForm->addError('homeHeaderTitleImage', Locale::translate('manager.setup.homeTitleImageInvalid'));
						}

					} else if ($request->getUserVar('deleteHomeHeaderTitleImage')) {
						$editData = true;
						$setupForm->deleteImage('homeHeaderTitleImage', $formLocale);

					} else if ($request->getUserVar('uploadHomeHeaderLogoImage')) {
						if ($setupForm->uploadImage('homeHeaderLogoImage', $formLocale)) {
							$editData = true;
						} else {
							$setupForm->addError('homeHeaderLogoImage', Locale::translate('manager.setup.homeHeaderImageInvalid'));
						}

					} else if ($request->getUserVar('deleteHomeHeaderLogoImage')) {
						$editData = true;
						$setupForm->deleteImage('homeHeaderLogoImage', $formLocale);

					} else if ($request->getUserVar('uploadPageHeaderTitleImage')) {
						if ($setupForm->uploadImage('pageHeaderTitleImage', $formLocale)) {
							$editData = true;
						} else {
							$setupForm->addError('pageHeaderTitleImage', Locale::translate('manager.setup.pageHeaderTitleImageInvalid'));
						}

					} else if ($request->getUserVar('deletePageHeaderTitleImage')) {
						$editData = true;
						$setupForm->deleteImage('pageHeaderTitleImage', $formLocale);

					} else if ($request->getUserVar('uploadPageHeaderLogoImage')) {
						if ($setupForm->uploadImage('pageHeaderLogoImage', $formLocale)) {
							$editData = true;
						} else {
							$setupForm->addError('pageHeaderLogoImage', Locale::translate('manager.setup.pageHeaderLogoImageInvalid'));
						}

					} else if ($request->getUserVar('deletePageHeaderLogoImage')) {
						$editData = true;
						$setupForm->deleteImage('pageHeaderLogoImage', $formLocale);

					} else if ($request->getUserVar('uploadHomepageImage')) {
						if ($setupForm->uploadImage('homepageImage', $formLocale)) {
							$editData = true;
						} else {
							$setupForm->addError('homepageImage', Locale::translate('manager.setup.homepageImageInvalid'));
						}

					} else if ($request->getUserVar('deleteHomepageImage')) {
						$editData = true;
						$setupForm->deleteImage('homepageImage', $formLocale);
					} else if ($request->getUserVar('uploadPressStyleSheet')) {
						if ($setupForm->uploadStyleSheet('pressStyleSheet')) {
							$editData = true;
						} else {
							$setupForm->addError('pressStyleSheet', Locale::translate('manager.setup.pressStyleSheetInvalid'));
						}

					} else if ($request->getUserVar('deletePressStyleSheet')) {
						$editData = true;
						$setupForm->deleteImage('pressStyleSheet');

					} else if ($request->getUserVar('addNavItem')) {
						// Add a navigation bar item
						$editData = true;
						$navItems = $setupForm->getData('navItems');
						$navItems[$formLocale][] = array();
						$setupForm->setData('navItems', $navItems);

					} else if (($delNavItem = $request->getUserVar('delNavItem')) && count($delNavItem) == 1) {
						// Delete a  navigation bar item
						$editData = true;
						list($delNavItem) = array_keys($delNavItem);
						$delNavItem = (int) $delNavItem;
						$navItems = $setupForm->getData('navItems');
						if (is_array($navItems) && is_array($navItems[$formLocale])) {
							array_splice($navItems[$formLocale], $delNavItem, 1);
							$setupForm->setData('navItems', $navItems);
						}
					} else if ($request->getUserVar('addCustomAboutItem')) {
						// Add a custom about item
						$editData = true;
						$customAboutItems = $setupForm->getData('customAboutItems');
						$customAboutItems[$formLocale][] = array();
						$setupForm->setData('customAboutItems', $customAboutItems);

					} else if (($delCustomAboutItem = $request->getUserVar('delCustomAboutItem')) && count($delCustomAboutItem) == 1) {
						// Delete a custom about item
						$editData = true;
						list($delCustomAboutItem) = array_keys($delCustomAboutItem);
						$delCustomAboutItem = (int) $delCustomAboutItem;
						$customAboutItems = $setupForm->getData('customAboutItems');
						if (!isset($customAboutItems[$formLocale])) $customAboutItems[$formLocale][] = array();
						array_splice($customAboutItems[$formLocale], $delCustomAboutItem, 1);
						$setupForm->setData('customAboutItems', $customAboutItems);
					}

					break;
			}

			if (!isset($editData) && $setupForm->validate()) {
				$setupForm->execute();

				// Create notification to indicate that setup was saved
				import('lib.pkp.classes.notification.NotificationManager');
				$notificationManager = new NotificationManager();
				$notificationManager->createTrivialNotification('notification.notification', 'manager.setup.pressSetupUpdated');

				$request->redirect(null, null, 'setup', $step+1);
			} else {
				$setupForm->display();
			}

		} else {
			$request->redirect();
		}
	}

	function downloadLayoutTemplate($args, $request) {
		$press =& $request->getPress();
		$templates = $press->getSetting('templates');
		import('classes.file.PressFileManager');
		$pressFileManager = new PressFileManager($press);
		$templateId = (int) array_shift($args);
		if ($templateId >= count($templates) || $templateId < 0) $request->redirect(null, null, 'setup');
		$template =& $templates[$templateId];

		$filename = "template-$templateId." . $pressFileManager->parseFileExtension($template['originalFilename']);
		$pressFileManager->downloadFile($filename, $template['fileType']);
	}
}
?>
