<?php

/**
 * @file plugins/importexport/native/filter/PublicationFormatNativeXmlFilter.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
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
	function PublicationFormatNativeXmlFilter($filterGroup) {
		parent::RepresentationNativeXmlFilter($filterGroup);
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
		$representationNode->setAttribute('physical_format', $representation->getPhysicalFormat()?'true':'false');

		return $representationNode;
	}
}

?>
