<?php

/**
 * @file plugins/generic/usageStats/UsageStatsLoader.php
 *
 * Copyright (c) 2013 Simon Fraser University Library
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsLoader
 * @ingroup plugins_generic_usageStats
 *
 * @brief Scheduled task to extract transform and load usage statistics data into database.
 */

import('lib.pkp.plugins.generic.usageStats.PKPUsageStatsLoader');

class UsageStatsLoader extends PKPUsageStatsLoader {

	/**
	 * Constructor.
	 */
	function UsageStatsLoader($args) {
		parent::PKPUsageStatsLoader($args);
	}


	//
	// Protected methods.
	//
	/**
	 * @see PKPUsageStatsLoader::getFileType($assocType, $assocId)
	 */
	protected function getFileType($assocType, $assocId) {
		// Check downloaded file type, if any.
		$file = null;
		$type = null;
		if ($assocType == ASSOC_TYPE_SUBMISSION_FILE) {
			$monographFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $monographFileDao SubmissionFileDAO */
			$file = $monographFileDao->getLatestRevision($assocId);
		}

		if ($file) {
			$fileType = $file->getFileType();
			if ($fileType == 'pdf') {
				$type = STATISTICS_FILE_TYPE_PDF;
			} else if ($fileType == 'html') {
				$type = STATISTICS_FILE_TYPE_HTML;
			} else {
				$type = STATISTICS_FILE_TYPE_OTHER;
			}
		}

		return $type;
	}

	/**
	 * @see PKPUsageStatsLoader::getExpectedPageAndOp()
	 */
	protected function getExpectedPageAndOp() {
		$pageAndOp = parent::getExpectedPageAndOp();

		$pageAndOp = $pageAndOp + array(
			ASSOC_TYPE_SUBMISSION_FILE => array(
				'catalog/download'),
			ASSOC_TYPE_MONOGRAPH => array(
				'catalog/book'),
			ASSOC_TYPE_SERIES => array(
				'catalog/series')
		);

		$pageAndOp[Application::getContextAssocType()][] = 'catalog/index';

		return $pageAndOp;
	}

	/**
	 * @see PKPUsageStatsLoader::getAssoc()
	 */
	protected function getAssoc($assocType, $contextPaths, $page, $op, $args) {
		list($assocType, $assocId) = parent::getAssoc($assocType, $contextPaths, $page, $op, $args);

		if (!$assocId && !$assocType) {
			if ($assocType == ASSOC_TYPE_SUBMISSION_FILE) {
				if (!isset($args[0])) break;
				$submissionId = $args[0];
				$submissionDao = DAORegistry::getDAO('MonographDAO');
				$monograph = $monographDao->getById($submissionId);
				if (!$monograph) break;

				if (!isset($args[1])) break;
				$fileId = $args[1];

				$monographFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $monographFileDao SubmissionFileDAO */
				$monographFile = $monographFileDao->getLatestRevision($fileId);
				if ($monographFile) {
					$assocId = $monographFile->getId();
				}
				break;
			}
		}

		return array($assocId, $assocType);
	}

	/**
	 * @see PKPUsageStatsLoader::getMetricType()
	 */
	protected function getMetricType() {
		return OMP_METRIC_TYPE_COUNTER;
	}

}
?>
