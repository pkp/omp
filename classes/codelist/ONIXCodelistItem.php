<?php

/**
 * @file classes/codelist/ONIXCodelistItem.php
 *
 * Copyright (c) 2014-2024 Simon Fraser University
 * Copyright (c) 2000-2024 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ONIXCodelistItem
 *
 * @see ONIXCodelistItemDAO
 *
 * @brief Basic class describing a codelist item.
 *
 */

namespace APP\codelist;

use PKP\core\DataObject;

class ONIXCodelistItem extends DataObject
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
    public function setText($text): void
    {
        $this->setData('text', $text);
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
    public function setCode($code): void
    {
        $this->setData('code', $code);
    }
}
