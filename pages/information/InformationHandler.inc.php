<?php

/**
 * @file InformationHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class InformationHandler
 * @ingroup pages_information
 *
 * @brief Display press information.
 */

// $Id$


import('classes.handler.Handler');

class InformationHandler extends Handler {
	/**
	 * Constructor
	 */
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
		$contentOnly = Request::getUserVar('contentOnly');

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

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('pageCrumbTitle', $pageCrumbTitle);
		$templateMgr->assign('pageTitle', $pageTitle);
		$templateMgr->assign('content', $content);
		$templateMgr->assign('contentOnly', $contentOnly); // Hide the header and footer code

		import('lib.pkp.classes.core.JSON');
		$json = new JSON('true', $templateMgr->fetch('information/information.tpl'));
		return $json->getString();
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
		return $this->index(array('competingInterestGuidelines'));
	}

	function sampleCopyrightWording() {
		$this->index(array('sampleCopyrightWording'));
	}

	/**
	 * Initialize the template.
	 */
	function setupTemplate() {
		$press =& Request::getPress();
		Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER));
		$templateMgr =& TemplateManager::getManager();
		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
	}
}

?>
