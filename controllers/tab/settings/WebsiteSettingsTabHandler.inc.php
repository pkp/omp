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
import('controllers.tab.settings.SettingsTabHandler');

class WebsiteSettingsTabHandler extends SettingsTabHandler {


	/**
	 * Constructor
	 */
	function WebsiteSettingsTabHandler() {
		$this->addRoleAssignment(ROLE_ID_PRESS_MANAGER,
				array(
					'reloadLocalizedDefaultSettings'
				)
		);
		parent::SettingsTabHandler();
		$pageTabs = array(
			'homepage' => 'controllers.tab.settings.homepage.form.HomepageForm',
			'appearance' => 'controllers.tab.settings.appearance.form.AppearanceForm',
			'languages' => 'controllers.tab.settings.languages.form.LanguagesForm'
		);
		$this->setPageTabs($pageTabs);
	}

	//
	// Implement template methods from SettingsTabHandler
	//
	/**
	 * @see SettingsTabHandler::editTabFormData()
	 */
	function editTabFormData($tabForm) {
		$locale = Locale::getLocale();
		if (is_a($tabForm, 'AppearanceForm')) {
			if (Request::getUserVar('deleteHomeHeaderTitleImage')) {
				$tabForm->deleteImage('homeHeaderTitleImage', $locale);
				return true;
			} elseif (Request::getUserVar('uploadHomeHeaderTitleImage')) {
				if (!$tabForm->uploadImage('homeHeaderTitleImage', $locale))
					$tabForm->addError('homeHeaderTitleImage', Locale::translate('manager.setup.homeTitleImageInvalid'));
				return true;
			}
		}
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
