<?php

/**
 * @file plugins/paymethod/paypal/PaypalPaymentPlugin.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PaypalPaymentPlugin
 * @ingroup plugins_paymethod_paypal
 *
 * @brief Paypal payment plugin class
 */

use APP\template\TemplateManager;
use PKP\plugins\PaymethodPlugin;

require_once(dirname(__FILE__) . '/vendor/autoload.php');


class PaypalPaymentPlugin extends PaymethodPlugin
{
    /**
     * @see Plugin::getName
     */
    public function getName()
    {
        return 'PaypalPayment';
    }

    /**
     * @see Plugin::getDisplayName
     */
    public function getDisplayName()
    {
        return __('plugins.paymethod.paypal.displayName');
    }

    /**
     * @see Plugin::getDescription
     */
    public function getDescription()
    {
        return __('plugins.paymethod.paypal.description');
    }

    /**
     * @copydoc Plugin::register()
     *
     * @param null|mixed $mainContextId
     */
    public function register($category, $path, $mainContextId = null)
    {
        if (parent::register($category, $path, $mainContextId)) {
            $this->addLocaleData();
            \HookRegistry::register('Form::config::before', [$this, 'addSettings']);
            return true;
        }
        return false;
    }

    /**
     * Add settings to the payments form
     *
     * @param string $hookName
     * @param FormComponent $form
     */
    public function addSettings($hookName, $form)
    {
        if ($form->id !== FORM_PAYMENT_SETTINGS) {
            return;
        }

        $context = Application::get()->getRequest()->getContext();
        if (!$context) {
            return;
        }

        $form->addGroup([
            'id' => 'paypalpayment',
            'label' => __('plugins.paymethod.paypal.displayName'),
            'showWhen' => 'paymentsEnabled',
        ])
            ->addField(new \PKP\components\forms\FieldOptions('testMode', [
                'label' => __('plugins.paymethod.paypal.settings.testMode'),
                'options' => [
                    ['value' => true, 'label' => __('common.enable')]
                ],
                'value' => (bool) $this->getSetting($context->getId(), 'testMode'),
                'groupId' => 'paypalpayment',
            ]))
            ->addField(new \PKP\components\forms\FieldText('accountName', [
                'label' => __('plugins.paymethod.paypal.settings.accountName'),
                'value' => $this->getSetting($context->getId(), 'accountName'),
                'groupId' => 'paypalpayment',
            ]))
            ->addField(new \PKP\components\forms\FieldText('clientId', [
                'label' => __('plugins.paymethod.paypal.settings.clientId'),
                'value' => $this->getSetting($context->getId(), 'clientId'),
                'groupId' => 'paypalpayment',
            ]))
            ->addField(new \PKP\components\forms\FieldText('secret', [
                'label' => __('plugins.paymethod.paypal.settings.secret'),
                'value' => $this->getSetting($context->getId(), 'secret'),
                'groupId' => 'paypalpayment',
            ]));

        return;
    }

    /**
     * @copydoc PaymethodPlugin::saveSettings()
     */
    public function saveSettings($params, $slimRequest, $request)
    {
        $allParams = $slimRequest->getParsedBody();
        $saveParams = [];
        foreach ($allParams as $param => $val) {
            switch ($param) {
                case 'accountName':
                case 'clientId':
                case 'secret':
                    $saveParams[$param] = (string) $val;
                    break;
                case 'testMode':
                    $saveParams[$param] = $val === 'true';
                    break;
            }
        }
        $contextId = $request->getContext()->getId();
        foreach ($saveParams as $param => $val) {
            $this->updateSetting($contextId, $param, $val);
        }
        return [];
    }

    /**
     * @copydoc PaymethodPlugin::getPaymentForm()
     */
    public function getPaymentForm($context, $queuedPayment)
    {
        $this->import('PaypalPaymentForm');
        return new PaypalPaymentForm($this, $queuedPayment);
    }

    /**
     * @copydoc PaymethodPlugin::isConfigured
     */
    public function isConfigured($context)
    {
        if (!$context) {
            return false;
        }
        if ($this->getSetting($context->getId(), 'accountName') == '') {
            return false;
        }
        return true;
    }

    /**
     * Handle a handshake with the PayPal service
     */
    public function handle($args, $request)
    {
        $context = $request->getContext();
        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO'); /** @var QueuedPaymentDAO $queuedPaymentDao */
        try {
            $queuedPayment = $queuedPaymentDao->getById($queuedPaymentId = $request->getUserVar('queuedPaymentId'));
            if (!$queuedPayment) {
                throw new \Exception("Invalid queued payment ID ${queuedPaymentId}!");
            }

            $gateway = Omnipay\Omnipay::create('PayPal_Rest');
            $gateway->initialize([
                'clientId' => $this->getSetting($context->getId(), 'clientId'),
                'secret' => $this->getSetting($context->getId(), 'secret'),
                'testMode' => $this->getSetting($context->getId(), 'testMode'),
            ]);
            $transaction = $gateway->completePurchase([
                'payer_id' => $request->getUserVar('PayerID'),
                'transactionReference' => $request->getUserVar('paymentId'),
            ]);
            $response = $transaction->send();
            if (!$response->isSuccessful()) {
                throw new \Exception($response->getMessage());
            }

            $data = $response->getData();
            if ($data['state'] != 'approved') {
                throw new \Exception('State ' . $data['state'] . ' is not approved!');
            }
            if (count($data['transactions']) != 1) {
                throw new \Exception('Unexpected transaction count!');
            }
            $transaction = $data['transactions'][0];
            if ((float) $transaction['amount']['total'] != (float) $queuedPayment->getAmount() || $transaction['amount']['currency'] != $queuedPayment->getCurrencyCode()) {
                throw new \Exception('Amounts (' . $transaction['amount']['total'] . ' ' . $transaction['amount']['currency'] . ' vs ' . $queuedPayment->getAmount() . ' ' . $queuedPayment->getCurrencyCode() . ') don\'t match!');
            }

            $paymentManager = Application::getPaymentManager($context);
            $paymentManager->fulfillQueuedPayment($request, $queuedPayment, $this->getName());
            $request->redirectUrl($queuedPayment->getRequestUrl());
        } catch (\Exception $e) {
            error_log('PayPal transaction exception: ' . $e->getMessage());
            $templateMgr = TemplateManager::getManager($request);
            $templateMgr->assign('message', 'plugins.paymethod.paypal.error');
            $templateMgr->display('frontend/pages/message.tpl');
        }
    }

    /**
     * @see Plugin::getInstallEmailTemplatesFile
     */
    public function getInstallEmailTemplatesFile()
    {
        return ($this->getPluginPath() . DIRECTORY_SEPARATOR . 'emailTemplates.xml');
    }

    /**
     * @copydoc Plugin::getTemplatePath()
     */
    public function getTemplatePath($inCore = false)
    {
        return parent::getTemplatePath($inCore) . 'templates/';
    }
}
