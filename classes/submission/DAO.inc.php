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

use APP\core\Application;

use PKP\db\DAORegistry;

class DAO extends \PKP\submission\DAO
{
    /** @copydoc \PKP\submission\DAO::deleteById() */
    public function deleteById(int $id)
    {
        // Delete references to features or new releases.
        $featureDao = DAORegistry::getDAO('FeatureDAO'); /** @var FeatureDAO $featureDao */
        $featureDao->deleteByMonographId($id);

        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO'); /** @var NewReleaseDAO $newReleaseDao */
        $newReleaseDao->deleteByMonographId($id);

        $monographSearchIndex = Application::getSubmissionSearchIndex();
        $monographSearchIndex->deleteTextIndex($id);
        $monographSearchIndex->submissionChangesFinished();

        parent::deleteById($id);
    }
}
