<?php

/**
 * @file classes/notification/Notification.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Notification
 * @ingroup notification
 * @see NotificationDAO
 * @brief OMP subclass for Notifications (defines OMP-specific types and icons).
 */


/** Notification associative types. */
define('NOTIFICATION_TYPE_MONOGRAPH_SUBMITTED',					0x1000001);
define('NOTIFICATION_TYPE_METADATA_MODIFIED',					0x1000002);
define('NOTIFICATION_TYPE_REVIEWER_COMMENT',					0x1000003);
define('NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION',		0x1000004);
define('NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW',	0x1000005);
define('NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW',	0x1000006);
define('NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING',			0x1000007);
define('NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION',		0x1000008);
define('NOTIFICATION_TYPE_AUDITOR_REQUEST',						0x1000009);
define('NOTIFICATION_TYPE_COPYEDIT_SIGNOFF',					0x100000A);
define('NOTIFICATION_TYPE_REVIEW_ASSIGNMENT',					0x100000B);

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
