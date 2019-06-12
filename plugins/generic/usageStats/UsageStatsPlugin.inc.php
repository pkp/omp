<?php

/**
 * @file plugins/generic/usageStats/UsageStatsPlugin.inc.php
 *
 * Copyright (c) 2013-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageStatsPlugin
 * @ingroup plugins_generic_usageStats
 *
 * @brief Provide usage statistics to data objects.
 */


import('lib.pkp.plugins.generic.usageStats.PKPUsageStatsPlugin');

class UsageStatsPlugin extends PKPUsageStatsPlugin {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * @copydoc PKPUsageEventPlugin::getDownloadFinishedEventHooks()
	 */
	protected function getDownloadFinishedEventHooks() {
		return array_merge(parent::getDownloadFinishedEventHooks(), array(
				'HtmlMonographFilePlugin::monographDownloadFinished'
		));
	}

	/**
	 * Register assets and output hooks to display statistics on the reader
	 * frontend.
	 *
	 * @return null
	 */
	function displayReaderStatistics() {

		// Add chart to article view page
		HookRegistry::register('Templates::Catalog::Book::Main', array($this, 'displayReaderMonographGraph'));
	}

	/**
	 * Add chart to article view page
	 *
	 * Hooked to `Templates::Catalog::Book::Main`
	 * @param $hookName string
	 * @param $params array
	 *   [1] $smarty object
	 *   [2] $output string HTML output to return
	 */
	function displayReaderMonographGraph($hookName, $params) {
		$smarty =& $params[1];
		$output =& $params[2];

		$pubObject =& $smarty->get_template_vars('publishedSubmission');
		assert(is_a($pubObject, 'PublishedSubmission'));
		$pubObjectId = $pubObject->getID();
		$pubObjectType = 'PublishedSubmission';

		$output .= $this->getTemplate(
			array(
				'pubObjectType' => $pubObjectType,
				'pubObjectId'   => $pubObjectId,
			),
			'outputFrontend.tpl',
			$smarty
		);

		$this->addJavascriptData($this->getAllDownloadsStats($pubObjectId), $pubObjectType, $pubObjectId, 'frontend-catalog-book');
		$this->loadJavascript('frontend-catalog-book' );

		return false;
	}
}

?>
