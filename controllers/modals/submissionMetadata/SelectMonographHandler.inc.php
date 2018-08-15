<?php

/**
 * @file controllers/modals/submissionMetadata/SelectMonographHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SelectMonographHandler
 * @ingroup controllers_modals_submissionMetadata
 *
 * @brief Handle requests for a modal wrapper around the catalog entry form
 *   allowing monograph submission in a drop-down.
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSONMessage');

class SelectMonographHandler extends Handler {
	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
		$this->addRoleAssignment(
			array(ROLE_ID_SUB_EDITOR, ROLE_ID_MANAGER),
			array('fetch', 'select')
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Fetch the modal contents for the monograph selection form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function fetch($args, $request) {
		$templateMgr = TemplateManager::getManager($request);
		import('lib.pkp.controllers.list.submissions.SelectSubmissionsListHandler');
		$selectNewEntryHandler = new SelectSubmissionsListHandler(array(
			'title' => 'submission.catalogEntry.select',
			'count' => 20,
			'inputName' => 'selectedSubmissions[]',
			'getParams' => array(
				'status' => STATUS_QUEUED,
			),
		));
		$templateMgr->assign('selectNewEntryData', json_encode($selectNewEntryHandler->getConfig()));
		return new JSONMessage(true, $templateMgr->fetch('controllers/modals/submissionMetadata/selectMonograph.tpl'));
	}

	/**
	 * Add selected submissions to the catalog
	 *
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function select($args, $request) {

		if (!$request->checkCSRF() || !$context = $request->getContext()) {
			return new JSONMessage(false, __('form.csrfInvalid'));
		}

		$selectedSubmissions = empty($args['selectedSubmissions']) ? array() : array_map('intval', $args['selectedSubmissions']);

		if (empty($selectedSubmissions)) {
			return new JSONMessage(false, __('submission.catalogEntry.selectionMissing'));
		}

		import('classes.core.ServicesContainer');
		$submissionService = ServicesContainer::instance()->get('submission');
		$submissionDao = Application::getSubmissionDAO();
		foreach ($selectedSubmissions as $submissionId) {
			$submissionService->addToCatalog($submissionDao->getById($submissionId));
		}

		$json = new JSONMessage(true);
		$json->setGlobalEvent('catalogEntryAdded', array(
			'submissionsAdded' => $selectedSubmissions,
		));
		return $json;
	}
}


