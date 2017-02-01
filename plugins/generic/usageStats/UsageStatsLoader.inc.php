<?php

/**
 * @file plugins/generic/usageStats/UsageStatsLoader.php
 *
 * Copyright (c) 2013-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
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
	function __construct($args) {
		parent::__construct($args);
	}


	//
	// Protected methods.
	//
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
		list($assocTypeToReturn, $assocId) = parent::getAssoc($assocType, $contextPaths, $page, $op, $args);

		if (!$assocId && !$assocTypeToReturn) {
			switch ($assocType) {
				case ASSOC_TYPE_SUBMISSION_FILE:
					if (!isset($args[0])) break;
					$submissionId = $args[0];
					$submissionDao = DAORegistry::getDAO('MonographDAO');
					$monograph = $submissionDao->getById($submissionId);
					if (!$monograph) break;

					if (!isset($args[2])) break;
					$fileIdAndRevision = $args[2];
					list($fileId, $revision) = array_map(create_function('$a', 'return (int) $a;'), preg_split('/-/', $fileIdAndRevision));

					$monographFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $monographFileDao SubmissionFileDAO */
					$monographFile = $monographFileDao->getRevision($fileId, $revision);
					if ($monographFile) {
						$assocId = $monographFile->getFileId();
					}

					$assocTypeToReturn = $assocType;
					break;
				case ASSOC_TYPE_SERIES:
					if (!isset($args[0])) break;
					$seriesPath = $args[0];
					$seriesDao = Application::getSectionDAO(); /* @var $seriesDao SeriesDAO */
					if (isset($this->_contextsByPath[current($contextPaths)])) {
						$context =  $this->_contextsByPath[current($contextPaths)];
						$series = $seriesDao->getByPath($seriesPath, $context->getId());
						if ($series) {
							$assocId = $series->getId();
						}
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
