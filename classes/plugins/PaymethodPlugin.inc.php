<?php

/**
 * @file classes/plugins/PaymethodPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2006-2009 Gunther Eysenbach, Juan Pablo Alperin, MJ Suhonos
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PaymethodPlugin
 * @ingroup plugins
 *
 * @brief Abstract class for paymethod plugins
 */

import('lib.pkp.classes.plugins.Plugin');

class PaymethodPlugin extends Plugin {
	/**
	 * Constructor
	 */
	function PaymethodPlugin() {
		parent::Plugin();
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category, and should be suitable for part of a filename
	 * (ie short, no spaces, and no dependencies on cases being unique).
	 * @return String name of plugin
	 */
	function getName() {
		assert(false); // Should always be overridden
	}

	/**
	 * Get a description of this plugin.
	 */
	function getDescription() {
		assert(false); // Should always be overridden
	}

	/**
	 * @see Plugin::getTemplatePath($inCore)
	 */
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates' . DIRECTORY_SEPARATOR ;
	}

	/**
	 * Display the payment form.
	 * @param $queuedPaymentId int
	 * @param $key string
	 * @param $queuedPayment QueuedPayment
	 * @param $request PKPRequest
	 */
	function displayPaymentForm($queuedPaymentId, $key, &$queuedPayment, $request) {
		assert(false); // Should always be overridden
	}

	/**
	 * Determine whether or not the payment plugin is configured for use.
	 * @return boolean
	 */
	function isConfigured() {
		return false; // Abstract; should be implemented in subclasses
	}

	/**
	 * Display the payment settings form.
	 * @param $params array
	 * @param $smarty Smarty
	 */
	function displayPaymentSettingsForm(&$params, &$smarty) {
		return $smarty->fetch($this->getTemplatePath() . 'settingsForm.tpl');
	}

	/**
	 * Fetch the settings form field names.
	 * @return array
	 */
	function getSettingsFormFieldNames() {
		return array(); // Subclasses should override
	}

	/**
	 * Handle an incoming request from a user callback or an external
	 * payment processing system.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function handle($args, $request) {
		// Subclass should override.
		$request->redirect(null, null, 'index');
	}
}

?>
