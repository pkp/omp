<?php

/**
 * @file classes/codelist/ONIXCodelistItem.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ONIXCodelistItem
 * @ingroup codelist
 *
 * @see ONIXCodelistItemDAO
 *
 * @brief Basic class describing a codelist item.
 *
 */

namespace APP\codelist;

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
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\codelist\ONIXCodelistItem', '\ONIXCodelistItem');
}
