<?php

/**
 * @file pages/information/InformationHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

import('classes.handler.Handler');

class InformationHandler extends Handler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}

	/**
	 * Display the information page for the press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$this->validate(null, $request);
		$press = $request->getPress();
		if ($press == null) $request->redirect('index');

		$this->setupTemplate($request, $press);

		$contentOnly = $request->getUserVar('contentOnly');

		switch(array_shift($args)) {
			case 'readers':
				$content = $press->getLocalizedSetting('readerInformation');
				$pageTitle = 'navigation.infoForReaders.long';
				$pageCrumbTitle = 'navigation.infoForReaders';
				break;
			case 'authors':
				$content = $press->getLocalizedSetting('authorInformation');
				$pageTitle = 'navigation.infoForAuthors.long';
				$pageCrumbTitle = 'navigation.infoForAuthors';
				break;
			case 'librarians':
				$content = $press->getLocalizedSetting('librarianInformation');
				$pageTitle = 'navigation.infoForLibrarians.long';
				$pageCrumbTitle = 'navigation.infoForLibrarians';
				break;
			case 'competingInterestPolicy':
				$content = $press->getLocalizedSetting('competingInterestPolicy');
				$pageTitle = $pageCrumbTitle = 'navigation.competingInterestPolicy';
				break;
			case 'sampleCopyrightWording':
				$content = __('manager.setup.copyrightNotice.sample');
				$pageTitle = $pageCrumbTitle = 'manager.setup.copyrightNotice';
				break;
			default:
				$request->redirect($press->getPath());
				return;
		}

		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
		$templateMgr->assign('pageTitle', $pageTitle);
		$templateMgr->assign('content', $content);
		$templateMgr->assign('contentOnly', $contentOnly); // Hide the header and footer code

		$templateMgr->display('frontend/pages/information.tpl');
	}

	function readers($args, $request) {
		$this->index(array('readers'), $request);
	}

	function authors($args, $request) {
		$this->index(array('authors'), $request);
	}

	function librarians($args, $request) {
		$this->index(array('librarians'), $request);
	}

	function competingInterestPolicy($args, $request) {
		return $this->index(array('competingInterestPolicy'), $request);
	}

	function sampleCopyrightWording($args, $request) {
		$this->index(array('sampleCopyrightWording'), $request);
	}

	/**
	 * Initialize the template.
	 * @param $press Press
	 */
	function setupTemplate($request, $press) {
		parent::setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_MANAGER); // FIXME needed?
		if (!$press->getSetting('restrictSiteAccess')) {
			$templateMgr = TemplateManager::getManager($request);
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
	}
}


