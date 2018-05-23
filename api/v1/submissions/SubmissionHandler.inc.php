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
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/participants',
					'handler' => array($this, 'getParticipants'),
					'roles' => array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/participants/{stageId}',
					'handler' => array($this, 'getParticipants'),
					'roles' => array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR),
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{submissionId}/chapters',
					'handler' => array($this, 'getChapters'),
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

		if ($routeName === 'getSubmission' || $routeName === 'getChapters' || $routeName === 'getParticipants') {
			import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
			$this->addPolicy(new SubmissionAccessPolicy($request, $args, $roleAssignments));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Get the chapters assigned to a submission
	 *
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param array $args arguments
	 *
	 * @return Response
	 */
	public function getChapters($slimRequest, $response, $args) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$submission = $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);

		if (!$submission) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$publishedMonograph = null;
		if ($context) {
			$publishedMonographDao = \DAORegistry::getDAO('PublishedMonographDAO');
			$publishedMonograph = $publishedMonographDao->getByBestId($submission->getId(), $context->getId());
		}

		if (!$publishedMonograph) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$data = array();
		$chapters = array();
		$chapterDao = \DAORegistry::getDAO('ChapterDAO');
		$chaptersResult = $chapterDao->getChapters($publishedMonograph->getId());
		while ($chapter = $chaptersResult->next()) {
			$chapters[] = $chapter;
		}

		if (!empty($chapters)) {
			$args = array(
					'request' => $request,
					'slimRequest' => $slimRequest,
			);
			$chapterService = \ServicesContainer::instance()->get('chapter');
			$chapterArgs = array_merge(array('parent' => $publishedMonograph), $args);
			foreach ($chapters as $chapter) {
				$data[] = $chapterService->getFullProperties($chapter, $chapterArgs);
			}
		}

		return $response->withJson($data, 200);
	}
}
