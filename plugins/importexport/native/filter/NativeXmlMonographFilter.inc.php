<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFilter.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a set of monographs.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlSubmissionFilter');

class NativeXmlMonographFilter extends NativeXmlSubmissionFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function NativeXmlMonographFilter($filterGroup) {
		parent::NativeXmlSubmissionFilter($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlMonographFilter';
	}

	/**
	 * Get the published submission DAO for this application.
	 * @return DAO
	 */
	function getPublishedSubmissionDAO() {
		return DAORegistry::getDAO('PublishedMonographDAO');
	}

	/**
	 * Get the method name for inserting a published submission.
	 * @return string
	 */
	function getPublishedSubmissionInsertMethod(){
		return 'insertObject';
	}

	/**
	 * Populate the submission object from the node
	 * @param $submission Submission
	 * @param $node DOMElement
	 * @return Submission
	 */
	function populateObject($submission, $node) {
		$seriesPath = $node->getAttribute('series');
		$seriesPosition = $node->getAttribute('series_position');
		if ($seriesPath !== '') {
			$seriesDao = DAORegistry::getDAO('SeriesDAO');
			$series = $seriesDao->getByPath($seriesPath, $submission->getContextId());
			if (!$series) {
				fatalError('Could not find a series with the path "' . $seriesPath . '"!');
			}
			$submission->setSeriesId($series->getId());
			$submission->setSeriesPosition($seriesPosition);
		}
		$workType = $node->getAttribute('work_type');
		$submission->setWorkType($workType);
		return parent::populateObject($submission, $node);
	}

	/**
	 * Handle an element whose parent is the submission element.
	 * @param $n DOMElement
	 * @param $submission Submission
	 */
	function handleChildElement($n, $submission) {
		switch ($n->tagName) {
			case 'artwork_file':
				$this->parseSubmissionFile($n, $submission);
				break;
			case 'publication_format':
				$this->parsePublicationFormat($n, $submission);
				break;
			default:
				parent::handleChildElement($n, $submission);
		}
	}

	/**
	 * Get the import filter for a given element.
	 * @param $elementName string Name of XML element
	 * @return Filter
	 */
	function getImportFilter($elementName) {
		$importClass = null; // Scrutinizer
		switch ($elementName) {
			case 'submission_file':
				$importClass='SubmissionFile';
				break;
			case 'artwork_file':
				$importClass='ArtworkFile';
				break;
			case 'publication_format':
				$importClass='PublicationFormat';
				break;
			default:
				fatalError('Unknown submission file node ' . $elementName);
		}
		// Caps on class name for consistency with imports, whose filter
		// group names are generated implicitly.
		$filterDao = DAORegistry::getDAO('FilterDAO');
		$importFilters = $filterDao->getObjectsByGroup('native-xml=>' . $importClass);
		$importFilter = array_shift($importFilters);
		return $importFilter;
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $submission Submission
	 */
	function parsePublicationFormat($n, $submission) {
		$importFilter = $this->getImportFilter($n->tagName);
		assert($importFilter); // There should be a filter

		$existingDeployment = $this->getDeployment();
		$onixDeployment = new Onix30ExportDeployment(Request::getContext(), Request::getUser());
		$onixDeployment->setSubmission($existingDeployment->getSubmission());
		$importFilter->setDeployment($onixDeployment);
		$formatDoc = new DOMDocument();
		$formatDoc->appendChild($formatDoc->importNode($n, true));
		return $importFilter->execute($formatDoc);
	}

	/**
	 * Get the representation export filter group name
	 * @return string
	 */
	function getRepresentationExportFilterGroupName() {
		return 'publication-format=>native-xml';
	}

	/**
	 * Class-specific methods for published submissions.
	 * @param PublishedMonograph $submission
	 * @param DOMElement $node
	 * @return PublishedMonograph
	 */
	function populatePublishedSubmission($submission, $node) {
		return $submission;
	}
}

?>
