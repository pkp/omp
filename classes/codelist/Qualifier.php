<?php

/**
 * @defgroup qualifier BIC Qualifiers
 */

/**
 * @file classes/codelist/Qualifier.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Qualifier
 *
 * @ingroup codelist
 *
 * @see QualifierDAO
 *
 * @brief Basic class describing a BIC Qualifier.
 *
 */

namespace APP\codelist;

class Qualifier extends CodelistItem
{
    /**
     * @var int The numerical representation of these Subject Qualifiers in ONIX 3.0
     */
    public $_onixSubjectSchemeIdentifier = 17;

    /**
     * @return string the numerical value representing this item in the ONIX 3.0 schema
     */
    public function getOnixSubjectSchemeIdentifier()
    {
        return $this->_onixSubjectSchemeIdentifier;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\Qualifier', '\Qualifier');
}
