<?php

/**
 * @file classes/press/PressSettingsDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsDAO
 * @ingroup press
 *
 * @brief Operations for retrieving and modifying press settings.
 */

namespace APP\press;

use PKP\db\SettingsDAO;

class PressSettingsDAO extends SettingsDAO
{
    /**
     * Get the settings table name.
     *
     * @return string
     */
    protected function _getTableName()
    {
        return 'press_settings';
    }

    /**
     * Get the primary key column name.
     */
    protected function _getPrimaryKeyColumn()
    {
        return 'press_id';
    }

    /**
     * Get the cache name.
     */
    protected function _getCacheName()
    {
        return 'pressSettings';
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\PressSettingsDAO', '\PressSettingsDAO');
}
