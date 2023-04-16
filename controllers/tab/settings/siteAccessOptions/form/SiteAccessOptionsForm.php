<?php

/**
 * @file controllers/tab/settings/siteAccessOptions/form/SiteAccessOptionsForm.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SiteAccessOptionsForm
 *
 * @ingroup controllers_tab_settings_siteAccessOptions_form
 *
 * @brief Form to edit site access options.
 */

namespace APP\controllers\tab\settings\siteAccessOptions\form;

use PKP\controllers\tab\settings\form\ContextSettingsForm;

class SiteAccessOptionsForm extends ContextSettingsForm
{
    /**
     * Constructor.
     */
    public function __construct($wizardMode = false)
    {
        $settings = [
            'disableUserReg' => 'bool',
            'restrictSiteAccess' => 'bool',
            'restrictMonographAccess' => 'bool',
        ];

        parent::__construct($settings, 'controllers/tab/settings/siteAccessOptions/form/siteAccessOptionsForm.tpl', $wizardMode);
    }
}
