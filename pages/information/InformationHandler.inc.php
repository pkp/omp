<?php

/**
 * @file InformationHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
	function InformationHandler() {
		parent::Handler();
	}

	/**
	 * Display the information page for the press.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);
		$press = $request->getPress();
		$contentOnly = $request->getUserVar('contentOnly');

		if ($press == null) {
			$request->redirect('index');
			return;
		}

		switch(isset($args[0])?$args[0]:null) {
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
				$content = __('manager.setup.authorCopyrightNotice.sample');
				$pageTitle = $pageCrumbTitle = 'manager.setup.copyrightNotice';
				break;
			default:
				$request->redirect($press->getPath());
				return;
		}

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
		$templateMgr->assign('pageTitle', $pageTitle);
		$templateMgr->assign('content', $content);
		$templateMgr->assign('contentOnly', $contentOnly); // Hide the header and footer code

		return $templateMgr->fetchJson('information/information.tpl');
	}

	function readers() {
		$this->index(array('readers'));
	}

	function authors() {
		$this->index(array('authors'));
	}

	function librarians() {
		$this->index(array('librarians'));
	}

	function competingInterestPolicy() {
		return $this->index(array('competingInterestPolicy'));
	}

	function sampleCopyrightWording() {
		$this->index(array('sampleCopyrightWording'));
	}

	/**
	 * Initialize the template.
	 * @param $request PKPRequest
	 */
	function setupTemplate($request) {
		$press =& $request->getPress();
		AppLocale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER));
		$templateMgr =& TemplateManager::getManager();
		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
	}
}

?>
