<?php

/**
 * @file plugins/paymethod/manual/ManualPaymentPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ManualPaymentPlugin
 * @ingroup plugins_paymethod_manual
 *
 * @brief Manual payment plugin class
 */

import('classes.plugins.PaymethodPlugin');

class ManualPaymentPlugin extends PaymethodPlugin {
	/**
	 * Constructor
	 */
	function ManualPaymentPlugin() {
		parent::PaymethodPlugin();
	}

	/**
	 * @see Plugin::getName
	 */
	function getName() {
		return 'ManualPayment';
	}

	/**
	 * @see Plugin::getDisplayName
	 */
	function getDisplayName() {
		return __('plugins.paymethod.manual.displayName');
	}

	/**
	 * @see Plugin::getDescription
	 */
	function getDescription() {
		return __('plugins.paymethod.manual.description');
	}

	/**
	 * @see Plugin::register
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			$this->addLocaleData();
			return true;
		}
		return false;
	}

	/**
	 * @see PaymentPlugin::getSettingsFormFieldNames
	 */
	function getSettingsFormFieldNames() {
		return array('manualInstructions');
	}

	/**
	 * @see PaymentPlugin::getRequiredSettingsFormFieldNames
	 */
	function getRequiredSettingsFormFieldNames() {
		return array();
	}

	/**
	 * @see PaymentPlugin::isConfigured
	 */
	function isConfigured() {
		$request = $this->getRequest();
		$press = $request->getPress();
		if (!$press) return false;

		return true;
	}

	/**
	 * @see PaymentPlugin::displayPaymentForm
	 */
	function displayPaymentForm($queuedPaymentId, $queuedPayment, $request) {
		if (!$this->isConfigured()) return false;
		$press = $request->getPress();
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);
		$templateMgr = TemplateManager::getManager($request);
		$user = $request->getUser();

		$templateMgr->assign('itemName', $queuedPayment->getName());
		$templateMgr->assign('itemDescription', $queuedPayment->getDescription());
		if ($queuedPayment->getAmount() > 0) {
			$templateMgr->assign('itemAmount', $queuedPayment->getAmount());
			$templateMgr->assign('itemCurrencyCode', $queuedPayment->getCurrencyCode());
		}
		$templateMgr->assign('manualInstructions', $this->getSetting($press->getId(), 'manualInstructions'));
		$templateMgr->assign('queuedPaymentId', $queuedPaymentId);

		$templateMgr->display($this->getTemplatePath() . 'paymentForm.tpl');
	}

	/**
	 * Handle incoming requests/notifications
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function handle($args, $request) {
		$press = $request->getPress();
		$templateMgr = TemplateManager::getManager($request);
		$user = $request->getUser();
		$op = isset($args[0])?$args[0]:null;
		$queuedPaymentId = isset($args[1])?((int) $args[1]):0;

		import('classes.payment.omp.OMPPaymentManager');
		$ompPaymentManager = new OMPPaymentManager($request);
		$queuedPayment =& $ompPaymentManager->getQueuedPayment($queuedPaymentId);
		// if the queued payment doesn't exist, redirect away from payments
		if (!$queuedPayment) return $request->redirect(null, 'index');

		switch ($op) {
			case 'notify':
				import('lib.pkp.classes.mail.MailTemplate');
				AppLocale::requireComponents(LOCALE_COMPONENT_APP_COMMON);
				$contactName = $press->getSetting('contactName');
				$contactEmail = $press->getSetting('contactEmail');
				$mail = new MailTemplate('MANUAL_PAYMENT_NOTIFICATION');
				$mail->setReplyTo($contactEmail, $contactName);
				$mail->addRecipient($contactEmail, $contactName);
				$mail->assignParams(array(
					'pressName' => $press->getLocalizedName(),
					'userFullName' => $user?$user->getFullName():('(' . __('common.none') . ')'),
					'userName' => $user?$user->getUsername():('(' . __('common.none') . ')'),
					'itemName' => $queuedPayment->getName(),
					'itemCost' => $queuedPayment->getAmount(),
					'itemCurrencyCode' => $queuedPayment->getCurrencyCode()
				));
				$mail->send();

				$templateMgr->assign(array(
					'currentUrl' => $request->url(null, null, 'payment', 'plugin', array('notify', $queuedPaymentId)),
					'pageTitle' => 'plugins.paymethod.manual.paymentNotification',
					'message' => 'plugins.paymethod.manual.notificationSent',
					'backLink' => $queuedPayment->getRequestUrl(),
					'backLinkLabel' => 'common.continue',
				));
				return $templateMgr->display('common/message.tpl');
		}
		return parent::handle($args, $request); // Don't know what to do with it
	}

	/**
	 * @see Plugin::getInstallEmailTemplatesFile
	 */
	function getInstallEmailTemplatesFile() {
		return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
	}

	/**
	 * @see Plugin::getInstallEmailTemplateDataFile
	 */
	function getInstallEmailTemplateDataFile() {
		return ($this->getPluginPath() . '/locale/{$installedLocale}/emailTemplates.xml');
	}
}

?>
