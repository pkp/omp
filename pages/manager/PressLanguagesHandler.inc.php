<?php

/**
 * @file PressLanguagesHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressLanguagesHandler
 * @ingroup pages_manager
 *
 * @brief Handle requests for changing press language settings. 
 */

// $Id$

import('pages.manager.ManagerHandler');

class PressLanguagesHandler extends ManagerHandler {
	/**
	 * Constructor
	 */	
	function PressLanguagesHandler() {
		parent::ManagerHandler();
	}
	
	/**
	 * Display form to edit language settings.
	 */
	function languages() {
		$this->validate();
		$this->setupTemplate(true);

		import('manager.form.LanguageSettingsForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$settingsForm =& new LanguageSettingsForm();
		$settingsForm->initData();
		$settingsForm->display();
	}

	/**
	 * Save changes to language settings.
	 */
	function saveLanguageSettings() {
		$this->validate();
		$this->setupTemplate(true);

		import('manager.form.LanguageSettingsForm');

		// FIXME: Need construction by reference or validation always fails on PHP 4.x
		$settingsForm =& new LanguageSettingsForm();
		$settingsForm->readInputData();

		if ($settingsForm->validate()) {
			$settingsForm->execute();

			$templateMgr =& TemplateManager::getManager();
			$templateMgr->assign(array(
				'currentUrl' => Request::url(null, null, 'languages'),
				'pageTitle' => 'common.languages',
				'message' => 'common.changesSaved',
				'backLink' => Request::url(null, Request::getRequestedPage()),
				'backLinkLabel' => 'manager.pressManagement'
			));
			$templateMgr->display('common/message.tpl');

		} else {
			$settingsForm->display();
		}
	}
	
	function reloadLocalizedDefaultSettings() {
		// make sure the locale is valid
		$locale = Request::getUserVar('localeToLoad');
		if ( !Locale::isLocaleValid($locale) ) {
			Request::redirect(null, null, 'languages');
		}

		$this->validate();
		$this->setupTemplate(true);
					
		$press =& Request::getPress();
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->reloadLocalizedDefaultSettings($press->getId(), 'registry/pressSettings.xml', array(
				'indexUrl' => Request::getIndexUrl(),
				'pressPath' => $press->getData('path'),
				'primaryLocale' => $press->getPrimaryLocale(),
				'pressName' => $press->getName($press->getPrimaryLocale())
			),
			$locale);

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign(array(
			'currentUrl' => Request::url(null, null, 'languages'),
			'pageTitle' => 'common.languages',
			'message' => 'common.changesSaved',
			'backLink' => Request::url(null, Request::getRequestedPage()),
			'backLinkLabel' => 'manager.pressManagement'
		));
		$templateMgr->display('common/message.tpl');
	}

	

}
?>
