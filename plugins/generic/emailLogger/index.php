<?php

/**
 * @defgroup plugins_generic_emailLogger
 */

/**
 * @file plugins/generic/emailLogger/index.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_emailLogger
 * @brief Wrapper for email logger plugin.
 *
 */
require_once('plugins/generic/emailLogger/EmailLoggerPlugin.inc.php');

return new EmailLoggerPlugin();

?>
