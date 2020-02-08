<?php

/**
 * @file classes/notification/Notification.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
	function __construct() {
		parent::__construct();
	}
}


