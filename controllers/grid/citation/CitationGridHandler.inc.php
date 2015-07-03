<?php

/**
 * @file controllers/grid/citation/CitationGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CitationGridHandler
 * @ingroup controllers_grid_citation
 *
 * @brief Handle OJS specific parts of citation grid requests.
 */

import('lib.pkp.classes.controllers.grid.citation.PKPCitationGridHandler');

class CitationGridHandler extends PKPCitationGridHandler {
	/**
	 * Constructor
	 */
	function CitationGridHandler() {
		parent::PKPCitationGridHandler();
		$this->addRoleAssignment(
				array(ROLE_ID_MANAGER, ROLE_ID_SERIES_EDITOR),
				array('fetchGrid', 'addCitation', 'editCitation', 'updateRawCitation',
					'checkCitation', 'updateCitation', 'deleteCitation', 'exportCitations',
					'fetchCitationFormErrorsAndComparison', 'sendAuthorQuery'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		// Make sure the user can edit the submission in the request.
		import('classes.security.authorization.OjsSubmissionAccessPolicy');
		$this->addPolicy(new OjsSubmissionAccessPolicy($request, $args, $roleAssignments, 'assocId'));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request, $args) {
		// Associate the citation editor with the authorized article.
		$this->setAssocType(ASSOC_TYPE_SUBMISSION);
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
		assert(is_a($submission, 'Submission'));
		$this->setAssocObject($submission);

		parent::initialize($request, $args);
	}

	//
	// Override methods from PKPCitationGridHandler
	//
	/**
	 * @see PKPCitationGridHandler::exportCitations()
	 */
	function exportCitations($args, $request) {
		$dispatcher = $this->getDispatcher();
		$articleMetadataUrl = $dispatcher->url($request, ROUTE_PAGE, null, 'editor', 'viewMetadata', $this->getAssocId());
		$noCitationsFoundMessage = __("submission.citations.editor.pleaseImportCitationsFirst", array('articleMetadataUrl' => $articleMetadataUrl));
		return parent::exportCitations($args, $request, $noCitationsFoundMessage);
	}
}
