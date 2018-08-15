<?php

/**
 * @file plugins/importexport/native/filter/PublicationFormatNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a PublicationFormat to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.RepresentationNativeXmlFilter');

class PublicationFormatNativeXmlFilter extends RepresentationNativeXmlFilter {
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
		return 'plugins.importexport.native.filter.PublicationFormatNativeXmlFilter';
	}

	//
	// Extend functions in RepresentationNativeXmlFilter
	//
	/**
	 * Create and return a representation node. Extend the parent class
	 * with publication format specific data.
	 * @param $doc DOMDocument
	 * @param $representation Representation
	 * @return DOMElement
	 */
	function createRepresentationNode($doc, $representation) {
		$representationNode = parent::createRepresentationNode($doc, $representation);
		$representationNode->setAttribute('approved', $representation->getIsApproved()?'true':'false');
		$representationNode->setAttribute('available', $representation->getIsAvailable()?'true':'false');
		$representationNode->setAttribute('physical_format', $representation->getPhysicalFormat()?'true':'false');

		// If all nexessary press settings exist, export ONIX metadata
		$context = $this->getDeployment()->getContext();
		if ($context->getSetting('publisher') && $context->getSetting('location') && $context->getSetting('codeType') && $context->getSetting('codeValue')) {
			$submission = $this->getDeployment()->getSubmission();

			$filterDao = DAORegistry::getDAO('FilterDAO');
			$nativeExportFilters = $filterDao->getObjectsByGroup('monograph=>onix30-xml');
			assert(count($nativeExportFilters) == 1); // Assert only a single serialization filter
			$exportFilter = array_shift($nativeExportFilters);

			$exportFilter->setDeployment(new Onix30ExportDeployment(Request::getContext(), Request::getUser()));

			$onixDoc  = $exportFilter->execute($submission);
			if ($onixDoc) { // we do this to ensure validation.
				// assemble just the Product node we want.
				$publicationFormatDOMElement = $exportFilter->createProductNode($doc, $submission, $representation);
				if ($publicationFormatDOMElement instanceof DOMElement) {
					import('lib.pkp.classes.xslt.XSLTransformer');
					$xslTransformer = new XSLTransformer();
					$xslFile = 'plugins/importexport/native/onixProduct2NativeXml.xsl';
					$productXml = $publicationFormatDOMElement->ownerDocument->saveXML($publicationFormatDOMElement);
					$filteredXml = $xslTransformer->transform($productXml, XSL_TRANSFORMER_DOCTYPE_STRING, $xslFile, XSL_TRANSFORMER_DOCTYPE_FILE, XSL_TRANSFORMER_DOCTYPE_STRING);
					$representationFragment = $doc->createDocumentFragment();
					$representationFragment->appendXML($filteredXml);
					$representationNode->appendChild($representationFragment);
				}
			}
		}
		return $representationNode;
	}

	/**
	 * Get the available submission files for a representation
	 * @param $representation Representation
	 * @return array
	 */
	function getFiles($representation) {
		$deployment = $this->getDeployment();
		$submission = $deployment->getSubmission();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		return array_filter(
			$submissionFileDao->getLatestRevisions($submission->getId()),
			function($a) use ($representation) {
				return $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT && $a->getAssocId() == $representation->getId();
			}
		);
	}
}


