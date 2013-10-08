<?php
/**
 * @defgroup plugins_metadata_native_filter Native XML submission filter
 */

/**
 * @file plugins/metadata/native/filter/MonographNativeXmlFilter.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographNativeXmlFilter
 * @ingroup plugins_importexport_native_filter
 *
 * @brief Class that converts a Monograph to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.SubmissionNativeXmlFilter');
import('lib.pkp.classes.xml.XMLCustomWriter');

class MonographNativeXmlFilter extends SubmissionNativeXmlFilter {
	/**
	 * Constructor
	 * $filterGroup FilterGroup
	 */
	function MonographNativeXmlFilter($filterGroup) {
		parent::SubmissionNativeXmlFilter($filterGroup);
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
	// Implement template methods from SubmissionNativeXmlFilter
	//
	/**
	 * Get the submission node name
	 * @return string
	 */
	function getSubmissionNodeName() {
		return 'monograph';
	}

	/**
	 * Get the namespace URN
	 * @return string
	 */
	function getNamespace() {
		return 'http://pkp.sfu.ca';
	}

	/**
	 * Get the schema filename.
	 * @return string
	 */
	function getSchemaFilename() {
		return 'native.xsd';
	}
}

?>
