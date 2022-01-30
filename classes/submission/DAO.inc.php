<?php
/**
 * @file classes/submission/DAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class submission
 *
 * @brief Read and write submissions to the database.
 */

namespace APP\submission;

use PKP\db\DAORegistry;

use PKP\observers\events\SubmissionDeleted;

class DAO extends \PKP\submission\DAO
{
    /** @copydoc SchemaDAO::$primaryTableColumns */
    public $primaryTableColumns = [
        'id' => 'submission_id',
        'contextId' => 'context_id',
        'currentPublicationId' => 'current_publication_id',
        'dateLastActivity' => 'date_last_activity',
        'dateSubmitted' => 'date_submitted',
        'lastModified' => 'last_modified',
        'locale' => 'locale',
        'stageId' => 'stage_id',
        'status' => 'status',
        'submissionProgress' => 'submission_progress',
        'workType' => 'work_type',
    ];

    /** @copydoc \PKP\submission\DAO::deleteById() */
    public function deleteById(int $id)
    {
        // Delete references to features or new releases.
        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featureDao->deleteByMonographId($id);

        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
        $newReleaseDao->deleteByMonographId($id);

        event(new SubmissionDeleted($this->get($id)));

        parent::deleteById($id);
    }
}
