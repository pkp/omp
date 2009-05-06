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


import('handler.Handler');

class InformationHandler extends Handler {
	function InformationHandler() {
		parent::Handler();
	}

	/**
	 * Display the information page for the press..
	 */
	function index($args) {
		$this->validate();
		$this->setupTemplate();
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
		$this->index(array('readers'));
	}

	function authors() {
		$this->index(array('authors'));
	}

	function librarians() {
		$this->index(array('librarians'));
	}

	function competingInterestGuidelines() {
		$this->index(array('competingInterestGuidelines'));
	}

	function sampleCopyrightWording() {
		$this->index(array('sampleCopyrightWording'));
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
