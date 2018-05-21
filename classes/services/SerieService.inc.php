<?php

/**
 * @file classes/services/SerieService.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SerieService
 * @ingroup services
 *
 * @brief Helper class that encapsulates serie business logic
 */

namespace OMP\Services;

use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;
use \DBResultRange;
use \DAORegistry;
use \DAOResultFactory;

class SerieService extends PKPBaseEntityPropertyService {
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
	public function getProperties($serie, $props, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();
		$values = array();
		foreach ($props as $prop) {
			switch ($prop) {
				case 'id':
					$values[$prop] = (int) $serie->getId();
					break;
				case 'title':
					$values[$prop] = $serie->getLocalizedTitle();
					break;
			}
		}
		\HookRegistry::call('Serie::getProperties::values', array(&$values, $serie, $props, $args));
		return $values;
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getSummaryProperties()
	 */
	public function getSummaryProperties($serie, $args = null) {
		$props = array('id', 'title');
		\HookRegistry::call('Serie::getProperties::summaryProperties', array(&$props, $serie, $args));
		return $this->getProperties($serie, $props, $args);
	}
	
	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getFullProperties()
	 */
	public function getFullProperties($serie, $args = null) {
		$props = array('id', 'title');
		\HookRegistry::call('Serie::getProperties::fullProperties', array(&$props, $serie, $args));
		return $this->getProperties($serie, $props, $args);
	}
}
