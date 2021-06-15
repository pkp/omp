<?php

/**
 * @file classes/monograph/AuthorDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class AuthorDAO
 * @ingroup monograph
 *
 * @see Author
 * @see ChapterDAO (uses AuthorDAO)
 *
 * @brief Operations for retrieving and modifying Author objects.
 */

namespace APP\monograph;

use PKP\submission\PKPAuthorDAO;

class AuthorDAO extends PKPAuthorDAO
{
    /**
     * Get a new data object
     *
     * @return DataObject
     */
    public function newDataObject()
    {
        return new Author();
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\monograph\AuthorDAO', '\AuthorDAO');
}
