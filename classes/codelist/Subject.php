<?php

/**
 * @defgroup subject BIC Subjects
 */

/**
 * @file classes/codelist/Subject.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Subject
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
     * @var The numerical representation of these Subject Qualifiers in ONIX 3.0
     */
    public int $_onixSubjectSchemeIdentifier = 12;

    /**
     * Get the ONIX subject scheme identifier.
     */
    public function getOnixSubjectSchemeIdentifier(): int
    {
        return $this->_onixSubjectSchemeIdentifier;
    }
}
