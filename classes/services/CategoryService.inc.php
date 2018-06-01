<?php

/**
 * @file classes/services/CategoryService.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CategoryService
 * @ingroup services
 *
 * @brief Helper class that encapsulates category business logic
 */

namespace OMP\Services;

use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;
use \DBResultRange;
use \DAORegistry;
use \DAOResultFactory;

class CategoryService extends PKPBaseEntityPropertyService {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct($this);
	}

	/**
	 * Get categories
	 *
	 * @param int $contextId
	 * @param array $args {
	 * 		@option int parentId
	 * }
	 *
	 * @return array
	 */
	public function getCategories($contextId, $args = array()) {
		$categories = null;
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		if (isset($args['parentId']) && !is_null($args['parentId'])) {
			$categories = $categoryDao->getByParentId($args['parentId'], $contextId);
		}
		else {
			$categories = $categoryDao->getByPressId($contextId);
		}
		$data = array();
		while ($category = $categories->next()) {
			$data[] = $category;
		}
		return $data;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($category, $props, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();
		$values = array();
		foreach ($props as $prop) {
			switch ($prop) {
				case 'id':
					$values[$prop] = (int) $category->getId();
					break;
				case 'seq':
					$values[$prop] = (int) $category->getSequence();
					break;
				case '_parent':
					$values[$prop] = null;
					$parentId = $category->getParentId();
					if (!empty($args['slimRequest']) && $parentId) {
						$route = $args['slimRequest']->getAttribute('route');
						$arguments = $route->getArguments();
						$values[$prop] = $this->getAPIHref(
							$args['request'],
							$arguments['contextPath'],
							$arguments['version'],
							'categories',
							$parentId
						);
					}
					break;
				case 'path':
					$values[$prop] = $category->getPath();
					break;
				case 'title':
					$values[$prop] = $category->getLocalizedTitle();
					break;
				case 'description':
					$values[$prop] = $category->getLocalizedDescription();
					break;
				case 'image':
					$values[$prop] = $category->getImage();
					break;
				case 'sort':
					$values[$prop] = $category->getSortOption();
					break;
			}
		}
		\HookRegistry::call('Category::getProperties::values', array(&$values, $category, $props, $args));
		return $values;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 */
	public function getSummaryProperties($category, $args = null) {
		$props = array('id', '_parent', 'path', 'title','seq');
		\HookRegistry::call('Category::getProperties::summaryProperties', array(&$props, $category, $args));
		return $this->getProperties($category, $props, $args);
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 */
	public function getFullProperties($category, $args = null) {
		$props = array('id', '_parent', 'path', 'title', 'description', 'image', 'sort','seq');
		\HookRegistry::call('Category::getProperties::fullProperties', array(&$props, $category, $args));
		return $this->getProperties($category, $props, $args);
	}
}
