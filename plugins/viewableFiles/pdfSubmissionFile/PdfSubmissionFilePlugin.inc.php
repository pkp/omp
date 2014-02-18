<?php

/**
 * @file plugins/viewableFiles/pdfSubmissionFile/PdfSubmissionFilePlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PdfSubmissionFilePlugin
 * @ingroup plugins_viewableFiles_pdfSubmissionFile
 *
 * @brief Class for PdfSubmissionFile plugin
 */

import('classes.plugins.ViewableFilePlugin');

class PdfSubmissionFilePlugin extends ViewableFilePlugin {
	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.viewableFiles.pdfSubmissionFile.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.viewableFiles.pdfSubmissionFile.description');
	}

	/**
	 * @copydoc ViewableFilePlugin::canHandle
	 */
	function canHandle($publishedMonograph, $submissionFile) {
		return ($submissionFile->getFileType() == 'application/pdf');
	}

	/**
	 * @see ViewableFilePlugin::displaySubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $submissionFile) {
		$request = $this->getRequest();
		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateMgr->assign('pluginJSPath', $this->getJSPath($request));
		return parent::displaySubmissionFile($publishedMonograph, $submissionFile);
	}

	/**
	 * returns the base path for JS included in this plugin.
	 * @param $request PKPRequest
	 * @return string
	 */
	function getJSPath($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath() . '/js';
	}
}

?>
