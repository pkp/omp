<?php
/**
 * @defgroup plugins_metadata_native_filter Native XML import filter
 */

/**
 * @file plugins/metadata/native/filter/NativeXmlMonographFilter.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFilter
 * @ingroup plugins_importexport_native_filter
 *
 * @brief Class that converts a Native XML document to a set of monographs.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlSubmissionFilter');
import('lib.pkp.classes.xml.XMLCustomWriter');

class NativeXmlMonographFilter extends NativeXmlSubmissionFilter {
	/**
	 * Constructor
	 * $filterGroup FilterGroup
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
	 * Get the submissions node name
	 * @return string
	 */
	function getSubmissionsNodeName() {
		return 'monographs';
	}

}

?>
