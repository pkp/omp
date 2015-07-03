<?php

/**
 * @file plugins/importexport/native/filter/AuthorNativeXmlFilter.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorNativeXmlFilter
 * @ingroup plugins_importexport_native
 *
 * @brief Class that converts a Author to a Native XML document.
 */

import('lib.pkp.plugins.importexport.native.filter.PKPAuthorNativeXmlFilter');

class AuthorNativeXmlFilter extends PKPAuthorNativeXmlFilter {
	/**
	 * Constructor
	 * @param $filterGroup FilterGroup
	 */
	function AuthorNativeXmlFilter($filterGroup) {
		parent::PKPAuthorNativeXmlFilter($filterGroup);
	}


	//
	// Implement template methods from PersistableFilter
	//
	/**
	 * @copydoc PersistableFilter::getClassName()
	 */
	function getClassName() {
		return 'plugins.importexport.native.filter.AuthorNativeXmlFilter';
	}
}

?>
