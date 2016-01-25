<?php

/**
 * @file classes/oai/omp/OAIDAO.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIDAO
 * @ingroup oai_omp
 * @see OAI
 *
 * @brief DAO operations for the OMP OAI interface.
 */

import('lib.pkp.classes.oai.PKPOAIDAO');

class OAIDAO extends PKPOAIDAO {

	/** @var PublicationFormatDAO */
	var $_publicationFormatDao;

	/** @var PublishedMonographDAO */
	var $_publishedMonographDao;

	/** @var SeriesDAO */
	var $_seriesDao;

	/** @var PressDAO */
	var $_pressDao;

	/** @var array */
	var $_pressCache;

	/** @var array */
	var $_seriesCache;

	/**
	 * Constructor.
	 */
	function OAIDAO() {
		parent::PKPOAIDAO();

		$this->_publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$this->_publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$this->_seriesDao = DAORegistry::getDAO('SeriesDAO');
		$this->_pressDao = DAORegistry::getDAO('PressDAO');
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getEarliestDatestamp()
	 */
	function getEarliestDatestamp($setIds) {
		return parent::getEarliestDatestamp('SELECT	MIN(COALESCE(dot.date_deleted, ms.last_modified))', $setIds);
	}

	/**
	 * Cached function to get a press
	 * @param $pressId int
	 * @return Press
	 */
	function getPress($pressId) {
		if (!isset($this->_pressCache[$pressId])) {
			$this->_pressCache[$pressId] = $this->_pressDao->getById($pressId);
		}
		return $this->_pressCache[$pressId];
	}

	/**
	 * Cached function to get a press series
	 * @param $seriesId int
	 * @return Series
	 */
	function getSeries($seriesId) {
		if (!isset($this->_seriesCache[$seriesId])) {
			$this->_seriesCache[$seriesId] = $this->_seriesDao->getById($seriesId);
		}
		return $this->_seriesCache[$seriesId];
	}

	//
	// Sets
	//

	/**
	 * Return hierarchy of OAI sets (presses plus press series).
	 * @param $pressId int
	 * @param $offset int
	 * @param $total int
	 * @return array OAISet
	 */
	function getSets($pressId = null, $offset, $limit, &$total) {
		if (isset($pressId)) {
			$presses = array($this->getPress($pressId));
		} else {
			$pressFactory = $this->_pressDao->getAll();
			$presses = $pressFactory->toArray();
		}

		// FIXME Set descriptions
		$sets = array();
		foreach ($presses as $press) {
			$title = $press->getLocalizedName();
			$abbrev = $press->getPath();
			array_push($sets, new OAISet(urlencode($abbrev), $title, ''));

			$dataObjectTombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
			$publicationFormatSets = $dataObjectTombstoneDao->getSets(ASSOC_TYPE_PRESS, $press->getId());

			$seriesFactory = $this->_seriesDao->getByPressId($press->getId());
			foreach ($seriesFactory->toArray() as $series) {
				if (array_key_exists(urlencode($abbrev) . ':' . urlencode($series->getPath()), $publicationFormatSets)) {
					unset($publicationFormatSets[urlencode($abbrev) . ':' . urlencode($series->getPath())]);
				}
				array_push($sets, new OAISet(urlencode($abbrev) . ':' . urlencode($series->getPath()), $series->getLocalizedTitle(), ''));
			}
			foreach ($publicationFormatSets as $publicationFormatSetSpec => $publicationFormatSetName) {
				array_push($sets, new OAISet($publicationFormatSetSpec, $publicationFormatSetName, ''));
			}
		}

		HookRegistry::call('OAIDAO::getSets', array(&$this, $pressId, $offset, $limit, $total, &$sets));

		$total = count($sets);
		$sets = array_slice($sets, $offset, $limit);

		return $sets;
	}

	/**
	 * Return the press ID and series ID corresponding to a press/series pairing.
	 * @param $pressSpec string
	 * @param $seriesSpec string
	 * @param $restrictPressId int
	 * @return array (int, int, int)
	 */
	function getSetPressSeriesId($pressSpec, $seriesSpec, $restrictPressId = null) {
		$press = $this->_pressDao->getByPath($pressSpec);
		if (!isset($press) || (isset($restrictPressId) && $press->getId() != $restrictPressId)) {
			return array(0, 0);
		}

		$pressId = $press->getId();
		$seriesId = null;

		if (isset($seriesSpec)) {
			$series = $this->_seriesDao->getByPath($seriesSpec, $press->getId());
			if ($series && is_a($series, 'Series')) {
				$seriesId = $series->getId();
			} else {
				$seriesId = 0;
			}
		}

		return array($pressId, $seriesId);
	}


	//
	// Protected methods.
	//
	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getRecordSelectStatement()
	 */
	function getRecordSelectStatement() {
		return 'SELECT	COALESCE(dot.date_deleted, ms.last_modified) AS last_modified,
			COALESCE(pf.publication_format_id, dot.data_object_id) AS data_object_id,
			COALESCE(p.press_id, tsop.assoc_id) AS press_id,
			COALESCE(tsos.assoc_id, s.series_id) AS series_id,
			dot.tombstone_id,
			dot.set_spec,
			dot.oai_identifier';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getRecordJoinClause()
	 */
	function getRecordJoinClause($publicationFormatId = null, $setIds = array(), $set = null) {
		assert(is_array($setIds));
		list($pressId, $seriesId) = array_pad($setIds, 2, null);
		return 'LEFT JOIN publication_formats pf ON (m.i=0' . (isset($publicationFormatId) ? ' AND pf.publication_format_id = ?' : '') . ')
			LEFT JOIN published_submissions ps ON (ps.submission_id = pf.submission_id)
			LEFT JOIN submissions ms ON (ms.submission_id = ps.submission_id' . (isset($pressId) ? ' AND ms.context_id = ?' : '') . (isset($seriesId) && $seriesId != 0 ? ' AND ms.series_id = ?' : '') .')
			LEFT JOIN series s ON (s.series_id = ms.series_id)
			LEFT JOIN presses p ON (p.press_id = ms.context_id)
			LEFT JOIN data_object_tombstones dot ON (m.i = 1' . (isset($publicationFormatId) ? ' AND dot.data_object_id = ?' : '') . (isset($set) ? ' AND dot.set_spec = ?' : '') . ')
			LEFT JOIN data_object_tombstone_oai_set_objects tsop ON ' . (isset($pressId) ? '(tsop.tombstone_id = dot.tombstone_id AND tsop.assoc_type = ' . ASSOC_TYPE_PRESS . ' AND tsop.assoc_id = ?)' : 'tsop.assoc_id = null') .
			' LEFT JOIN data_object_tombstone_oai_set_objects tsos ON ' . (isset($seriesId) ? '(tsos.tombstone_id = dot.tombstone_id AND tsos.assoc_type = ' . ASSOC_TYPE_SERIES . ' AND tsos.assoc_id = ?)' : 'tsos.assoc_id = null');
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getAccessibleRecordWhereClause()
	 */
	function getAccessibleRecordWhereClause() {
		return 'WHERE ((p.enabled = 1 AND ms.status <> ' . STATUS_DECLINED . ' AND pf.is_available = 1) OR dot.data_object_id IS NOT NULL)';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getDateRangeWhereClause()
	 */
	function getDateRangeWhereClause($from, $until) {
		return (isset($from) ? ' AND ((dot.date_deleted IS NOT NULL AND dot.date_deleted >= '. $this->datetimeToDB($from) .') OR (dot.date_deleted IS NULL AND ms.last_modified >= ' . $this->datetimeToDB($from) .'))' : '')
			. (isset($until) ? ' AND ((dot.date_deleted IS NOT NULL AND dot.date_deleted <= ' .$this->datetimeToDB($until) .') OR (dot.date_deleted IS NULL AND ms.last_modified <= ' . $this->datetimeToDB($until) .'))' : '')
			. ' ORDER BY press_id';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::setOAIData()
	 */
	function setOAIData($record, $row, $isRecord = true) {
		$press = $this->getPress($row['press_id']);
		$series = $this->getSeries($row['series_id']);
		$publicationFormatId = $row['data_object_id'];

		$record->identifier = $this->oai->publicationFormatIdToIdentifier($publicationFormatId);
		$record->sets = array(urlencode($press->getPath()) . ($series?':' . urlencode($series->getPath()):''));

		if ($isRecord) {
			$publicationFormat = $this->_publicationFormatDao->getById($publicationFormatId);
			$monograph = $this->_publishedMonographDao->getById($publicationFormat->getMonographId());
			$record->setData('publicationFormat', $publicationFormat);
			$record->setData('monograph', $monograph);
			$record->setData('press', $press);
			$record->setData('series', $series);
		}

		return $record;
	}
}

?>
