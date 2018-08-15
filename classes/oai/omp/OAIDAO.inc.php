<?php

/**
 * @file classes/oai/omp/OAIDAO.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
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
	function __construct() {
		parent::__construct();

		$this->_publicationFormatDao = DAORegistry::getDAO('PublicationFormatDAO');
		$this->_publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		$this->_seriesDao = DAORegistry::getDAO('SeriesDAO');
		$this->_pressDao = DAORegistry::getDAO('PressDAO');
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

	/**
	 * Get a OAI records record set.
	 * @param $setIds array Objects ids that specify an OAI set,
	 * in hierarchical order.
	 * @param $from int/string *nix timestamp or ISO datetime string
	 * @param $until int/string *nix timestamp or ISO datetime string
	 * @param $set string
	 * @param $submissionId int Optional
	 * @param $orderBy string UNFILTERED
	 * @return ADORecordSet
	 */
	function _getRecordsRecordSet($setIds, $from, $until, $set, $submissionId = null, $orderBy = 'press_id, data_object_id') {
		$pressId = array_shift($setIds);
		$seriesId = array_shift($setIds);

		$params = array();
		if ($pressId) $params[] = (int) $pressId;
		if ($seriesId) $params[] = (int) $seriesId;
		if ($submissionId) $params[] = (int) $submissionId;
		if ($pressId) $params[] = (int) $pressId;
		if ($seriesId) $params[] = (int) $seriesId;
		if (isset($set)) $params[] = $set;
		if ($submissionId) $params[] = (int) $submissionId;

		$result = $this->retrieve(
			'SELECT	ms.last_modified AS last_modified,
				pf.publication_format_id AS data_object_id,
				p.press_id AS press_id,
				s.series_id AS series_id,
				NULL AS tombstone_id,
				NULL AS set_spec,
				NULL AS oai_identifier
			FROM	publication_formats pf
				JOIN published_submissions ps ON (ps.submission_id = pf.submission_id)
				JOIN submissions ms ON (ms.submission_id = ps.submission_id)
				LEFT JOIN series s ON (s.series_id = ms.series_id)
				JOIN presses p ON (p.press_id = ms.context_id)
			WHERE	p.enabled = 1
				' . ($pressId?' AND p.press_id = ?':'') . '
				' . ($seriesId?' AND s.series_id = ?':'') . '
				AND ms.status <> ' . STATUS_DECLINED . '
				AND pf.is_available = 1
				AND ps.date_published IS NOT NULL
				' . ($from?' AND ms.last_modified >= ' . $this->datetimeToDB($from):'') . '
				' . ($until?' AND ms.last_modified <= ' . $this->datetimeToDB($until):'') . '
				' . ($submissionId?' AND pf.publication_format_id=?':'') . '
			UNION
			SELECT	dot.date_deleted AS last_modified,
				dot.data_object_id AS data_object_id,
				tsop.assoc_id AS press_id,
				tsos.assoc_id AS series_id,
				dot.tombstone_id,
				dot.set_spec,
				dot.oai_identifier
			FROM
				data_object_tombstones dot
				LEFT JOIN data_object_tombstone_oai_set_objects tsop ON ' . (isset($pressId) ? '(tsop.tombstone_id = dot.tombstone_id AND tsop.assoc_type = ' . ASSOC_TYPE_PRESS . ' AND tsop.assoc_id = ?)' : 'tsop.assoc_id = null') . '
				LEFT JOIN data_object_tombstone_oai_set_objects tsos ON ' . (isset($seriesId) ? '(tsos.tombstone_id = dot.tombstone_id AND tsos.assoc_type = ' . ASSOC_TYPE_SERIES . ' AND tsos.assoc_id = ?)' : 'tsos.assoc_id = null') . '
			WHERE	1=1
				' . ($from?' AND dot.date_deleted >= ' . $this->datetimeToDB($from):'') . '
				' . ($until?' AND dot.date_deleted <= ' . $this->datetimeToDB($until):'') . '
				' . (isset($set)?' AND dot.set_spec = ?':'') . '
				' . ($submission?' dot.data_object_id = ?':'') . '
			ORDER BY ' . $orderBy,
			$params
		);

		return $result;
	}
}


