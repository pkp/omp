<?php

/**
 * @file plugins/generic/emailLogger/EmailLoggerPlugin.inc.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EmailLoggerPlugin
 * @ingroup plugins_generic_emailLogger
 *
 * @brief Implement app specifics for the Email Logger plugin.
 */

import('lib.pkp.plugins.generic.emailLogger.PKPEmailLoggerPlugin');

class EmailLoggerPlugin extends PKPEmailLoggerPlugin {

	/**
	 * @copydoc PKPEmailLoggerPlugin::existsByAssoc()
	 */
	function existsByAssoc($assocType, $assocId, $recipientEmail = null, $eventType = null, $bodyText = null) {
		if ($assocType == ASSOC_TYPE_SUBMISSION_FILE) {
			$this->emailLogEntryDao = DAORegistry::getDAO('MonographFileEmailLogDAO');
		}

		return parent::existsByAssoc($assocType, $assocId, $recipientEmail, $eventType, $bodyText);
	}
}
