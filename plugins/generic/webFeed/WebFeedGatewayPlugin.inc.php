<?php

/**
 * @file plugins/generic/webFeed/WebFeedGatewayPlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WebFeedGatewayPlugin
 * @ingroup plugins_generic_webFeed
 *
 * @brief Gateway component of web feed plugin
 *
 */

import('lib.pkp.classes.plugins.GatewayPlugin');

class WebFeedGatewayPlugin extends GatewayPlugin {
	/** @var string Name of parent plugin */
	var $parentPluginName;

	function WebFeedGatewayPlugin($parentPluginName) {
		parent::GatewayPlugin();
		$this->parentPluginName = $parentPluginName;
	}

	/**
	 * Hide this plugin from the management interface (it's subsidiary)
	 */
	function getHideManagement() {
		return true;
	}

	/**
	 * Get the name of this plugin. The name must be unique within
	 * its category.
	 * @return String name of plugin
	 */
	function getName() {
		return 'WebFeedGatewayPlugin';
	}

	function getDisplayName() {
		return __('plugins.generic.webfeed.displayName');
	}

	function getDescription() {
		return __('plugins.generic.webfeed.description');
	}

	/**
	 * Get the web feed plugin
	 * @return WebFeedPlugin
	 */
	function getWebFeedPlugin() {
		return PluginRegistry::getPlugin('generic', $this->parentPluginName);
	}

	/**
	 * Override the builtin to get the correct plugin path.
	 */
	function getPluginPath() {
		return $this->getWebFeedPlugin()->getPluginPath();
	}

	/**
	 * Override the builtin to get the correct template path.
	 * @return string
	 */
	function getTemplatePath() {
		return $this->getWebFeedPlugin()->getTemplatePath();
	}

	/**
	 * Get whether or not this plugin is enabled. (Should always return true, as the
	 * parent plugin will take care of loading this one when needed)
	 * @return boolean
	 */
	function getEnabled() {
		return $this->getWebFeedPlugin()->getEnabled();
	}

	/**
	 * Handle fetch requests for this plugin.
	 * @param $args array Arguments.
	 * @param $request PKPRequest Request object.
	 */
	function fetch($args, $request) {
		$webFeedPlugin = $this->getWebFeedPlugin();
		if (!$webFeedPlugin->getEnabled()) return false;

		// Make sure the feed type is specified and valid
		$type = array_shift($args);
		$typeMap = array(
			'rss' => 'rss.tpl',
			'rss2' => 'rss2.tpl',
			'atom' => 'atom.tpl'
		);
		$mimeTypeMap = array(
			'rss' => 'application/rdf+xml',
			'rss2' => 'application/rss+xml',
			'atom' => 'application/atom+xml'
		);
		if (!isset($typeMap[$type])) return false;

		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getContext();
		$templateMgr->assign('press', $press);

		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$recentItems = (int) $webFeedPlugin->getSetting($press->getId(), 'recentItems');
		if ($recentItems > 0) {
			import('lib.pkp.classes.db.DBResultRange');
			$rangeInfo = new DBResultRange($recentItems, 1);
			$publishedMonographObjects = $publishedMonographDao->getByPressId(
				$press->getId(),
				null,
				$rangeInfo
			);
			$publishedMonographs = $publishedMonographObjects->toArray();
		} else $publishedMonographs = array();
		$templateMgr->assign('publishedMonographs', $publishedMonographs);

		$versionDao = DAORegistry::getDAO('VersionDAO');
		$version = $versionDao->getCurrentVersion();
		$templateMgr->assign('ompVersion', $version->getVersionString());

		$templateMgr->display($this->getTemplatePath() . $typeMap[$type], $mimeTypeMap[$type]);

		return true;
	}
}

?>
