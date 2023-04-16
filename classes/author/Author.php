<?php

/**
 * @file classes/author/Author.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class Author
 *
 * @ingroup monograph
 *
 * @see DAO
 *
 * @brief Monograph author metadata class.
 */

namespace APP\author;

class Author extends \PKP\author\Author
{
    /**
     * Get whether or not this author should be displayed as a volume editor
     *
     * @return bool
     */
    public function getIsVolumeEditor()
    {
        return $this->getData('isVolumeEditor');
    }

    /**
     * Set whether or not this author should be displayed as a volume editor
     *
     * @param bool $isVolumeEditor
     */
    public function setIsVolumeEditor($isVolumeEditor)
    {
        $this->setData('isVolumeEditor', $isVolumeEditor);
    }
}

if (!PKP_STRICT_MODE) {
    // Required for import/export toolset
    class_alias('\APP\author\Author', '\Author');
}
