<?php

/**
 * @file classes/submission/SubmissionFileDAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionFileDAO
 * @ingroup submission
 * @see SubmissionFile
 *
 * @brief Operations for retrieving and modifying submission files
 */
use Illuminate\Database\Capsule\Manager as Capsule;

import('lib.pkp.classes.submission.PKPSubmissionFileDAO');

class SubmissionFileDAO extends PKPSubmissionFileDAO {

	/** @copydoc SchemaDAO::$primaryTableColumns */
	public $primaryTableColumns = [
		'assocId' => 'assoc_id',
		'assocType' => 'assoc_type',
		'createdAt' => 'created_at',
		'directSalesPrice' => 'direct_sales_price',
		'fileId' => 'file_id',
		'fileStage' => 'file_stage',
		'genreId' => 'genre_id',
		'id' => 'submission_file_id',
		'salesType' => 'sales_type',
		'sourceSubmissionFileId' => 'source_submission_file_id',
		'submissionId' => 'submission_id',
		'updatedAt' => 'updated_at',
		'uploaderUserId' => 'uploader_user_id',
		'viewable' => 'viewable',
	];

	/**
	 * Update the files associated with a chapter
	 *
	 * @param array $submissionFileIds
	 * @param int $chapterId
	 */
	public function updateChapterFiles($submissionFileIds, $chapterId) {
		Capsule::table('submission_file_settings')
			->where('setting_name', '=', 'chapterId')
			->where('setting_value', '=', $chapterId)
			->delete();

		if (!empty($submissionFileIds)) {
			$insertRows = array_map(function($submissionFileId) use ($chapterId) {
				return [
					'submission_file_id' => $submissionFileId,
					'setting_name' => 'chapterId',
					'setting_value' => $chapterId,
				];
			}, $submissionFileIds);
			Capsule::table('submission_file_settings')->insert($insertRows);
		}
	}
}
