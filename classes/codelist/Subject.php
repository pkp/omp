<?php

/**
 * @defgroup subject BIC Subjects
 */

/**
 * @file classes/codelist/Subject.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Subject
 *
 * @ingroup codelist
 *
 * @see SubjectDAO
 *
 * @brief Basic class describing a BIC Subject.
 *
 */

namespace APP\codelist;

class Subject extends CodelistItem
{
    /**
     * @var int The numerical representation of these Subject Qualifiers in ONIX 3.0
     */
    public $_onixSubjectSchemeIdentifier = 12;

    /**
     * Get the ONIX subject scheme identifier.
     *
     * @return string the numerical value representing this item in the ONIX 3.0 schema
     */
    public function getOnixSubjectSchemeIdentifier()
    {
        return $this->_onixSubjectSchemeIdentifier;
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\Subject', '\Subject');
}
