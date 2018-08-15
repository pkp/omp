<?php

/**
 * @file plugins/importexport/native/filter/MonographNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Monograph to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.SubmissionNativeXmlFilter');

class MonographNativeXmlFilter extends SubmissionNativeXmlFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function __construct($filterGroup) {
		parent::__construct($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.MonographNativeXmlFilter';
	}


	//
	// Implement abstract methods from SubmissionNativeXmlFilter
	//
	/**
	 * Get the representation export filter group name
	 * @return string
	 */
	function getRepresentationExportFilterGroupName() {
		return 'publication-format=>native-xml';
	}

	//
	// Submission conversion functions
	//
	/**
	 * Create and return a submission node.
	 * @param $doc DOMDocument
	 * @param $submission Submission
	 * @return DOMElement
	 */
	function createSubmissionNode($doc, $submission) {
		$submissionNode = parent::createSubmissionNode($doc, $submission);

		// Add the series, if one is designated.
		if ($seriesId = $submission->getSeriesId()) {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getById($seriesId, $submission->getContextId());
			assert($series);
			$submissionNode->setAttribute('series', $series->getPath());
			$submissionNode->setAttribute('series_position', $submission->getSeriesPosition());
			$submissionNode->setAttribute('work_type', $submission->getWorkType());
		}

		return $submissionNode;
	}
}


