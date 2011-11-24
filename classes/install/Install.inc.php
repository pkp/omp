<?php

/**
 * @file classes/install/Install.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Install
 * @ingroup install
 * @see Installer, InstallForm
 *
 * @brief Perform system installation.
 *
 * This script will:
 *  - Create the database (optionally), and install the database tables and initial data.
 *  - Update the config file with installation parameters.
 */


// Default installation data
define('INSTALLER_DEFAULT_SITE_TITLE', 'common.omp');
define('INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH', 6);

import('lib.pkp.classes.install.PKPInstall');

class Install extends PKPInstall {

	/**
	 * Constructor.
	 * @see install.form.InstallForm for the expected parameters
	 * @param $params array installer parameters
	 * @param $descriptor string descriptor path
	 * @param $isPlugin boolean true iff a plugin is being installed
	 */
	function Install($params, $descriptor = 'install.xml', $isPlugin = false) {
		parent::PKPInstall($descriptor, $params, $isPlugin);
	}

	//
	// Installer actions
	//

	/**
	 * Get the names of the directories to create.
	 * @return array
	 */
	function getCreateDirectories() {
		$directories = parent::getCreateDirectories();
		$directories[] = 'presses';
		return $directories;
	}

	/**
	 * Create initial required data.
	 * @return boolean
	 */
	function createData() {
		// Add initial site data
		$locale = $this->getParam('locale');
		$siteDao =& DAORegistry::getDAO('SiteDAO', $this->dbconn);
		$site = $site->newDataObject();
		$site->setRedirect(0);
		$site->setMinPasswordLength(INSTALLER_DEFAULT_MIN_PASSWORD_LENGTH);
		$site->setPrimaryLocale($locale);
		$site->setInstalledLocales($this->installedLocales);
		$site->setSupportedLocales($this->installedLocales);
		if (!$siteDao->insertSite($site)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		// Install email template list and data for each locale
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->installEmailTemplates($emailTemplateDao->getMainEmailTemplatesFilename());
		foreach ($this->installedLocales as $locale) {
			$emailTemplateDao->installEmailTemplateData($emailTemplateDao->getMainEmailTemplateDataFilename($locale));
		}

		$siteSettingsDao =& DAORegistry::getDAO('SiteSettingsDAO');
		$siteSettingsDao->updateSetting('title', array($locale => __(INSTALLER_DEFAULT_SITE_TITLE)), null, true);
		$siteSettingsDao->updateSetting('contactName', array($locale => __(INSTALLER_DEFAULT_SITE_TITLE)), null, true);
		$siteSettingsDao->updateSetting('contactEmail', array($locale => $this->getParam('adminEmail')), null, true);

		// Add initial site administrator user
		$userDao =& DAORegistry::getDAO('UserDAO', $this->dbconn);
		$user = new User();
		$user->setUsername($this->getParam('adminUsername'));
		$user->setPassword(Validation::encryptCredentials($this->getParam('adminUsername'), $this->getParam('adminPassword'), $this->getParam('encryption')));
		$user->setFirstName($user->getUsername());
		$user->setLastName('');
		$user->setEmail($this->getParam('adminEmail'));
		if (!$userDao->insertUser($user)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		// Create an admin user group
		AppLocale::requireComponents(array(LOCALE_COMPONENT_OMP_DEFAULT_SETTINGS));
		$userGroupDao =& DAORegistry::getDao('UserGroupDAO', $this->dbconn);
		$adminUserGroup = new UserGroup();
		$adminUserGroup->setRoleId(ROLE_ID_SITE_ADMIN);
		$adminUserGroup->setContextId(0);
		$adminUserGroup->setPath(ROLE_PATH_SITE_ADMIN);
		$adminUserGroup->setDefault(true);
		foreach ($this->installedLocales as $locale) {
			$name = __('default.groups.name.siteAdmin', array(), $locale);
			$namePlural = __('default.groups.plural.siteAdmin', array(), $locale);
			$adminUserGroup->setData('name', $name, $locale);
			$adminUserGroup->setData('namePlural', $namePlural, $locale);
		}
		if (!$userGroupDao->insertUserGroup($adminUserGroup)) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		// Put the installer into this user group
		if (!$userGroupDao->assignUserToGroup($user->getId(), $adminUserGroup->getId())) {
			$this->setError(INSTALLER_ERROR_DB, $this->dbconn->errorMsg());
			return false;
		}

		return true;
	}
}

?>
