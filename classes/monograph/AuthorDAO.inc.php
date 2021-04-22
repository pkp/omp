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

use \PKP\submission\PKPAuthorDAO;

import('classes.monograph.Author');

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
