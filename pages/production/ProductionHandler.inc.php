<?php

/**
 * @file InformationHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

// $Id$


import('core.Handler');

class InformationHandler extends Handler {

	/**
	 * Display the information page for the press..
	 */
	function index($args) {
		parent::validate();
		InformationHandler::setupTemplate();
		$press = Request::getPress();

		if ($press == null) {
			Request::redirect('index');
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
			case 'competingInterestGuidelines':
				$content = $press->getLocalizedSetting('competingInterestGuidelines');
				$pageTitle = $pageCrumbTitle = 'navigation.competingInterestGuidelines';
				break;
			case 'sampleCopyrightWording':
				$content = Locale::translate('manager.setup.authorCopyrightNotice.sample');
				$pageTitle = $pageCrumbTitle = 'manager.setup.copyrightNotice';
				break;
			default:
				Request::redirect($press->getPath());
				return;
		}

		$templateMgr = &TemplateManager::getManager();
		$templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
		$templateMgr->assign('pageTitle', $pageTitle);
		$templateMgr->assign('content', $content);
		$templateMgr->display('information/information.tpl');
	}

	function readers() {
		InformationHandler::index(array('readers'));
	}

	function authors() {
		InformationHandler::index(array('authors'));
	}

	function librarians() {
		InformationHandler::index(array('librarians'));
	}

	function competingInterestGuidelines() {
		InformationHandler::index(array('competingInterestGuidelines'));
	}

	function sampleCopyrightWording() {
		InformationHandler::index(array('sampleCopyrightWording'));
	}

	/**
	 * Initialize the template.
	 */
	function setupTemplate() {
		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();
		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
	}
}

?>
