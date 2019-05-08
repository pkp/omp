<?php

/**
 * @file plugins/importexport/native/filter/NativeXmlMonographFileFilter.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
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
		return 'plugins.importexport.native.filter.NativeXmlMonographFileFilter';
	}
}


