<?php
/**
 * @file classes/user/Repository.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Repository
 *
 * @brief A repository to find and manage users.
 */

namespace APP\user;

use APP\payment\omp\OMPCompletedPaymentDAO;
use PKP\db\DAORegistry;

class Repository extends \PKP\user\Repository
{
    /**
     * @copydoc \PKP\user\Repository::mergeUsers()
     */
    public function mergeUsers($oldUserId, $newUserId)
    {
        if (!parent::mergeUsers($oldUserId, $newUserId)) {
            return false;
        }

        // Transfer completed payments.
        $paymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO'); /** @var OMPCompletedPaymentDAO $paymentDao */
        $paymentFactory = $paymentDao->getByUserId($oldUserId);
        while ($payment = $paymentFactory->next()) {
            $payment->setUserId($newUserId);
            $paymentDao->updateObject($payment);
        }

        return true;
    }
}
