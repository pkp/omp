<?php

/**
 * @file plugins/generic/codeCoverage/CodeCoveragePlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CodeCoveragePlugin
 * @ingroup plugins_generic_codeCoverage
 * @see PKPCodeCoveragePlugin
 *
 * @brief This is an application specific wrapper class around PKPCodeCoveragePlugin.
 *
 * NB: Please read the installation and configuration requirements in PKPCodeCoveragePlugin's
 * classdoc.
 */


import('lib.pkp.plugins.generic.codeCoverage.PKPCodeCoverageGenericPlugin');

class CodeCoverageGenericPlugin extends PKPCodeCoverageGenericPlugin {
	/**
	 * Constructor
	 */
	function CodeCoveragePlugin() {
		return parent::PKPCodeCoveragePlugin();
	}
}

?>
