<?php

/**
 * @file plugins/importexport/native/filter/ArtworkFileNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2000-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ArtworkFileNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Filter to convert an artwork file to a Native XML document
 */

import('lib.pkp.plugins.importexport.native.filter.SubmissionFileNativeXmlFilter');

class MonographFileNativeXmlFilter extends SubmissionFileNativeXmlFilter {
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
		return 'plugins.importexport.native.filter.MonographFileNativeXmlFilter';
	}


	//
	// Implement/override functions from SubmissionFileNativeXmlFilter
	//
	/**
	 * Create and return a submissionFile node.
	 * @param $doc DOMDocument
	 * @param $submissionFile SubmissionFile
	 * @return DOMElement
	 */
	function createSubmissionFileNode($doc, $submissionFile) {
		$deployment = $this->getDeployment();
		$submissionFileNode = parent::createSubmissionFileNode($doc, $submissionFile);
		
		if ($submissionFile->getData('directSalesPrice')) {
			$submissionFileNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'directSalesPrice', $submissionFile->getData('directSalesPrice')));
		}

		if ($submissionFile->getData('salesType')) {
			$submissionFileNode->appendChild($doc->createElementNS($deployment->getNamespace(), 'salesType', $submissionFile->getData('salesType')));
		}

		// FIXME: is permission file ID implemented?
		// FIXME: is chapter ID implemented?
		// FIXME: is contact author ID implemented?

		return $submissionFileNode;
	}

	/**
	 * Get the submission file element name
	 */
	function getSubmissionFileElementName() {
		return 'submission_file';
	}
}


