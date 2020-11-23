<?php

/**
 * @file plugins/generic/usageEvent/UsageEventPlugin.inc.php
 *
 * Copyright (c) 2013-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
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
		);

		return array_merge($hooks, $ompHooks);
	}
	/**
	 * @see PKPUsageEventPlugin::getUsageEventData()
	 */
	protected function getUsageEventData($hookName, $hookArgs, $request, $router, $templateMgr, $context) {
		list($pubObject, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams) =
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

					$press = $templateMgr->getTemplateVars('currentContext'); /* @var $press Press */
					$series = $templateMgr->getTemplateVars('series'); /* @var $series Series */
					$submission = $templateMgr->getTemplateVars('publishedSubmission');

					// No published objects, no usage event.
					if (!$press && !$series && !$submission) break;

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

					if ($submission) {
						$pubObject = $submission;
						$assocType = ASSOC_TYPE_MONOGRAPH;
						$canonicalUrlParams = array($pubObject->getId());
						$idParams = array('m' . $pubObject->getId());
					}

					$canonicalUrlOp = $op;
					break;

					// Publication format file.
				case 'CatalogBookHandler::view':
					$pubObject = $hookArgs[3];
					$assocType = ASSOC_TYPE_SUBMISSION_FILE;
					$canonicalUrlOp = 'download';
					$submission = $hookArgs[1];
					$publicationFormat = $hookArgs[2];
					// if file is not a publication format file (e.g. CSS or images), there is no usage event.
					if ($pubObject->getData('assocId') != $publicationFormat->getId()) return false;
					$canonicalUrlParams = array($submission->getId(), $pubObject->getData('assocId'), $pubObject->getId());
					$idParams = array('m' . $submission->getId(), 'f' . $pubObject->getId());
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

		return array($pubObject, $assocType, $idParams, $canonicalUrlPage, $canonicalUrlOp, $canonicalUrlParams);
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
		return is_a($pubObject, 'Submission');
	}
}


