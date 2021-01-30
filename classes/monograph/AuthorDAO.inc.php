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
 * @see Author
 * @see ChapterDAO (uses AuthorDAO)
 *
 * @brief Operations for retrieving and modifying Author objects.
 */



import('classes.monograph.Author');
import('classes.submission.Submission');
import('lib.pkp.classes.submission.PKPAuthorDAO');

class AuthorDAO extends PKPAuthorDAO {
}

