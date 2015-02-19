<?php

/**
 * @file classes/user/form/RegistrationForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class RegistrationForm
 * @ingroup user_form
 *
 * @brief Form for user registration.
 */

import('lib.pkp.classes.user.form.PKPRegistrationForm');

class RegistrationForm extends PKPRegistrationForm {
	/**
	 * Constructor.
	 */
	function RegistrationForm($site, $existingUser = false) {
		parent::PKPRegistrationForm($site, $existingUser);
	}

	/**
	 * Register a new user.
	 */
	function execute($request) {
		parent::execute($request);

		// By default, self-registering readers will receive
		// context updates. (The double set is here to prevent a
		// duplicate insert error msg if there was a notification entry
		// left over from a previous role.)
		if (isset($allowedRoles['reader']) && $this->getData($allowedRoles['reader'])) {
			$notificationStatusDao = DAORegistry::getDAO('NotificationStatusDAO');
			$notificationStatusDao->setPressNotifications($context->getId(), $userId, false);
			$notificationStatusDao->setPressNotifications($context->getId(), $userId, true);
		}
	}
}

?>
