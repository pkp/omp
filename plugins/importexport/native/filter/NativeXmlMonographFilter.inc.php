<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFilter.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
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
	 * Handle an element whose parent is the submission element.
	 * @param $n DOMElement
	 * @param $submission Submission
	 */
	function handleChildElement($n, $submission) {
		switch ($n->tagName) {
			case 'artwork_file':
				$this->parseSubmissionFile($n, $submission);
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
		switch ($elementName) {
			case 'submission_file':
				$importClass='SubmissionFile';
				break;
			case 'artwork_file':
				$importClass='ArtworkFile';
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
}

?>
