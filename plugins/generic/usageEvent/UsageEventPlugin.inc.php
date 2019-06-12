<?php

/**
 * @file plugins/generic/usageEvent/UsageEventPlugin.inc.php
 *
 * Copyright (c) 2013-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UsageEventPlugin
 * @ingroup plugins_generic_usageEvent
 *
 * @brief Implements application specifics for usage event generation.
 */

import('lib.pkp.plugins.generic.usageEvent.PKPUsageEventPlugin');

class UsageEventPlugin extends PKPUsageEventPlugin {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Protected methods.
	//
	/**
	 * @see PKPUsageEventPlugin::getEventHooks()
	 */
	protected function getEventHooks() {
		$hooks = parent::getEventHooks();
		$ompHooks = array(
			'CatalogBookHandler::view',
			'CatalogBookHandler::download',
			'HtmlMonographFilePlugin::monographDownload',
			'HtmlMonographFilePlugin::monographDownloadFinished',
		);

		return array_merge($hooks, $ompHooks);
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
	 * @see PKPUsageEventPlugin::getUsageEventData()
	 */
	protected function getUsageEventData($hookName, $hookArgs, $request, $router, $templateMgr, $context) {
		list($pubObject, $downloadSuccess, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams) =
			parent::getUsageEventData($hookName, $hookArgs, $request, $router, $templateMgr, $context);

		if (!$pubObject) {
			switch ($hookName) {
				// Press index page, series content page and monograph abstract.
				case 'TemplateManager::display':
					$page = $router->getRequestedPage($request);
					$op = $router->getRequestedOp($request);

					$wantedPages = array('catalog');
					$wantedOps = array('index', 'book', 'series');

					if (!in_array($page, $wantedPages) || !in_array($op, $wantedOps)) break;

					$press = $templateMgr->get_template_vars('currentContext'); /* @var $press Press */
					$series = $templateMgr->get_template_vars('series'); /* @var $series Series */
					$publishedSubmission = $templateMgr->get_template_vars('publishedSubmission');

					// No published objects, no usage event.
					if (!$press && !$series && !$publishedSubmission) break;

					if ($press) {
						$pubObject = $press;
						$assocType = ASSOC_TYPE_PRESS;
						$canonicalUrlOp = '';
					}

					if ($series) {
						$pubObject = $series;
						$assocType = ASSOC_TYPE_SERIES;
						$canonicalUrlParams = array($series->getPath());
						$idParams = array('s' . $series->getId());
					}

					if ($publishedSubmission) {
						$pubObject = $publishedSubmission;
						$assocType = ASSOC_TYPE_MONOGRAPH;
						$canonicalUrlParams = array($pubObject->getId());
						$idParams = array('m' . $pubObject->getId());
					}

					$downloadSuccess = true;
					$canonicalUrlOp = $op;
					break;

					// Publication format file.
				case 'CatalogBookHandler::view':
				case 'CatalogBookHandler::download':
				case 'HtmlMonographFilePlugin::monographDownload':
					$pubObject = $hookArgs[3];
					$assocType = ASSOC_TYPE_SUBMISSION_FILE;
					$canonicalUrlOp = 'download';
					$publishedSubmission = $hookArgs[1];
					$publicationFormat = $hookArgs[2];
					// if file is not a publication format file (e.g. CSS or images), there is no usage event.
					if ($pubObject->getAssocId() != $publicationFormat->getId()) return false;
					$canonicalUrlParams = array($publishedSubmission->getId(), $pubObject->getAssocId(), $pubObject->getFileId() . '-' . $pubObject->getRevision());
					$idParams = array('m' . $publishedSubmission->getId(), 'f' . $pubObject->getId());
					$downloadSuccess = false;
					break;
				default:
					// Why are we called from an unknown hook?
					assert(false);
			}
		}

		switch ($assocType) {
			case ASSOC_TYPE_PRESS:
			case ASSOC_TYPE_SERIES:
			case ASSOC_TYPE_MONOGRAPH:
			case ASSOC_TYPE_SUBMISSION_FILE:
				$canonicalUrlPage = 'catalog';
				break;
		}

		return array($pubObject, $downloadSuccess, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams);
	}

	/**
	 * @see PKPUsageEventPlugin::getHtmlPageAssocTypes()
	 */
	protected function getHtmlPageAssocTypes() {
		return array(
			ASSOC_TYPE_PRESS,
			ASSOC_TYPE_SERIES,
			ASSOC_TYPE_MONOGRAPH
		);
	}

	/**
	 * @see PKPUsageEventPlugin::isPubIdObjectType()
	 */
	protected function isPubIdObjectType($pubObject) {
		return is_a($pubObject, 'PublishedSubmission');
	}
}

?>
