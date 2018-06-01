<?php

/**
 * @file classes/services/SeriesService.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesService
 * @ingroup services
 *
 * @brief Helper class that encapsulates series business logic
 */

namespace OMP\Services;

use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;
use \DBResultRange;
use \DAORegistry;
use \DAOResultFactory;

class SeriesService extends PKPBaseEntityPropertyService {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct($this);
	}

	/**
	 * Get series
	 *
	 * @param int $contextId
	 * @param array $args {
	 * }
	 *
	 * @return array
	 */
	public function getSeries($contextId, $args = array()) {
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getByPressId($contextId);
		$data = array();
		while ($serie = $series->next()) {
			$data[] = $serie;
		}
		return $data;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($series, $props, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();
		$values = array();
		foreach ($props as $prop) {
			switch ($prop) {
				case 'id':
					$values[$prop] = (int) $series->getId();
					break;
				case 'title':
					$values[$prop] = $series->getLocalizedTitle();
					break;
			}
		}
		\HookRegistry::call('Series::getProperties::values', array(&$values, $series, $props, $args));
		return $values;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 */
	public function getSummaryProperties($series, $args = null) {
		$props = array('id', 'title');
		\HookRegistry::call('Series::getProperties::summaryProperties', array(&$props, $series, $args));
		return $this->getProperties($series, $props, $args);
	}
	
	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 */
	public function getFullProperties($series, $args = null) {
		$props = array('id', 'title');
		\HookRegistry::call('Series::getProperties::fullProperties', array(&$props, $series, $args));
		return $this->getProperties($series, $props, $args);
	}
}
