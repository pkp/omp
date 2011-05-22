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
		parent::SettingsTabHandler();
		$pageTabs = array(
			'homepage' => 'controllers.tab.settings.homepage.form.HomepageForm',
			'appearance' => 'controllers.tab.settings.appearance.form.AppearanceForm'
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
}

?>
