<?php

/**
 * @file api/v1/series/SeriesHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesHandler
 * @ingroup api_v1_user
 *
 * @brief Handle API requests for user operations.
 *
 */

import('lib.pkp.classes.handler.APIHandler');
import('classes.core.ServicesContainer');

class SeriesHandler extends APIHandler {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_handlerPath = 'series';
		$roles = array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR);
		$this->_endpoints = array(
			'GET' => array (
				array(
					'pattern' => $this->getEndpointPattern(),
					'handler' => array($this, 'getSeriesList'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{seriesId}',
					'handler' => array($this, 'getSeries'),
					'roles' => $roles
				),
			)
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
		import('lib.pkp.classes.security.authorization.ContextRequiredPolicy');
		$this->addPolicy(new ContextRequiredPolicy($request));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Get the list of series
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param array $args arguments
	 *
	 * @return Response
	 */
	public function getSeriesList($slimRequest, $response, $args) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$seriesService = ServicesContainer::instance()->get('series');

		if (!$context) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$items = array();
		$params = array();
		$series = $seriesService->getSeries($context->getId(), $params);
		if (!empty($series)) {
			$propertyArgs = array(
				'request' => $request,
				'slimRequest' => $slimRequest,
			);
			foreach ($series as $serie) {
				$items[] = $seriesService->getSummaryProperties($serie, $propertyArgs);
			}
		}
		
		return $response->withJson($items, 200);
	}

	/**
	 * Get a single series
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param array $args arguments
	 *
	 * @return Response
	 */
	public function getSeries($slimRequest, $response, $args) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($this->getParameter('seriesId'), $context->getId());
		
		if (!$series) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}
		
		$data = ServicesContainer::instance()
				->get('series')
				->getFullProperties($series, array(
					'request' => $request,
					'slimRequest' => $slimRequest,
				)
			);
		
		return $response->withJson($data, 200);
	}
}
