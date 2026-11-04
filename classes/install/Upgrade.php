<?php

/**
 * @file classes/install/Upgrade.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Upgrade
 *
 * @ingroup install
 *
 * @brief Perform system upgrade.
 */

namespace APP\install;

use PKP\install\Installer;

class Upgrade extends Installer
{
    protected $appEmailTemplateVariableNames = [
        'contextName' => 'pressName',
        'contextUrl' => 'pressUrl',
        'contextSignature' => 'pressSignature',
    ];

    /**
     * Constructor.
     *
     * @param array $params upgrade parameters
     */
    public function __construct($params, $installFile = 'upgrade.xml', $isPlugin = false)
    {
        parent::__construct($installFile, $params, $isPlugin);
    }


    /**
     * Returns true iff this is an upgrade process.
     *
     * @return bool
     */
    public function isUpgrade()
    {
        return true;
    }
}
