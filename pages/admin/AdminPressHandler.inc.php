<?php

/**
 * @file pages/admin/AdminPressHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AdminPressHandler
 * @ingroup pages_admin
 *
 * @brief Handle requests for press management in site administration.
 */


import('pages.admin.AdminHandler');

class AdminPressHandler extends AdminHandler {
	function AdminPressHandler() {
		parent::AdminHandler();
	}

	/**
	 * Display a list of the presses hosted on the site.
	 */
	function presses() {
		$this->validate();
		$this->setupTemplate(true);

		$rangeInfo = Handler::getRangeInfo('presses');

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses($rangeInfo);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('presses', $presses);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');
		$templateMgr->display('admin/presses.tpl');
	}

	/**
	 * Display form to create a new press.
	 */
	function createPress() {
		$this->editPress();
	}

	/**
	 * Display form to create/edit a press.
	 * @param $args array optional, if set the first parameter is the ID of the press to edit
	 */
	function editPress($args = array()) {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.admin.form.PressSiteSettingsForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$settingsForm = new PressSiteSettingsForm(!isset($args) || empty($args) ? null : $args[0]);
		} else {
			$settingsForm =& new PressSiteSettingsForm(!isset($args) || empty($args) ? null : $args[0]);
		}
		if ($settingsForm->isLocaleResubmit()) {
			$settingsForm->readInputData();
		} else {
			$settingsForm->initData();
		}
		$settingsForm->display();
	}

	/**
	 * Save changes to a press' settings.
	 */
	function updatePress() {
		$this->validate();
		$this->setupTemplate(true);

		import('classes.admin.form.PressSiteSettingsForm');

		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$settingsForm = new PressSiteSettingsForm(Request::getUserVar('pressId'));
		} else {
			$settingsForm =& new PressSiteSettingsForm(Request::getUserVar('pressId'));
		}
		$settingsForm->readInputData();

		if ($settingsForm->validate()) {
			PluginRegistry::loadCategory('blocks');
			$settingsForm->execute();
			import('lib.pkp.classes.notification.NotificationManager');
			$notificationManager = new NotificationManager();
			$notificationManager->createTrivialNotification('notification.notification', 'common.changesSaved');
			Request::redirect(null, null, 'presses');
		} else {
			$settingsForm->display();
		}
	}

	/**
	 * Delete a press.
	 * @param $args array first parameter is the ID of the press to delete
	 */
	function deletePress($args) {
		$this->validate();

		$pressDao =& DAORegistry::getDAO('PressDAO');

		if (isset($args) && !empty($args) && !empty($args[0])) {
			$pressId = $args[0];
			if ($pressDao->deletePressById($pressId)) {
				// Delete press file tree
				// FIXME move this somewhere better.
				import('lib.pkp.classes.file.FileManager');
				$fileManager = new FileManager();

				$pressPath = Config::getVar('files', 'files_dir') . '/presses/' . $pressId;
				$fileManager->rmtree($pressPath);

				import('classes.file.PublicFileManager');
				$publicFileManager = new PublicFileManager();
				$publicFileManager->rmtree($publicFileManager->getPressFilesPath($pressId));
			}
		}

		Request::redirect(null, null, 'presses');
	}

	/**
	 * Change the sequence of a press on the site index page.
	 */
	function movePress() {
		$this->validate();

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $pressDao->getPress(Request::getUserVar('pressId'));

		if ($press != null) {
			$press->setSequence($press->getSequence() + (Request::getUserVar('d') == 'u' ? -1.5 : 1.5));
			$pressDao->updatePress($press);
			$pressDao->resequencePresses();
		}

		Request::redirect(null, null, 'presses');
	}



}

?>
