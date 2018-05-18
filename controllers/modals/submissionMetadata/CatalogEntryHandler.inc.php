<?php

/**
 * @file controllers/modals/submissionMetadata/CatalogEntryHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogEntryHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle the request to generate the tab structure on the New Catalog Entry page.
 */

// Import the base Handler.
import('lib.pkp.controllers.modals.submissionMetadata.PublicationEntryHandler');

class CatalogEntryHandler extends PublicationEntryHandler {

	/** the selected format id **/
	var $_selectedFormatId;

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Overridden methods from Handler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $args = null) {
		$this->_selectedFormatId = (int) $request->getUserVar('selectedFormatId');
		parent::initialize($request, $args);
	}

	// Getters and Setters
	/**
	 * Get the selected format id.
	 * @return int
	 */
	function getSelectedFormatId() {
		return $this->_selectedFormatId;
	}

	//
	// Public handler methods
	//
	/**
	 * Display the tabs index page.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function fetch($args, $request) {
		parent::fetch($args, $request);

		$templateMgr = TemplateManager::getManager($request);

		$templateMgr->assign('selectedFormatId', $this->getSelectedFormatId());

		$submission = $this->getSubmission();

		// load in any publication formats assigned to this published monograph
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$formats = $publicationFormatDao->getBySubmissionId($submission->getId());
		$publicationFormats = array();
		while ($publicationFormat = $formats->next()) {
			$publicationFormats[] = $publicationFormat;
		}

		$templateMgr->assign_by_ref('publicationFormats', $publicationFormats);

		$request = Application::getRequest();
		$router = $request->getRouter();
		$dispatcher = $router->getDispatcher();

		// These two URLs are catalog/monograph specific.
		$tabsUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, 'modals.submissionMetadata.CatalogEntryHandler', 'fetchFormatInfo', null, array('submissionId' => $submission->getId(), 'stageId' => $this->getStageId()));
		$templateMgr->assign('tabsUrl', $tabsUrl);

		$tabContentUrl = $dispatcher->url($request, ROUTE_COMPONENT, null, 'tab.catalogEntry.CatalogEntryTabHandler', 'publicationMetadata', null, array('submissionId' => $submission->getId(), 'stageId' => $this->getStageId()));
		$templateMgr->assign('tabContentUrl', $tabContentUrl);

		return $templateMgr->fetchJson('controllers/modals/submissionMetadata/catalogEntryTabs.tpl');
	}

	/**
	 * Returns a JSON response containing information regarding the formats enabled
	 * for this submission.
	 * @param $args array
	 * @param $request Request
	 * @return JSONMessage JSON object
	 */
	function fetchFormatInfo($args, $request) {
		$submission = $this->getSubmission();
		$publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$formats = $publicationFormatDao->getBySubmissionId($submission->getId());

		$publicationFormats = array();
		while ($format = $formats->next()) {
			$publicationFormats[$format->getId()] = $format->getLocalizedName();
		}

		$json = new JSONMessage(true, true);
		$json->setAdditionalAttributes(array('formats' => $publicationFormats));
		return $json;
	}
}

?>
