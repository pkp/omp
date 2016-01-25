<?php

/**
 * @file classes/plugins/ViewableFilePlugin.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewableFilePlugin
 * @ingroup plugins
 *
 * @brief Abstract class for article galley plugins
 */

import('lib.pkp.classes.plugins.PKPViewableFilePlugin');

abstract class ViewableFilePlugin extends PKPViewableFilePlugin {
	/**
	 * Constructor
	 */
	function ViewableFilePlugin() {
		parent::PKPViewableFilePlugin();
	}

	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (!parent::register($category, $path)) return false;

		if ($this->getEnabled()) {
			HookRegistry::register('CatalogBookHandler::view', array($this, 'callback'));
		}
		return true;
	}

	/**
	 * Display this galley in some manner.
	 *
	 * @param $publishedMonograph PublishedMonograph
	 * @param $submissionFile SubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $submissionFile) {
		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateFilename = $this->getTemplateFilename();
		if ($templateFilename === null) return '';
		$templateMgr->assign('publishedMonograph', $publishedMonograph);
		$templateMgr->assign('submissionFile', $submissionFile);
		$templateMgr->assign('viewableFileContent', $templateMgr->fetch($this->getTemplatePath() . $templateFilename));
		$templateMgr->display('catalog/book/viewFile.tpl');
	}

	/**
	 * Determine whether this plugin can handle the specified content.
	 * @param $publishedMonograph PublishedMonograph
	 * @param $submissionFile SubmissionFile
	 * @return boolean True iff the plugin can handle the content
	 */
	function canHandle($publishedMonograph, $submissionFile) {
		return false;
	}

	/**
	 * Callback that renders the galley.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callback($hookName, $args) {
		$publishedMonograph =& $args[1];
		$submissionFile =& $args[2];

		if ($this->canHandle($publishedMonograph, $submissionFile)) {
			$this->displaySubmissionFile($publishedMonograph, $submissionFile);
			return true;
		}

		return false;
	}
}

?>
