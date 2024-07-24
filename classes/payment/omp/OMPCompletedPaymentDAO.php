<?php

/**
 * @file classes/payment/omp/OMPCompletedPaymentDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class OMPCompletedPaymentDAO
 *
 * @ingroup payment
 *
 * @see CompletedPayment, Payment
 *
 * @brief Operations for retrieving and querying past payments
 *
 */

namespace APP\payment\omp;

use PKP\core\Core;
use PKP\db\DAO;
use PKP\db\DAOResultFactory;
use PKP\db\DBResultRange;
use PKP\payment\CompletedPayment;

class OMPCompletedPaymentDAO extends DAO
{
    /**
     * Retrieve a CompletedPayment by its ID.
     *
     * @param int $completedPaymentId
     *
     * @return CompletedPayment|null
     */
    public function getById($completedPaymentId, ?int $contextId = null)
    {
        $params = [(int) $completedPaymentId];
        if ($contextId) {
            $params[] = (int) $contextId;
        }

        $result = $this->retrieve(
            'SELECT * FROM completed_payments WHERE completed_payment_id = ?' . ($contextId ? ' AND context_id = ?' : ''),
            $params
        );
        $row = $result->current();
        return $row ? $this->_fromRow((array) $row) : null;
    }

    /**
     * Insert a new completed payment.
     *
     * @param CompletedPayment $completedPayment
     */
    public function insertCompletedPayment($completedPayment)
    {
        $this->update(
            sprintf(
                'INSERT INTO completed_payments
				(timestamp, payment_type, context_id, user_id, assoc_id, amount, currency_code_alpha, payment_method_plugin_name)
				VALUES
				(%s, ?, ?, ?, ?, ?, ?, ?)',
                $this->datetimeToDB(Core::getCurrentDate())
            ),
            [
                (int) $completedPayment->getType(),
                (int) $completedPayment->getContextId(),
                (int) $completedPayment->getUserId(),
                $completedPayment->getAssocId(), /* NOT int */
                $completedPayment->getAmount(),
                $completedPayment->getCurrencyCode(),
                $completedPayment->getPayMethodPluginName()
            ]
        );

        return $this->getInsertId();
    }

    /**
     * Update an existing completed payment.
     *
     * @param CompletedPayment $completedPayment
     */
    public function updateObject($completedPayment)
    {
        $returner = false;

        $this->update(
            sprintf(
                'UPDATE completed_payments
			SET
				timestamp = %s,
				payment_type = ?,
				context_id = ?,
				user_id = ?,
				assoc_id = ?,
				amount = ?,
				currency_code_alpha = ?,
				payment_method_plugin_name = ?
			WHERE completed_payment_id = ?',
                $this->datetimeToDB($completedPayment->getTimestamp())
            ),
            [
                (int) $completedPayment->getType(),
                (int) $completedPayment->getContextId(),
                (int) $completedPayment->getUserId(),
                (int) $completedPayment->getAssocId(),
                $completedPayment->getAmount(),
                $completedPayment->getCurrencyCode(),
                $completedPayment->getPayMethodPluginName(),
                (int) $completedPayment->getId()
            ]
        );
    }

    /**
     * Look for a completed PURCHASE_PUBLICATION_FORMAT payment matching the article ID
     *
     * @param int $userId
     * @param int $submissionFileId
     *
     * @return bool
     */
    public function hasPaidPurchaseFile($userId, $submissionFileId)
    {
        $result = $this->retrieve(
            'SELECT count(*) AS row_count FROM completed_payments WHERE payment_type = ? AND user_id = ? AND assoc_id = ?',
            [
                OMPPaymentManager::PAYMENT_TYPE_PURCHASE_FILE,
                (int) $userId,
                $submissionFileId
            ]
        );
        $row = $result->current();
        return $row ? (bool) $row->row_count : false;
    }

    /**
     * Retrieve an array of payments for a particular context ID.
     *
     * @param DBResultRange|null $rangeInfo
     *
     * @return DAOResultFactory<CompletedPayment> containing matching payments
     */
    public function getByContextId(int $contextId, $rangeInfo = null)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM completed_payments WHERE context_id = ? ORDER BY timestamp DESC',
                [(int) $contextId],
                $rangeInfo
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Retrieve an array of payments for a particular user ID.
     *
     * @param int $userId
     * @param DBResultRange|null $rangeInfo
     *
     * @return DAOResultFactory<CompletedPayment> Matching payments
     */
    public function getByUserId($userId, $rangeInfo = null)
    {
        return new DAOResultFactory(
            $this->retrieveRange(
                'SELECT * FROM completed_payments WHERE user_id = ? ORDER BY timestamp DESC',
                [(int) $userId],
                $rangeInfo
            ),
            $this,
            '_fromRow'
        );
    }

    /**
     * Return a new data object.
     *
     * @return CompletedPayment
     */
    public function newDataObject()
    {
        return new CompletedPayment();
    }

    /**
     * Internal function to return a CompletedPayment object from a row.
     *
     * @param array $row
     *
     * @return CompletedPayment
     */
    public function _fromRow($row)
    {
        $payment = $this->newDataObject();
        $payment->setTimestamp($this->datetimeFromDB($row['timestamp']));
        $payment->setId($row['completed_payment_id']);
        $payment->setType($row['payment_type']);
        $payment->setContextId($row['context_id']);
        $payment->setAmount($row['amount']);
        $payment->setCurrencyCode($row['currency_code_alpha']);
        $payment->setUserId($row['user_id']);
        $payment->setAssocId($row['assoc_id']);
        $payment->setPayMethodPluginName($row['payment_method_plugin_name']);

        return $payment;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\payment\omp\OMPCompletedPaymentDAO', '\OMPCompletedPaymentDAO');
}
