<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlPublicationFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlPublicationFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a set of monographs.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlPKPPublicationFilter');

class NativeXmlPublicationFilter extends NativeXmlPKPPublicationFilter {
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
		return 'plugins.importexport.native.filter.NativeXmlPublicationFilter';
	}

	/**
	 * @see Filter::process()
	 * @param $document DOMDocument|string
	 * @return array Array of imported documents
	 */
	function &process(&$document) {
		$importedObjects =& parent::process($document);

		// Index imported content
		// $monographSearchIndex = Application::getSubmissionSearchIndex();
		// foreach ($importedObjects as $submission) {
		// 	assert(is_a($submission, 'Submission'));
		// 	$monographSearchIndex->submissionMetadataChanged($submission);
		// 	$monographSearchIndex->submissionFilesChanged($submission);
		// }
		// $monographSearchIndex->submissionChangesFinished();

		return $importedObjects;
	}

	/**
	 * Populate the submission object from the node
	 * @param $publication Publication
	 * @param $node DOMElement
	 * @return Publication
	 */
	function populateObject($publication, $node) {
		$deployment = $this->getDeployment();
		$context = $deployment->getContext();

		$seriesPath = $node->getAttribute('series');
		$seriesPosition = $node->getAttribute('series_position');
		if ($seriesPath !== '') {
			$seriesDao = DAORegistry::getDAO('SeriesDAO'); /* @var $seriesDao SeriesDAO */
			$series = $seriesDao->getByPath($seriesPath, $context->getId());
			if (!$series) {
				$deployment->addError(ASSOC_TYPE_PUBLICATION, $publication->getId(), __('plugins.importexport.native.error.unknownSeries', array('param' => $seriesPath)));
			} else {
				$publication->setData('seriesId', $series->getId());
				$publication->setData('seriesPosition', $seriesPosition);
			}
		}

		return parent::populateObject($publication, $node);
	}

	/**
	 * Handle an element whose parent is the submission element.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function handleChildElement($n, $publication) {
		switch ($n->tagName) {
			case 'publication_format':
				$this->parsePublicationFormat($n, $publication);
				break;
			// case 'chapter':
			// 	$this->parseChapter($n, $publication);
			// 	break;
			default:
				parent::handleChildElement($n, $publication);
		}
	}

	/**
	 * Get the import filter for a given element.
	 * @param $elementName string Name of XML element
	 * @return Filter
	 */
	function getImportFilter($elementName) {
		$deployment = $this->getDeployment();
		$publication = $deployment->getPublication();
		$importClass = null; // Scrutinizer
		switch ($elementName) {
			case 'publication_format':
				$importClass='PublicationFormat';
				break;
			case 'chapter':
				$importClass='Chapter';
				break;
			default:
				$deployment->addError(ASSOC_TYPE_PUBLICATION, $publication->getId(), __('plugins.importexport.common.error.unknownElement', array('param' => $elementName)));
		}
		// Caps on class name for consistency with imports, whose filter
		// group names are generated implicitly.
		$filterDao = DAORegistry::getDAO('FilterDAO'); /* @var $filterDao FilterDAO */
		$importFilters = $filterDao->getObjectsByGroup('native-xml=>' . $importClass);
		$importFilter = array_shift($importFilters);
		return $importFilter;
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function parsePublicationFormat($n, $publication) {
		$importFilter = $this->getImportFilter($n->tagName);
		assert($importFilter); // There should be a filter

		$existingDeployment = $this->getDeployment();
		$request = Application::get()->getRequest();
		
		// $onixDeployment = new Onix30ExportDeployment($request->getContext(), $request->getUser());
		// $onixDeployment->setSubmission($existingDeployment->getSubmission());
		// $onixDeployment->setFileDBIds($existingDeployment->getFileDBIds());
		$importFilter->setDeployment($existingDeployment);
		$formatDoc = new DOMDocument();
		$formatDoc->appendChild($formatDoc->importNode($n, true));
		return $importFilter->execute($formatDoc);
	}

	/**
	 * Parse a publication format and add it to the submission.
	 * @param $n DOMElement
	 * @param $publication Publication
	 */
	function parseChapter($n, $publication) {
		$importFilter = $this->getImportFilter($n->tagName);
		assert($importFilter); // There should be a filter

		$existingDeployment = $this->getDeployment();
		$request = Application::get()->getRequest();

		
		$importFilter->setDeployment($existingDeployment);
		$chapterDoc = new DOMDocument();
		$chapterDoc->appendChild($chapterDoc->importNode($n, true));
		return $importFilter->execute($chapterDoc);
	}

	/**
	 * Get the representation export filter group name
	 * @return string
	 */
	function getRepresentationExportFilterGroupName() {
		return 'publication-format=>native-xml';
	}
}


