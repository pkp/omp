<?php

/**
 * @defgroup codelist ONIX code lists
 */

/**
 * @file classes/codelist/CodelistItem.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CodelistItem
 * @ingroup codelist
 *
 * @see CodelistItemDAO
 *
 * @brief Basic class describing a codelist item.
 *
 */

namespace APP\codelist;

use PKP\core\DataObject;

class CodelistItem extends DataObject
{
    //
    // Get/set methods
    //
    /**
     * Get the text component of the codelist.
     *
     * @return string
     */
    public function getText()
    {
        return $this->getData('text');
    }

    /**
     * Set the text component of the codelist.
     *
     * @param string $text
     */
    public function setText($text)
    {
        return $this->setData('text', $text);
    }

    /**
     * Get codelist code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * Set codelist code.
     *
     * @param string $code
     */
    public function setCode($code)
    {
        return $this->setData('code', $code);
    }

    /**
     * @return string the numerical value representing this item in the ONIX 3.0 schema
     */
    public function getOnixSubjectSchemeIdentifier()
    {
        assert(false); // provided by subclasses
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\CodelistItem', '\CodelistItem');
}
