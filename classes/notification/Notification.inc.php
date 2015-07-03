<?php

/**
 * @file classes/notification/Notification.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Notification
 * @ingroup notification
 * @see NotificationDAO
 * @brief OMP subclass for Notifications (defines OMP-specific types and icons).
 */

import('lib.pkp.classes.notification.PKPNotification');

class Notification extends PKPNotification {
	/**
	 * Constructor.
	 */
	function Notification() {
		parent::PKPNotification();
	}
}

?>
