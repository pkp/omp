<?php

/**
 * @file classes/user/UserAction.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserAction
 * @ingroup user
 * @see User
 *
 * @brief UserAction class.
 */

import('lib.pkp.classes.user.PKPUserAction');

class UserAction extends PKPUserAction {
	/**
	 * @copydoc PKPUserAction::mergeUsers()
	 */
	function mergeUsers($oldUserId, $newUserId) {
		if (!parent::mergeUsers($oldUserId, $newUserId)) return false;

		// Transfer completed payments.
		$paymentDao = DAORegistry::getDAO('OMPCompletedPaymentDAO');
		$paymentFactory = $paymentDao->getByUserId($oldUserId);
		while ($payment = $paymentFactory->next()) {
			$payment->setUserId($newUserId);
			$paymentDao->updateObject($payment);
		}

		return true;
	}
}

