<?php

/**
 * @file plugins/importexport/csv/classes/handlers/CSVFileHandler.php
 *
 * Copyright (c) 2013-2025 Simon Fraser University
 * Copyright (c) 2003-2025 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class CSVFileHandler
 * @ingroup plugins_importexport_csv
 *
 * @brief A CSV file handler with static methods for working with CSV files.
 */

namespace APP\plugins\importexport\csv\classes\handlers;

use SplFileObject;
use Exception;

class CSVFileHandler {

    /**
	 * The expected headers coming from the CSV file, in their respective order
	 *
	 * @var string[]
	 */
	private static $expectedHeaders = [
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

    /**
	 * The expected size for a valid Submission row on CSV file
	 */
	private static int $expectedRowSize;

	public static function createAndValidateCSVFile(string $filename): SplFileObject
    {
		$file = self::createNewFile($filename, 'r');
		$file->setFlags(SplFileObject::READ_CSV);



		$headers = $file->fgetcsv();

		$missingHeaders = array_diff(self::$expectedHeaders, $headers);

		if (count($missingHeaders)) {
			echo __('plugin.importexport.csv.missingHeadersOnCsv', ['missingHeaders' => $missingHeaders]);
			exit(1);
		}

		return $file;
	}

    public static function createInvalidCSVFile(string $csvForInvalidRowsName): SplFileObject
    {
        $file = self::createNewFile($csvForInvalidRowsName, 'w');
        $file->fputcsv(array_merge(self::$expectedHeaders, ['error']));

        return $file;
    }

    public static function processInvalidRows(array $data, string $reason, SplFileObject &$invalidRowsFile, int &$failedRowsCount): void
    {
        $invalidRowsFile->fputcsv(array_merge($data, [$reason]));
        $failedRowsCount++;
    }


	private static function createNewFile(string $filename, string $mode): SplFileObject
    {
		try {
			return new SplFileObject($filename, $mode);
		} catch (Exception $e) {
			echo $e->getMessage();
			exit(1);
		}
	}
}
