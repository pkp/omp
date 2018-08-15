<?php

/**
 * @file controllers/grid/admin/press/form/PressSiteSettingsForm.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSiteSettingsForm
 * @ingroup controllers_grid_admin_press_form
 *
 * @brief Form for site administrator to edit basic press settings.
 */

import('lib.pkp.controllers.grid.admin.context.form.ContextSiteSettingsForm');

class PressSiteSettingsForm extends ContextSiteSettingsForm {
	/**
	 * Constructor.
	 * @param $contextId omit for a new press
	 */
	function __construct($contextId = null) {
		parent::__construct($contextId);
	}

	/**
	 * Save press settings.
	 */
	function execute() {
		$request = Application::getRequest();
		$pressDao = DAORegistry::getDAO('PressDAO');

		if (isset($this->contextId)) {
			$press = $pressDao->getById($this->contextId); /* @var $press Press */

			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
			if ($press->getEnabled() && !$this->getData('enabled')) {
				// Will disable the press. Create tombstones for all
				// published monographs publication formats.
				$publicationFormatTombstoneMgr->insertTombstonesByPress($press);
			} elseif (!$press->getEnabled() && $this->getData('enabled')) {
				// Will enable the press. Delete all tombstones.
				$publicationFormatTombstoneMgr->deleteTombstonesByPressId($press->getId());
			}
		}

		if (!isset($press)) {
			$press = $pressDao->newDataObject();
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
		$site = $request->getSite();

		if ($press->getId() != null) {
			$pressDao->updateObject($press);
		} else {
			$isNewPress = true;

			// Give it a default primary locale
			$press->setPrimaryLocale($site->getPrimaryLocale());

			$contextId = $pressDao->insertObject($press);
			$pressDao->resequence();

			// Make the file directories for the press
			import('lib.pkp.classes.file.ContextFileManager');
			$pressFileManager = new ContextFileManager($contextId);
			$pressFileManager->mkdir($pressFileManager->getBasePath());
			$pressFileManager->mkdir($pressFileManager->getBasePath() . '/monographs');

			$installedLocales = $site->getInstalledLocales();

			// Install default genres
			$genreDao = DAORegistry::getDAO('GenreDAO');
			$genreDao->installDefaults($contextId, $installedLocales); /* @var $genreDao GenreDAO */

			// load the default user groups and stage assignments.
			$this->_loadDefaultUserGroups($press->getId());

			$this->_assignManagerGroup($press->getId());

			// Install default press settings
			$pressSettingsDao = DAORegistry::getDAO('PressSettingsDAO');
			$titles = $this->getData('title');
			AppLocale::requireComponents(LOCALE_COMPONENT_APP_DEFAULT, LOCALE_COMPONENT_PKP_DEFAULT);
			$pressSettingsDao->installSettings($contextId, 'registry/pressSettings.xml', array(
				'indexUrl' => $request->getIndexUrl(),
				'pressPath' => $this->getData('path'),
				'primaryLocale' => $site->getPrimaryLocale(),
				'contextName' => $titles[$site->getPrimaryLocale()],
				'ldelim' => '{', // Used to add variables to settings without translating now
				'rdelim' => '}',
			));

			$press->updateSetting('supportedLocales', $site->getSupportedLocales());

			// load default navigationMenus.
			$this->_loadDefaultNavigationMenus($press->getId());

		}
		$press->updateSetting('name', $this->getData('name'), 'string', true);
		$press->updateSetting('description', $this->getData('description'), 'string', true);

		// Make sure all plugins are loaded for settings preload
		PluginRegistry::loadAllPlugins();

		HookRegistry::call('PressSiteSettingsForm::execute', array(&$this, &$press, &$isNewPress));

		if ($isNewPress || $pathChanged) {
			return $press->getPath();
		}
	}
}


