<?php

/**
 * @defgroup press Press
 */

/**
 * @file classes/press/Press.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Press
 *
 * @ingroup press
 *
 * @see PressDAO
 *
 * @brief Basic class describing a press.
 */

namespace APP\press;

use APP\core\Application;
use PKP\context\Context;
use PKP\db\DAORegistry;

class Press extends Context
{
    /**
     * Returns true if this press contains the fields required for creating valid
     * ONIX export metadata.
     */
    public function hasRequiredOnixHeaderFields(): bool
    {
        return ($this->getData('codeType') != '' && $this->getData('codeValue') != '');
    }

    /**
     * Get the association type for this context.
     */
    public function getAssocType(): int
    {
        return Application::ASSOC_TYPE_PRESS;
    }

    /**
     * Get the DAO for this context object.
     */
    public function getDAO(): PressDAO
    {
        return DAORegistry::getDAO('PressDAO');
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\Press', '\Press');
}
