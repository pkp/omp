<?php

/**
 * @file controllers/tab/settings/paymentMethod/form/PaymentMethodForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymentMethodForm
 * @ingroup controllers_tab_settings_paymentMethod_form
 *
 * @brief Form to edit press payment method settings.
 */

import('lib.pkp.classes.form.Form');
import('controllers.tab.settings.form.PressSettingsForm');

class PaymentMethodForm extends PressSettingsForm {
	/** @var $paymentPlugins array */
	var $paymentPlugins;

	/**
	 * Constructor.
	 */
	function PaymentMethodForm($wizardMode = false) {
		$settings = array(
			'paymentPluginName' => 'string',
			'pressCurrency' => 'string',
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/paymentMethod/form/paymentMethodForm.tpl', $wizardMode);
		$this->paymentPlugins =& PluginRegistry::loadCategory('paymethod');
	}

	/**
	 * @see PressSettingsForm::fetch
	 */
	function fetch(&$request) {
		$templateMgr =& TemplateManager::getManager();
		$currencyDao =& DAORegistry::getDAO('CurrencyDAO');
		$currencies = array();
		foreach ($currencyDao->getCurrencies() as $currency) {
			$currencies[$currency->getCodeAlpha()] = $currency->getName();
		}
		$templateMgr->assign('currencies', $currencies);
		return parent::fetch($request);
	}

	/**
	 * @see PressSettingsForm::initData
	 */

	/**
	 * @see PressSettingsForm::readInputData
	 */
	function readInputData(&$request) {
		parent::readInputData($request);

		$paymentPluginName = $this->getData('paymentPluginName');
		if (!isset($this->paymentPlugins[$paymentPluginName])) return false;
		$plugin =& $this->paymentPlugins[$paymentPluginName];

		$this->readUserVars($plugin->getSettingsFormFieldNames());
	}

	/**
	 * @see PressSettingsForm::execute
	 */
	function execute(&$request) {
		$press =& $request->getPress();

		// Get the selected payment plugin
		$paymentPluginName = $this->getData('paymentPluginName');
		if (!isset($this->paymentPlugins[$paymentPluginName])) return;
		$plugin =& $this->paymentPlugins[$paymentPluginName];

		// Save the plugin-specific settings
		foreach ($plugin->getSettingsFormFieldNames() as $settingName) {
			$plugin->updateSetting($press->getId(), $settingName, $this->getData($settingName));
		}

		return parent::execute($request);
	}
}

?>
