<?php
/**
 * @file classes/services/SubmissionFileService.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileService
 * @ingroup services
 *
 * @brief Submission file service methods for OMP.
 */
namespace APP\Services;

use \HookRegistry;

class SubmissionFileService extends \PKP\Services\PKPSubmissionFileService {

	/**
	 * Get all valid file stages
	 *
	 * @return array
	 */
	public function getFileStages() {
		import('lib.pkp.classes.submission.SubmissionFile');
		$stages = [
			SUBMISSION_FILE_SUBMISSION,
			SUBMISSION_FILE_NOTE,
			SUBMISSION_FILE_INTERNAL_REVIEW_FILE,
			SUBMISSION_FILE_REVIEW_FILE,
			SUBMISSION_FILE_REVIEW_ATTACHMENT,
			SUBMISSION_FILE_FINAL,
			SUBMISSION_FILE_COPYEDIT,
			SUBMISSION_FILE_PROOF,
			SUBMISSION_FILE_PRODUCTION_READY,
			SUBMISSION_FILE_ATTACHMENT,
			SUBMISSION_FILE_REVIEW_REVISION,
			SUBMISSION_FILE_INTERNAL_REVIEW_REVISION,
			SUBMISSION_FILE_DEPENDENT,
			SUBMISSION_FILE_QUERY,
		];

		HookRegistry::call('SubmissionFile::fileStages', [&$stages]);

		return $stages;
	}
}