<?php
/**
 * @file classes/submissionFile/Repository.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submissionFile
 *
 * @brief A repository to find and manage submission files.
 */

namespace APP\submissionFile;

use APP\core\Request;
use PKP\services\PKPSchemaService;
use PKP\submissionFile\Repository as SubmissionFileRepository;

class Repository extends SubmissionFileRepository
{
    /** @copydoc \PKP\submissionFile\Repository::$schemaMap */
    public $schemaMap = maps\Schema::class;

    public function __construct(
        DAO $dao,
        Request $request,
        PKPSchemaService $schemaService
    ) {
        $this->schemaService = $schemaService;
        $this->dao = $dao;
        $this->request = $request;
    }
}
