<?php

/**
 * @file plugins/viewableFiles/pdfJsViewer/PdfJsViewerPlugin.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
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
	function getTemplatePath($inCore = false) {
		return parent::getTemplatePath($inCore) . 'templates/';
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
	function canHandle($publishedMonograph, $publicationFormat, $submissionFile) {
		return ($submissionFile->getFileType() == 'application/pdf');
	}

	/**
	 * @copydoc ViewableFilePlugin::displaySubmissionFile
	 */
	function displaySubmissionFile($publishedMonograph, $publicationFormat, $submissionFile) {
		$request = $this->getRequest();
		$router = $request->getRouter();
		$dispatcher = $request->getDispatcher();
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign(array(
			'pluginTemplatePath' => $this->getTemplatePath(),
			'pluginUrl' => $request->getBaseUrl() . DIRECTORY_SEPARATOR . $this->getPluginPath(),
			'downloadUrl' => $dispatcher->url($request, ROUTE_PAGE, null, null, 'download', array($publishedMonograph->getBestId(), $publicationFormat->getBestId(), $submissionFile->getBestId()), array('inline' => true)),
		));


		return parent::displaySubmissionFile($publishedMonograph, $publicationFormat, $submissionFile);
	}

	/**
	 * Callback for download function
	 * @param $hookName string
	 * @param $params array
	 * @return boolean
	 */
	function downloadCallback($hookName, $params) {
		$publishedMonograph =& $params[1];
		$publicationFormat =& $params[2];
		$submissionFile =& $params[3];
		$inline =& $params[4];

		if ($this->canHandle($publishedMonograph, $publicationFormat, $submissionFile) && Request::getUserVar('inline')) {
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
