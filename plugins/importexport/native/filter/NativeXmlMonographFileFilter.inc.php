<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFileFilter.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class NativeXmlMonographFileFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Native XML document to a monograph file.
 */

import('lib.pkp.plugins.importexport.native.filter.NativeXmlSubmissionFileFilter');

class NativeXmlMonographFileFilter extends NativeXmlSubmissionFileFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function NativeXmlMonographFileFilter($filterGroup) {
		parent::NativeXmlSubmissionFileFilter($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.NativeXmlMonographFileFilter';
	}
}

?>
