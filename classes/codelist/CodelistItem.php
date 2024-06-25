<?php

/**
 * @defgroup codelist ONIX code lists
 */

/**
 * @file classes/codelist/CodelistItem.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CodelistItem
 *
 * @see CodelistItemDAO
 *
 * @brief Basic class describing a codelist item.
 *
 */

namespace APP\codelist;

use PKP\core\DataObject;

abstract class CodelistItem extends DataObject
{
    //
    // Get/set methods
    //
    /**
     * Get the text component of the codelist.
     *
     */
    public function getText(): string
    {
        return $this->getData('text');
    }

    /**
     * Set the text component of the codelist.
     */
    public function setText(string $text): void
    {
        return $this->setData('text', $text);
    }

    /**
     * Get codelist code.
     */
    public function getCode(): string
    {
        return $this->getData('code');
    }

    /**
     * Set codelist code.
     */
    public function setCode(string $code)
    {
        return $this->setData('code', $code);
    }

    /**
     * Get the numerical value representing this item in the ONIX 3.0 schema
     */
    abstract public function getOnixSubjectSchemeIdentifier(): int;
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\CodelistItem', '\CodelistItem');
}
