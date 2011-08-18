<?php

/**
 * @file controllers/grid/admin/press/form/PressSiteSettingsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSiteSettingsForm
 * @ingroup controllers_grid_admin_press_form
 *
 * @brief Form for site administrator to edit basic press settings.
 */


import('lib.pkp.classes.db.DBDataXMLParser');
import('lib.pkp.classes.form.Form');

class PressSiteSettingsForm extends Form {

	/** The ID of the press being edited */
	var $pressId;

	/**
	 * Constructor.
	 * @param $pressId omit for a new press
	 */
	function PressSiteSettingsForm($pressId = null) {
		parent::Form('admin/pressSettings.tpl');

		$this->pressId = isset($pressId) ? (int) $pressId : null;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'admin.presses.form.titleRequired'));
		$this->addCheck(new FormValidator($this, 'path', 'required', 'admin.presses.form.pathRequired'));
		$this->addCheck(new FormValidatorAlphaNum($this, 'path', 'required', 'admin.presses.form.pathAlphaNumeric'));
		$this->addCheck(new FormValidatorCustom($this, 'path', 'required', 'admin.presses.form.pathExists', create_function('$path,$form,$pressDao', 'return !$pressDao->pressExistsByPath($path) || ($form->getData(\'oldPath\') != null && $form->getData(\'oldPath\') == $path);'), array(&$this, DAORegistry::getDAO('PressDAO'))));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function fetch($args, &$request) {
		$json = new JSONMessage();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pressId', $this->pressId);
		$templateMgr->assign('helpTopicId', 'site.siteManagement');

		return parent::fetch($request);
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData() {
		if (isset($this->pressId)) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press =& $pressDao->getPress($this->pressId);

			if ($press != null) {
				$this->_data = array(
					'name' => $press->getSetting('name', null), // Localized
					'description' => $press->getSetting('description', null), // Localized
					'path' => $press->getPath(),
					'enabled' => $press->getEnabled()
				);

			} else {
				$this->pressId = null;
			}
		}
		if (!isset($this->pressId)) {
			$this->_data = array(
				'enabled' => 1
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('name', 'description', 'path', 'enabled'));
		$this->setData('enabled', (int)$this->getData('enabled'));

		if (isset($this->pressId)) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press =& $pressDao->getPress($this->pressId);
			$this->setData('oldPath', $press->getPath());
		}
	}

	/**
	 * Get a list of field names for which localized settings are used
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name', 'description');
	}

	/**
	 * Save press settings.
	 */
	function execute() {
		$pressDao =& DAORegistry::getDAO('PressDAO');

		if (isset($this->pressId)) {
			$press =& $pressDao->getPress($this->pressId);
		}

		if (!isset($press)) {
			$press = new Press();
		}

		// Check if the press path has changed.
		$pathChanged = false;
		$pressPath = $press->getPath();
		if ($pressPath != $this->getData('path')) {
			$pathChanged = true;
		}
		$press->setPath($this->getData('path'));
		$press->setEnabled($this->getData('enabled'));

		$isNewPress = false;

		if ($press->getId() != null) {
			$pressDao->updatePress($press);
			$series = null;
		} else {
			$isNewPress = true;
			$site =& Request::getSite();

			// Give it a default primary locale
			$press->setPrimaryLocale($site->getPrimaryLocale());

			$pressId = $pressDao->insertPress($press);
			$pressDao->resequencePresses();

			// Make the file directories for the press
			import('lib.pkp.classes.file.FileManager');
			FileManager::mkdir(Config::getVar('files', 'files_dir') . '/presses/' . $pressId);
			FileManager::mkdir(Config::getVar('files', 'files_dir'). '/presses/' . $pressId . '/monographs');
			FileManager::mkdir(Config::getVar('files', 'public_files_dir') . '/presses/' . $pressId);

			$installedLocales =& $site->getInstalledLocales();

			// Install default genres
			$genreDao =& DAORegistry::getDAO('GenreDAO');
			$genreDao->installDefaults($pressId, $installedLocales); /* @var $genreDao GenreDAO */

			// Install default publication formats
			$publicationFormatDao =& DAORegistry::getDAO('PublicationFormatDAO'); /* @var $publicationFormatDao PublicationFormatDAO */
			$publicationFormatDao->installDefaults($pressId, $installedLocales);

			// Install default user groups
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$userGroupDao->installSettings($pressId, 'registry/userGroups.xml');

			// Make the site administrator the press manager of newly created presses
			$sessionManager =& SessionManager::getManager();
			$userSession =& $sessionManager->getUserSession();
			if ($userSession->getUserId() != null && $userSession->getUserId() != 0 && !empty($pressId)) {
				// get the default site admin user group
				$managerUserGroup =& $userGroupDao->getDefaultByRoleId($pressId, ROLE_ID_PRESS_MANAGER);
				$userGroupDao->assignUserToGroup($userSession->getUserId(), $managerUserGroup->getId());
			}

			// Install default press settings
			$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$titles = $this->getData('title');
			Locale::requireComponents(array(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));
			$pressSettingsDao->installSettings($pressId, 'registry/pressSettings.xml', array(
				'indexUrl' => Request::getIndexUrl(),
				'pressPath' => $this->getData('path'),
				'primaryLocale' => $site->getPrimaryLocale(),
				'pressName' => $titles[$site->getPrimaryLocale()]
			));
		}
		$press->updateSetting('name', $this->getData('name'), 'string', true);
		$press->updateSetting('description', $this->getData('description'), 'string', true);
		$press->updateSetting('enabled', (int)$this->getData('enabled'), 0, true);

		// Make sure all plugins are loaded for settings preload
		PluginRegistry::loadAllPlugins();

		HookRegistry::call('PressSiteSettingsForm::execute', array(&$this, &$press, &$series, &$isNewPress));

		if ($isNewPress || $pathChanged) {
			return $press->getPath();
		}
	}

}

?>
