<?php

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 */

import('lib.pkp.classes.user.form.PKPProfileForm');

class ProfileForm extends PKPProfileForm {
	/**
	 * Constructor.
	 */
	function ProfileForm($user) {
		parent::PKPProfileForm($user);
	}

	/**
	 * Save profile settings.
	 */
	function execute($request) {
		$user = $request->getUser();

		$notificationStatusDao = DAORegistry::getDAO('NotificationStatusDAO');
		$pressNotifications = $notificationStatusDao->getPressNotifications($user->getId());
		$readerNotify = $request->getUserVar('pressNotify');

		$pressDao = DAORegistry::getDAO('PressDAO');
		$presses = $pressDao->getAll();
		while ($thisPress = $presses->next()) {
			$thisPressId = $thisPress->getId();
			$currentlyReceives = !empty($pressNotifications[$thisPressId]);
			$shouldReceive = !empty($readerNotify) && in_array($thisPress->getId(), $readerNotify);
			if ($currentlyReceives != $shouldReceive) {
				$notificationStatusDao->setPressNotifications($thisPressId, $user->getId(), $shouldReceive);
			}
		}

		parent::execute($request);
	}
}

?>
