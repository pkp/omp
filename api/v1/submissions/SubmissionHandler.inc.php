<?php

/**
 * @file api/v1/submission/SubmissionHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionHandler
 * @ingroup api_v1_submission
 *
 * @brief Handle API requests for submission operations.
 *
 */

import('lib.pkp.api.v1.submissions.PKPSubmissionHandler');
import('classes.core.ServicesContainer');

class SubmissionHandler extends PKPSubmissionHandler {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_handlerPath = 'submissions';
		$roles = array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR);
		$this->_endpoints = array(
			'GET' => array (
				array(
					'pattern' => $this->getEndpointPattern(),
					'handler' => array($this, 'getSubmissionList'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}',
					'handler' => array($this, 'getSubmission'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/galleys',
					'handler' => array($this, 'getGalleys'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/participants',
					'handler' => array($this, 'getParticipants'),
					'roles' => array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/participants/{stageId}',
					'handler' => array($this, 'getParticipants'),
					'roles' => array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
				),
			),
		);
		parent::__construct();
	}

	//
	// Implement methods from PKPHandler
	//
	function authorize($request, &$args, $roleAssignments) {
		$routeName = null;
		$slimRequest = $this->getSlimRequest();

		if (!is_null($slimRequest) && ($route = $slimRequest->getAttribute('route'))) {
			$routeName = $route->getName();
		}

		if ($routeName === 'getSubmission' || $routeName === 'getGalleys' || $routeName === 'getParticipants') {
			import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
			$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}
}
