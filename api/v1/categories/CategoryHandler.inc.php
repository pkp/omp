<?php

/**
 * @file api/v1/user/CategoryHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryHandler
 * @ingroup api_v1_user
 *
 * @brief Handle API requests for user operations.
 *
 */

import('lib.pkp.classes.handler.APIHandler');
import('classes.core.ServicesContainer');

class CategoryHandler extends APIHandler {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->_handlerPath = 'categories';
		$roles = array(ROLE_ID_MANAGER, ROLE_ID_SUB_EDITOR, ROLE_ID_ASSISTANT, ROLE_ID_REVIEWER, ROLE_ID_AUTHOR);
		$this->_endpoints = array(
			'GET' => array (
				array(
					'pattern' => $this->getEndpointPattern(),
					'handler' => array($this, 'getCategoryList'),
					'roles' => $roles
				),
				array(
					'pattern' => $this->getEndpointPattern() . '/{categoryId}',
					'handler' => array($this, 'getCategory'),
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
	 * Get the list of categories
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param array $args arguments
	 *
	 * @return Response
	 */
	public function getCategoryList($slimRequest, $response, $args) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$categoryService = ServicesContainer::instance()->get('category');
		
		if (!$context) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$items = array();
		$params = array();
		$categories = $categoryService->getCategories($context->getId(), $params);
		if (!empty($categories)) {
			$propertyArgs = array(
				'request' => $request,
				'slimRequest' => $slimRequest,
			);
			foreach ($categories as $category) {
				$items[] = $categoryService->getSummaryProperties($category, $propertyArgs);
			}
		}
		
		return $response->withJson($items, 200);
	}

	/**
	 * Get a single category
	 * @param $slimRequest Request Slim request object
	 * @param $response Response object
	 * @param array $args arguments
	 *
	 * @return Response
	 */
	public function getCategory($slimRequest, $response, $args) {
		$request = $this->getRequest();
		$context = $request->getContext();
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$category = $categoryDao->getById($this->getParameter('categoryId'), $context->getId());

		if (!$category) {
			return $response->withStatus(404)->withJsonError('api.submissions.404.resourceNotFound');
		}

		$data = ServicesContainer::instance()
				->get('category')
				->getFullProperties($category, array(
					'request' => $request,
					'slimRequest' => $slimRequest,
				)
			);

		return $response->withJson($data, 200);
	}
}
