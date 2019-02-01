<?php

/**
 * @file plugins/generic/htmlMonographFile/HtmlMonographFilePlugin.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class HtmlMonographFilePlugin
 * @ingroup plugins_generic_htmlMonographFile
 *
 * @brief Class for HtmlMonographFile plugin
 */

import('lib.pkp.classes.plugins.GenericPlugin');

class HtmlMonographFilePlugin extends GenericPlugin {
	/**
	 * @copydoc Plugin::register()
	 */
	function register($category, $path, $mainContextId = null) {
		if (parent::register($category, $path, $mainContextId)) {
			if ($this->getEnabled($mainContextId)) {
				HookRegistry::register('CatalogBookHandler::view', array($this, 'viewCallback'));
				HookRegistry::register('CatalogBookHandler::download', array($this, 'downloadCallback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Install default settings on press creation.
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
		return __('plugins.generic.htmlMonographFile.displayName');
	}

	/**
	 * Get a description of the plugin.
	 */
	function getDescription() {
		return __('plugins.generic.htmlMonographFile.description');
	}

	/**
	 * Callback to view the HTML content rather than downloading.
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function viewCallback($hookName, $params) {
		$publishedMonograph =& $params[1];
		$publicationFormat =& $params[2];
		$submissionFile =& $params[3];
		$inline =& $params[4];
		$request = Application::getRequest();

		if ($submissionFile && $submissionFile->getFileType() == 'text/html') {
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->addStyleSheet(
				'htmlArticleGalleyStyles',
				$request->getBaseUrl() . '/plugins/generic/htmlMonographFile/display.css',
				array(
					'priority' => STYLE_SEQUENCE_CORE,
					'contexts' => 'frontend',
				)
			);
			$templateMgr->assign(array(
				'pluginTemplatePath' => $this->getTemplatePath(),
				'pluginUrl' => $request->getBaseUrl() . '/' . $this->getPluginPath(),
				'monograph' => $publishedMonograph,
				'publicationFormat' => $publicationFormat,
				'downloadFile' => $submissionFile,
			));
			$templateMgr->display($this->getTemplatePath() . '/display.tpl');
			return true;
		}

		return false;
	}

	/**
	 * Callback to rewrite and serve HTML content.
	 * @param string $hookName
	 * @param array $args
	 */
	function downloadCallback($hookName, $params) {
		$publishedMonograph =& $params[1];
		$publicationFormat =& $params[2];
		$submissionFile =& $params[3];
		$inline =& $params[4];
		$request = Application::getRequest();

		if ($submissionFile && $submissionFile->getFileType() == 'text/html') {
			if (!HookRegistry::call('HtmlMonographFilePlugin::monographDownload', array(&$this, &$publishedMonograph, &$publicationFormat, &$submissionFile, &$inline))) {
				echo $this->_getHTMLContents($request, $publishedMonograph, $publicationFormat, $submissionFile);
				$returner = true;
				HookRegistry::call('HtmlMonographFilePlugin::monographDownloadFinished', array(&$returner));
				return true;
			}
		}

		return false;
	}
	/**
	 * Return string containing the contents of the HTML file.
	 * This function performs any necessary filtering, like image URL replacement.
	 * @param $request PKPRequest
	 * @param $monograph Monograph
	 * @param $publicationFormat PublicationFormat
	 * @param $submissionFile SubmissionFile
	 * @return string
	 */
	function _getHTMLContents($request, $monograph, $publicationFormat, $submissionFile) {
		$contents = file_get_contents($submissionFile->getFilePath());

		// Replace media file references
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		import('lib.pkp.classes.submission.SubmissionFile'); // Constants
		$embeddableFiles = array_merge(
			$submissionFileDao->getLatestRevisions($submissionFile->getSubmissionId(), SUBMISSION_FILE_PROOF),
			$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_SUBMISSION_FILE, $submissionFile->getFileId(), $submissionFile->getSubmissionId(), SUBMISSION_FILE_DEPENDENT)
		);

		foreach ($embeddableFiles as $embeddableFile) {
			$fileUrl = $request->url(null, 'catalog', 'download', array($monograph->getBestId(), $publicationFormat->getBestId(), $embeddableFile->getBestId()), array('inline' => true));
			$pattern = preg_quote($embeddableFile->getOriginalFileName());

			$contents = preg_replace(
					'/([Ss][Rr][Cc]|[Hh][Rr][Ee][Ff]|[Dd][Aa][Tt][Aa])\s*=\s*"([^"]*' . $pattern . ')"/',
					'\1="' . $fileUrl . '"',
					$contents
			);

			// Replacement for Flowplayer
			$contents = preg_replace(
					'/[Uu][Rr][Ll]\s*\:\s*\'(' . $pattern . ')\'/',
					'url:\'' . $fileUrl . '\'',
					$contents
			);

			// Replacement for other players (tested with odeo; yahoo and google player won't work w/ OJS URLs, might work for others)
			$contents = preg_replace(
					'/[Uu][Rr][Ll]=([^"]*' . $pattern . ')/',
					'url=' . $fileUrl ,
					$contents
			);

		}

		// Perform replacement for ojs://... URLs
		$contents = preg_replace_callback(
				'/(<[^<>]*")[Oo][Mm][Pp]:\/\/([^"]+)("[^<>]*>)/',
				array(&$this, '_handleOmpUrl'),
				$contents
		);

		// Perform variable replacement for press, publication format, site info
		$press = $request->getPress();
		$site = $request->getSite();

		$paramArray = array(
				'pressTitle' => $press->getLocalizedName(),
				'siteTitle' => $site->getLocalizedTitle(),
				'currentUrl' => $request->getRequestUrl()
		);

		foreach ($paramArray as $key => $value) {
			$contents = str_replace('{$' . $key . '}', $value, $contents);
		}

		return $contents;
	}

	function _handleOmpUrl($matchArray) {
		$request = Application::getRequest();
		$url = $matchArray[2];
		$anchor = null;
		if (($i = strpos($url, '#')) !== false) {
			$anchor = substr($url, $i+1);
			$url = substr($url, 0, $i);
		}
		$urlParts = explode('/', $url);
		if (isset($urlParts[0])) switch(strtolower_codesafe($urlParts[0])) {
			case 'press':
				$url = $request->url(
					isset($urlParts[1]) ? $urlParts[1] : $request->getRequestedPressPath(),
					null, null, null, null, $anchor
				);
				break;
			case 'monograph':
				if (isset($urlParts[1])) {
					$url = $request->url(
						null, 'catalog', 'book',
						$urlParts[1], null, $anchor
					);
				}
				break;
			case 'sitepublic':
				array_shift($urlParts);
				import ('classes.file.PublicFileManager');
				$publicFileManager = new PublicFileManager();
				$url = $request->getBaseUrl() . '/' . $publicFileManager->getSiteFilesPath() . '/' . implode('/', $urlParts) . ($anchor?'#' . $anchor:'');
				break;
			case 'public':
				array_shift($urlParts);
				$press = $request->getPress();
				import ('classes.file.PublicFileManager');
				$publicFileManager = new PublicFileManager();
				$url = $request->getBaseUrl() . '/' . $publicFileManager->getContextFilesPath($press->getId()) . '/' . implode('/', $urlParts) . ($anchor?'#' . $anchor:'');
				break;
		}
		return $matchArray[1] . $url . $matchArray[3];
	}
}


