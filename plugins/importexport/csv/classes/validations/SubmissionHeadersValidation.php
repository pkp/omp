<?php

/**
 * @file plugins/importexport/csv/classes/validations/SubmissionHeadersValidation.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHeadersValidation
 * @ingroup plugins_importexport_csv
 *
 * @brief A class to validate headers in the submission CSV files.
 */

namespace APP\plugins\importexport\csv\classes\validations;

class SubmissionHeadersValidation
{
    /**
	 * The expected headers coming from the CSV file, in their respective order
	 *
	 * @var string[]
	 */
	public static $expectedHeaders = [
		'pressPath',
		'authorString',
		'title',
		'abstract',
		'seriesPath',
		'year',
		'isEditedVolume',
		'locale',
		'filename',
		'doi',
		'keywords',
		'subjects',
		'bookCoverImage',
		'bookCoverImageAltText',
		'categories',
		'genreName',
	];

    public static $requiredHeaders = [
        'pressPath',
        'authorString',
        'title',
        'abstract',
        'locale',
        'filename',
    ];
}

