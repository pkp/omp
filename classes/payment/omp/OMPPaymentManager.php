<?php

/**
 * @file classes/payment/omp/OMPPaymentManager.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPPaymentManager
 *
 * @ingroup payment
 *
 * @see QueuedPayment
 *
 * @brief Provides payment management functions.
 *
 */

namespace APP\payment\omp;

use APP\core\Application;
use APP\core\Request;
use APP\facades\Repo;
use Exception;
use PKP\db\DAORegistry;
use PKP\payment\CompletedPayment;
use PKP\payment\PaymentManager;
use PKP\payment\QueuedPayment;
use PKP\payment\QueuedPaymentDAO;
use PKP\plugins\PaymethodPlugin;
use PKP\plugins\PluginRegistry;
use PKP\submissionFile\SubmissionFile;

class OMPPaymentManager extends PaymentManager
{
    public const PAYMENT_TYPE_PURCHASE_FILE = 1;

    /**
     * Determine whether the payment system is configured.
     *
     * @return bool true iff configured
     */
    public function isConfigured()
    {
        return parent::isConfigured() && $this->_context && $this->_context->getData('currency');
    }

    /**
     * Create a queued payment.
     *
     * @param Request $request
     * @param int $type PAYMENT_TYPE_...
     * @param int $userId ID of user responsible for payment
     * @param int $assocId ID of associated entity
     * @param float $amount Amount of currency $currencyCode
     * @param string $currencyCode optional ISO 4217 currency code
     *
     * @return QueuedPayment
     */
    public function createQueuedPayment($request, $type, $userId, $assocId, $amount, $currencyCode = null)
    {
        $payment = new QueuedPayment($amount, $this->_context->getData('currency'), $userId, $assocId);
        $payment->setContextId($this->_context->getId());
        $payment->setType($type);

        switch ($type) {
            case self::PAYMENT_TYPE_PURCHASE_FILE:
                $submissionFile = Repo::submissionFile()->get($assocId);
                if ($submissionFile->getData('fileStage') != SubmissionFile::SUBMISSION_FILE_PROOF) {
                    throw new Exception('The submission file for this queued payment is not in the correct file stage.');
                }
                assert($submissionFile);
                $payment->setRequestUrl($request->url(null, 'catalog', 'view', [
                    $submissionFile->getData('submissionId'),
                    $submissionFile->getData('assocId'),
                    $assocId
                ]));
                break;
            default:
                // Invalid payment type.
                assert(false);
                break;
        }

        return $payment;
    }

    /**
     * Get the payment plugin.
     *
     * @return PaymethodPlugin
     */
    public function getPaymentPlugin()
    {
        $paymentMethodPluginName = $this->_context->getData('paymentPluginName');
        $paymentMethodPlugin = null;
        if (!empty($paymentMethodPluginName)) {
            $plugins = PluginRegistry::loadCategory('paymethod');
            if (isset($plugins[$paymentMethodPluginName])) {
                $paymentMethodPlugin = $plugins[$paymentMethodPluginName];
            }
        }
        return $paymentMethodPlugin;
    }

    /**
     * Fulfill a queued payment.
     *
     * @param Request $request
     * @param QueuedPayment $queuedPayment
     * @param string $payMethodPluginName Name of payment plugin.
     *
     * @return mixed Dependent on payment type.
     */
    public function fulfillQueuedPayment($request, $queuedPayment, $payMethodPluginName = null)
    {
        $returner = false;
        if ($queuedPayment) {
            switch ($queuedPayment->getType()) {
                case self::PAYMENT_TYPE_PURCHASE_FILE:
                    $returner = true;
                    break;
                default:
                    // Invalid payment type
                    assert(false);
            }
        }

        $ompCompletedPaymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO'); /** @var OMPCompletedPaymentDAO $ompCompletedPaymentDao */
        $completedPayment = $this->createCompletedPayment($queuedPayment, $payMethodPluginName);
        $ompCompletedPaymentDao->insertCompletedPayment($completedPayment);

        $queuedPaymentDao = DAORegistry::getDAO('QueuedPaymentDAO'); /** @var QueuedPaymentDAO $queuedPaymentDao */
        $queuedPaymentDao->deleteById($queuedPayment->getId());

        return $returner;
    }

    /**
     * Create a completed payment from a queued payment.
     *
     * @param QueuedPayment $queuedPayment Payment to complete.
     * @param string $payMethod Name of payment plugin used.
     *
     * @return CompletedPayment
     */
    public function createCompletedPayment($queuedPayment, $payMethod)
    {
        $payment = new CompletedPayment();
        $payment->setContextId($queuedPayment->getContextId());
        $payment->setType($queuedPayment->getType());
        $payment->setAmount($queuedPayment->getAmount());
        $payment->setCurrencyCode($queuedPayment->getCurrencyCode());
        $payment->setUserId($queuedPayment->getUserId());
        $payment->setAssocId($queuedPayment->getAssocId());
        $payment->setPayMethodPluginName($payMethod);

        return $payment;
    }

    /**
     * Returns the name of a payment.
     *
     * @return string
     */
    public function getPaymentName($payment)
    {
        switch ($payment->getType()) {
            case self::PAYMENT_TYPE_PURCHASE_FILE:
                $submissionFile = Repo::submissionFile()->get($payment->getAssocId());
                if (!$submissionFile || $submissionFile->getData('assocType') !== Application::ASSOC_TYPE_PUBLICATION_FORMAT) {
                    return false;
                }

                return $submissionFile->getLocalizedData('name');
            default:
                // Invalid payment type
                assert(false);
        }
    }
}
