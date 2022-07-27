<?php

/**
 * @file classes/submission/SubmissionMetadataFormImplementation.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionMetadataFormImplementation
 * @ingroup submission
 *
 * @brief This can be used by other forms that want to
 * implement submission metadata data and form operations.
 */

namespace APP\submission;

use PKP\db\DAORegistry;
use PKP\submission\PKPSubmissionMetadataFormImplementation;

class SubmissionMetadataFormImplementation extends PKPSubmissionMetadataFormImplementation
{
    /**
     * Initialize form data from current submission.
     *
     * @param Submission $submission
     */
    public function initData($submission)
    {
        parent::initData($submission);
        $seriesDao = DAORegistry::getDAO('SeriesDAO'); /** @var SeriesDAO $seriesDao */
        if (isset($submission)) {
            $this->_parentForm->setData('series', $seriesDao->getById($submission->getSeriesId()));
        }
    }
}

if (!PKP_STRICT_MODE) {
    class_alias('\APP\submission\SubmissionMetadataFormImplementation', '\SubmissionMetadataFormImplementation');
}
