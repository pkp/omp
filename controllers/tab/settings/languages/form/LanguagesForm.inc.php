<?php

/**
 * @file controllers/tab/settings/languages/form/LanguagesForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class LanguagesForm
 * @ingroup controllers_tab_settings_languages_form
 *
 * @brief Form for modifying press language settings.
 */

// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class LanguagesForm extends PressSettingsForm {

	/** @var array set of locales available for press use */
	var $_availableLocales;

	/**
	 * Constructor.
	 */
	function LanguagesForm($wizardMode = false) {
		$settings = array(
			'supportedLocales' => 'object',
			'supportedSubmissionLocales' => 'object',
			'supportedFormLocales' => 'object'
		);

		$site =& Request::getSite();
		$this->setAvailableLocales($site->getSupportedLocales());

		$localeCheck = create_function('$locale,$availableLocales', 'return in_array($locale,$availableLocales);');

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'primaryLocale', 'required', 'manager.languages.form.primaryLocaleRequired'), array('Locale', 'isLocaleValid'));
		$this->addCheck(new FormValidator($this, 'primaryLocale', 'required', 'manager.languages.form.primaryLocaleRequired'), $localeCheck, array(&$this->availableLocales));

		parent::PressSettingsForm($settings, 'controllers/tab/settings/languages/form/languages.tpl', $wizardMode);
	}


	//
	// Getters and Setters
	//
	/**
	 * Get site available locales.
	 * @return array
	 */
	function getAvailableLocales() {
		return $this->_availableLocales;
	}

	/**
	 * Set site available locales.
	 * @param $siteAvailableLocales array
	 */
	function setAvailableLocales($siteAvailableLocales) {
		$this->_availableLocales = $siteAvailableLocales;
	}


	//
	// Overridden methods from PressSettingsForm.
	//
	/**
	 * @see PressSettingsForm::initData()
	 * @param $request Request
	 */
	function initData($request) {
		$press =& $request->getPress();
		$this->setData('primaryLocale', $press->getPrimaryLocale());

		parent::initData($request);

		foreach (array('supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales') as $name) {
			if ($this->getData($name) == null || !is_array($this->getData($name))) {
				$this->setData($name, array());
			}
		}
	}

	/**
	 * @see PressSettingsForm::fetch()
	 */
	function fetch(&$request) {
		$site =& $request->getSite();
		$availableLocales = $site->getSupportedLocaleNames();

		$reloadDefaultsLinkActions = $this->_getLinkActions($request, $availableLocales);

		$params = array(
			'reloadDefaultsLinkActions' => $reloadDefaultsLinkActions,
			'availableLocales' => $availableLocales,
			'helpTopicId' => 'press.managementPages.languages'
		);
		return parent::fetch(&$request, $params);
	}

	/**
	 * Assign form data to user-submitted data.
	 * @param $request Request
	 */
	function readInputData($request) {
		$primaryLocale = $request->getUserVar('primaryLocale');
		$this->setData('primaryLocale', $primaryLocale);

		parent::readInputData($request);

		foreach (array('supportedFormLocales', 'supportedSubmissionLocales', 'supportedLocales') as $name) {
			if ($this->getData($name) == null || !is_array($this->getData($name))) {
				$this->setData($name, array());
			}
		}
	}

	/**
	 * Save modified settings.
	 */
	function execute($request) {
		$press =& $request->getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');

		// Verify additional locales
		foreach (array('supportedLocales', 'supportedSubmissionLocales', 'supportedFormLocales') as $name) {
			$$name = array();
			foreach ($this->getData($name) as $locale) {
				if (Locale::isLocaleValid($locale) && in_array($locale, $this->getAvailableLocales())) {
					array_push($$name, $locale);
				}
			}
		}

		$primaryLocale = $this->getData('primaryLocale');

		// Make sure at least the primary locale is chosen as available
		if ($primaryLocale != null && !empty($primaryLocale)) {
			foreach (array('supportedLocales', 'supportedSubmissionLocales', 'supportedFormLocales') as $name) {
				if (!in_array($primaryLocale, $$name)) {
					array_push($$name, $primaryLocale);
				}
			}
		}
		$this->setData('supportedLocales', $supportedLocales);
		$this->setData('supportedSubmissionLocales', $supportedSubmissionLocales);
		$this->setData('supportedFormLocales', $supportedFormLocales);

		parent::execute();

		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press->setPrimaryLocale($this->getData('primaryLocale'));
		$pressDao->updatePress($press);
	}


	//
	// Private helper methods
	//
	/**
	 * Get link actions for form.
	 * @param $request Request
	 * @param $availableLocales array
	 * @return array
	 */
	function _getLinkActions($request, $availableLocales) {
		$router =& $request->getRouter();
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');

		$reloadDefaultsLinkAction = array();

		foreach ($availableLocales as $localeKey => $localeName) {
			$params = array('localeToLoad' => $localeKey);
			$confirmationModal = new ConfirmationModal(
				__('manager.language.confirmDefaultSettingsOverwrite'),
				null,
				$router->url($request, null, null, 'reloadLocalizedDefaultSettings', null, $params)
			);
			$reloadDefaultsLinkAction[$localeKey] = new LinkAction(
				'reloadDefault-' . $localeKey,
				$confirmationModal,
				__('manager.language.reloadLocalizedDefaultSettings'),
				null
			);
		}

		return $reloadDefaultsLinkAction;
	}
}

?>
