<?php

/**
 * @file pages/payment/PaymentHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PaymentHandler
 * @ingroup pages_payment
 *
 * @brief Handle requests for payment functions.
 */

use APP\handler\Handler;

class PaymentHandler extends Handler
{
    /**
     * Pass request to plugin.
     *
     * @param $args array
     * @param $request PKPRequest
     */
    public function plugin($args, $request)
    {
        $paymentMethodPlugins = PluginRegistry::loadCategory('paymethod');
        $paymentMethodPluginName = array_shift($args);
        if (empty($paymentMethodPluginName) || !isset($paymentMethodPlugins[$paymentMethodPluginName])) {
            $request->redirect(null, null, 'index');
        }

        $paymentMethodPlugin = $paymentMethodPlugins[$paymentMethodPluginName];
        if (!$paymentMethodPlugin->isConfigured($request->getContext())) {
            $request->redirect(null, null, 'index');
        }

        $paymentMethodPlugin->handle($args, $request);
    }
}
