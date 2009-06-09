<?php

/**
 * @file MonographHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographHandler
 * @ingroup pages_monograph
 *
 * @brief Handle requests for monograph functions.
 */

// $Id$


import ('monograph.MonographAction');
import('handler.Handler');

class MonographHandler extends Handler {
	function MonographHandler() {
		parent::Handler();
		
		$this->addCheck(new HandlerValidatorPress($this));
	}		

	/**
	 * Display about index page.
	 */
	function index($args) {
		$this->current();
	}

	/**
	 * Display the monograph page.
	 */
	function current($args = null) {
		$this->validate();

		$press =& Request::getPress();

		//FIXME deal with this function
	}

	/**
	 * Display monograph view page.
	 */
	function view($args) {
		$this->validate();

		$monographId = isset($args[0]) ? $args[0] : 0;
		$showToc = isset($args[1]) ? $args[1] : '';

		$press =& Request::getPress();

		//FIXME deal with this function

	}

	/**
	 * Given a monograph, set up the template with all the required variables.
	 * @param $monograph object The monograph to display
	 * @param $showToc boolean iff false and a custom cover page exists,
	 * 	the cover page will be displayed. Otherwise table of contents
	 * 	will be displayed.
	 */
	function setupTemplate(&$monograph, $showToc = false) {
		parent::setupTemplate();
	}

	/**
	 * Display the published monograph listings
	 */
	function published() {
		$this->validate();
		//FIXME deal with this function
		$press =& Request::getPress();
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$rangeInfo = Handler::getRangeInfo('monographs');

		$publishedMonographsIterator = $monographDao->getPublishedMonographs($press->getId(), $rangeInfo);

		import('file.PublicFileManager');
		$publicFileManager =& new PublicFileManager();
		$coverPagePath = Request::getBaseUrl() . '/';
		$coverPagePath .= $publicFileManager->getPressFilesPath($press->getId()) . '/';

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('coverPagePath', $coverPagePath);
		$templateMgr->assign('locale', Locale::getLocale());
		$templateMgr->display('monograph/published.tpl');
	}

}

?>
