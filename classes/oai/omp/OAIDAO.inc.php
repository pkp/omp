<?php

/**
 * @file classes/oai/ojs/OAIDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OAIDAO
 * @ingroup oai_ojs
 * @see OAI
 *
 * @brief DAO operations for the OJS OAI interface.
 */

import('lib.pkp.classes.oai.PKPOAIDAO');

class OAIDAO extends PKPOAIDAO {

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

		$this->_publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$this->_seriesDao = DAORegistry::getDAO('SeriesDAO');
		$this->_pressDao = DAORegistry::getDAO('PressDAO');
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getEarliestDatestamp()
	 */
	function getEarliestDatestamp($setIds) {
		return parent::getEarliestDatestamp('SELECT	MIN(COALESCE(st.date_deleted, ms.last_modified))', $setIds);
	}

	/**
	 * Cached function to get a press
	 * @param $pressId int
	 * @return Press
	 */
	function &getPress($pressId) {
		if (!isset($this->_pressCache[$pressId])) {
			$this->_pressCache[$pressId] =& $this->_pressDao->getById($pressId);
		}
		return $this->_pressCache[$pressId];
	}

	/**
	 * Cached function to get a press series
	 * @param $seriesId int
	 * @return Series
	 */
	function &getSeries($seriesId) {
		if (!isset($this->_seriesCache[$seriesId])) {
			$this->_seriesCache[$seriesId] =& $this->_seriesDao->getById($seriesId);
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
	function &getSets($pressId = null, $offset, $limit, &$total) {
		if (isset($pressId)) {
			$presses = array($this->getPress($pressId));
		} else {
			$pressFactory =& $this->_pressDao->getPresses();
			$presses =& $pressFactory->toArray();
		}

		// FIXME Set descriptions
		$sets = array();
		foreach ($presses as $press) {
			$title = $press->getLocalizedName();
			$abbrev = $press->getPath();
			array_push($sets, new OAISet(urlencode($abbrev), $title, ''));

			$monographTombstoneDao =& DAORegistry::getDAO('MonographTombstoneDAO');
			$monographTombstoneSets = $monographTombstoneDao->getSets($press->getId());

			$seriesFactory =& $this->_seriesDao->getByPressId($press->getId());
			foreach ($seriesFactory->toArray() as $series) {
				if (array_key_exists(urlencode($abbrev) . ':' . urlencode($series->getPath()), $monographTombstoneSets)) {
					unset($monographTombstoneSets[urlencode($abbrev) . ':' . urlencode($series->getPath())]);
				}
				array_push($sets, new OAISet(urlencode($abbrev) . ':' . urlencode($series->getPath()), $series->getLocalizedTitle(), ''));
			}
			foreach ($monographTombstoneSets as $monographTombstoneSetSpec => $monographTombstoneSetName) {
				array_push($sets, new OAISet($monographTombstoneSetSpec, $monographTombstoneSetName, ''));
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
		$pressId = null;

		$press =& $this->_pressDao->getPressByPath($pressSpec);
		if (!isset($press) || (isset($restrictPressId) && $press->getId() != $restrictPressId)) {
			return array(0, 0);
		}

		$pressId = $press->getId();
		$seriesId = null;

		if (isset($seriesSpec)) {
			$series =& $this->_seriesDao->getByPath($seriesSpec, $press->getId());
			if (is_a($series, 'Series')) {
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
		return 'SELECT	COALESCE(st.date_deleted, ms.last_modified) AS last_modified,
			COALESCE(ms.monograph_id, st.submission_id) AS submission_id,
			COALESCE(p.press_id, st.press_id) AS press_id,
			COALESCE(st.series_id, s.series_id) AS series_id,
			st.tombstone_id,
			st.set_spec,
			st.oai_identifier';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getRecordJoinClause()
	 */
	function getRecordJoinClause($monographId = null, $setIds = array(), $set = null) {
		assert(is_array($setIds));
		list($pressId, $seriesId) = $setIds;
		return 'LEFT JOIN published_monographs pm ON (m.i=0' . (isset($monographId) ? ' AND pm.monograph_id = ?' : '') . ')
			LEFT JOIN monographs ms ON (ms.monograph_id = pm.monograph_id' . (isset($pressId) ? ' AND ms.press_id = ?' : '') . (isset($seriesId) && $seriesId != 0 ? ' AND ms.series_id = ?' : '') .')
			LEFT JOIN series s ON (s.series_id = ms.series_id)
			LEFT JOIN presses p ON (p.press_id = ms.press_id)
			LEFT JOIN submission_tombstones st ON (m.i = 1' . (isset($monographId) ? ' AND st.submission_id = ?' : '') . (isset($pressId) ? ' AND st.press_id = ?' : '') . (isset($seriesId) && $seriesId != 0 ? ' AND st.series_id = ?' : '') . (isset($set) ? ' AND st.set_spec = ?' : '') .')';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getAccessibleRecordWhereClause()
	 */
	function getAccessibleRecordWhereClause() {
		return 'WHERE ((s.series_id IS NOT NULL AND p.enabled = 1 AND ms.status <> ' . STATUS_ARCHIVED . ') OR st.submission_id IS NOT NULL)';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::getDateRangeWhereClause()
	 */
	function getDateRangeWhereClause($from, $until) {
		return (isset($from) ? ' AND ((st.date_deleted IS NOT NULL AND st.date_deleted >= '. $this->datetimeToDB($from) .') OR (st.date_deleted IS NULL AND a.last_modified >= ' . $this->datetimeToDB($from) .'))' : '')
			. (isset($until) ? ' AND ((st.date_deleted IS NOT NULL AND st.date_deleted <= ' .$this->datetimeToDB($until) .') OR (st.date_deleted IS NULL AND a.last_modified <= ' . $this->datetimeToDB($until) .'))' : '')
			. ' ORDER BY press_id';
	}

	/**
	 * @see lib/pkp/classes/oai/PKPOAIDAO::setOAIData()
	 */
	function &setOAIData(&$record, &$row, $isRecord = true) {
		$press =& $this->getPress($row['press_id']);
		$series =& $this->getSeries($row['series_id']);
		$monographId = $row['submission_id'];

		$record->identifier = $this->oai->monographIdToIdentifier($monographId);
		$record->sets = array(urlencode($press->getPath()) . ':' . urlencode($series->getPath()));

		if ($isRecord) {
			$publishedMonograph =& $this->_publishedMonographDao->getById($monographId);
			$record->setData('monograph', $publishedMonograph);
			$record->setData('press', $press);
			$record->setData('series', $series);
		}

		return $record;
	}
}

?>
