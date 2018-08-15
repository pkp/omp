<?php

/**
 * @file plugins/generic/acron/AcronPlugin.inc.php
 *
 * Copyright (c) 2013-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AcronPlugin
 * @ingroup plugins_generic_acron
 *
 * @brief Removes dependency on 'cron' for scheduled tasks, including
 * possible tasks defined by plugins. See the AcronPlugin::parseCrontab
 * hook implementation.
 */

import('lib.pkp.plugins.generic.acron.PKPAcronPlugin');

class AcronPlugin extends PKPAcronPlugin {


}

