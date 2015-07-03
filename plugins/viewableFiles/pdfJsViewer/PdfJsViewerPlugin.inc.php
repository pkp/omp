<?php

/**
 * @file plugins/viewableFiles/pdfJsViewer/PdfJsViewerPlugin.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PdfJsViewerPlugin
 * @ingroup plugins_viewableFiles_pdfJsViewer
 *
 * @brief Class for PdfJsViewer plugin
 */

import('classes.plugins.ViewableFilePlugin');

class PdfJsViewerPlugin extends ViewableFilePlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->getEnabled()) {
				HookRegistry::register('CatalogBookHandler::download', array($this, 'downloadCallback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Install default settings on journal creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * @copydoc PKPPlugin::getTemplatePath
	 */
	function getTemplatePath() {
		return parent::getTemplatePath() . 'templates/';
	}

	/**
	 * Get the display name of this plugin.
	 * @return String
	 */
	function getDisplayName() {
		return __('plugins.viewableFiles.pdfJsViewer.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.viewableFiles.pdfJsViewer.description');
	}

	/**
	 * @copydoc ViewableFilePlugin::canHandle
	 */
	function canHandle($publishedMonograph, $submissionFile) {
		return ($submissionFile->getFileType() == 'application/pdf');
	}

	/**
	 * @copydoc ViewableFilePlugin::displaySubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $submissionFile) {
		$templateMgr = TemplateManager::getManager($this->getRequest());
		$templateMgr->assign(array(
			'pluginTemplatePath' => $this->getTemplatePath(),
			'pluginUrl' => $this->getRequest()->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath(),
		));
		return parent::displaySubmissionFile($publishedMonograph, $submissionFile);
	}

	/**
	 * Callback for download function
	 * @param $hookName string
	 * @param $params array
	 * @return boolean
	 */
	function downloadCallback($hookName, $params) {
		$publishedMonograph =& $params[1];
		$submissionFile =& $params[2];
		$inline =& $params[3];

		if ($this->canHandle($publishedMonograph, $submissionFile) && Request::getUserVar('inline')) {
			// Turn on the inline flag to ensure that the content
			// disposition header doesn't foil the PDF embedding
			// plugin.
			$inline = true;
		}

		// Return to regular handling
		return false;
	}

	/**
	 * Get the plugin base URL.
	 * @param $request PKPRequest
	 * @return string
	 */
	private function _getPluginUrl($request) {
		return $request->getBaseUrl() . '/' . $this->getPluginPath();
	}
}

?>
