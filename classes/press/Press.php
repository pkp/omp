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
     *
     * @return bool
     */
    public function hasRequiredOnixHeaderFields()
    {
        if ($this->getData('codeType') != '' && $this->getData('codeValue') != '') {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the association type for this context.
     *
     * @return int
     */
    public function getAssocType()
    {
        return Application::ASSOC_TYPE_PRESS;
    }

    /**
     * Get the DAO for this context object.
     *
     * @return PressDAO
     */
    public function getDAO()
    {
        /** @var PressDAO */
        $dao = DAORegistry::getDAO('PressDAO');
        return $dao;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\press\Press', '\Press');
}
