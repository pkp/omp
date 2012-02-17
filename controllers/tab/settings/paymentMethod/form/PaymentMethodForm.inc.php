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

	/**
	 * Constructor.
	 */
	function PaymentMethodForm($wizardMode = false) {
		$settings = array(
			'paymentPluginName' => 'string',
		);

		parent::PressSettingsForm($settings, 'controllers/tab/settings/paymentMethod/form/paymentMethodForm.tpl', $wizardMode);
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request, $params = null) {
		$templateMgr =& TemplateManager::getManager();

		// Expose names of payment plugins to template.
		$templateMgr->assign(
			'pluginNames',
			array(__('test')) +
			array_map(
				create_function('$a', 'return $a->getDisplayName();'),
				PluginRegistry::loadCategory('paymethod')
			)
		);

		return parent::fetch($request, $params);
	}
}

?>
