<?php

/**
 * @file pages/search/SearchHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class SearchHandler
 * @ingroup pages_search
 *
 * @brief Handle site index requests.
 */

import('classes.search.MonographSearch');
import('classes.handler.Handler');

class SearchHandler extends Handler {

	/**
	 * Show the search form
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, $request) {
		$this->search($args, $request);
	}

	/**
	 * View the results of a search operation.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string
	 */
	function search($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		$press = $request->getPress();
		$this->setupTemplate($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION);

		$query = $request->getUserVar('query');
		$templateMgr->assign('searchQuery', $query);

		// Get the range info.
		$rangeInfo = $this->getRangeInfo($request, 'search');

		// Fetch the monographs to display
		$monographSearch = new MonographSearch();
		$error = null;
		$results = $monographSearch->retrieveResults($request, $press, array(null => $query), $error, null, null, $rangeInfo);
		$templateMgr->assign('results', $results);

		// Display
		$templateMgr->display('frontend/pages/search.tpl');
	}
}


