<?php

/**
 * @file classes/monograph/PublishedMonographDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonographDAO
 * @ingroup monograph
 * @see PublishedMonograph
 *
 * @brief Operations for retrieving and modifying PublishedMonograph objects.
 */

// $Id$


import('monograph.PublishedMonograph');

class PublishedMonographDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $galleyDao;
	var $suppFileDao;

 	/**
	 * Constructor.
	 */
	function PublishedMonographDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
//		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
		$this->suppFileDao =& DAORegistry::getDAO('SuppFileDAO');
	}

	/**
	 * Retrieve Published Monographs by issue id.  Limit provides number of records to retrieve
	 * @param $issueId int
	 * @param $limit int, default NULL
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph objects array
	 */
	function &getPublishedMonographs($issueId, $limit = NULL, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$publishedMonographs = array();

		$params = array(
			$issueId,
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$issueId
		);

		$sql = 'SELECT DISTINCT
				pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
				COALESCE(o.seq, s.seq) AS section_seq,
				pa.seq
			FROM	published_monographs pa,
				monographs a LEFT JOIN sections s ON s.section_id = a.section_id
				LEFT JOIN custom_section_orders o ON (a.section_id = o.section_id AND o.issue_id = ?)
				LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND pa.issue_id = ?
				AND a.status <> ' . STATUS_ARCHIVED . '
			ORDER BY section_seq ASC, pa.seq ASC';

		if (isset($limit)) $result =& $this->retrieveLimit($sql, $params, $limit);
		else $result =& $this->retrieve($sql, $params);

		while (!$result->EOF) {
			$publishedMonographs[] =& $this->$func($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $publishedMonographs;
	}

	/**
	 * Retrieve a count of published monographs in a press.
	 */
	function getPublishedMonographCountByPressId($pressId) {
		$result =& $this->retrieve(
			'SELECT count(*) FROM published_monographs pa, monographs a WHERE pa.monograph_id = a.monograph_id AND a.press_id = ? AND a.status <> ' . STATUS_ARCHIVED,
			$pressId
		);
		list($count) = $result->fields;
		$result->Close();
		return $count;
	}

	/**
	 * Retrieve all published monographs in a press.
	 * @param $pressId int
	 * @param $rangeInfo object
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return object
	 */
	function &getPublishedMonographsByPressId($pressId, $rangeInfo = null, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$result =& $this->retrieveRange(
			'SELECT	pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM	published_monographs pa,
				monographs a
				LEFT JOIN sections s ON s.section_id = a.section_id
				LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND a.press_id = ?
				AND a.status <> ' . STATUS_ARCHIVED,
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$pressId
			),
			$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, $func);
		return $returner;
	}

	/**
	 * Retrieve Published Monographs by issue id
	 * @param $issueId int
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph objects array
	 */
	function &getPublishedMonographsInSections($issueId, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$publishedMonographs = array();

		$result =& $this->retrieve(
			'SELECT DISTINCT
				pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev,
				s.abstracts_not_required AS abstracts_not_required,
				s.hide_title AS section_hide_title,
				s.hide_author AS section_hide_author,
				COALESCE(o.seq, s.seq) AS section_seq,
				pa.seq
			FROM	published_monographs pa,
				monographs a
				LEFT JOIN sections s ON s.section_id = a.section_id
				LEFT JOIN custom_section_orders o ON (a.section_id = o.section_id AND o.issue_id = ?)
				LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND pa.issue_id = ?
				AND a.status <> ' . STATUS_ARCHIVED . '
			ORDER BY section_seq ASC, pa.seq ASC',
			array(
				$issueId,
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$issueId
			)
		);

		$currSectionId = 0;
		while (!$result->EOF) {
			$row =& $result->GetRowAssoc(false);
			$publishedMonograph =& $this->$func($row);
			if ($publishedMonograph->getSectionId() != $currSectionId) {
				$currSectionId = $publishedMonograph->getSectionId();
				$publishedMonographs[$currSectionId] = array(
					'monographs'=> array(),
					'title' => '',
					'abstractsNotRequired' => $row['abstracts_not_required'],
					'hideAuthor' => $row['section_hide_author']
				);

				if (!$row['section_hide_title']) {
					$publishedMonographs[$currSectionId]['title'] = $publishedMonograph->getSectionTitle();
				}
			}
			$publishedMonographs[$currSectionId]['monographs'][] = $publishedMonograph;
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $publishedMonographs;
	}

	/**
	 * Retrieve Published Monographs by section id
	 * @param $sectionId int
	 * @param $issueId int
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph objects array
	 */
	function &getPublishedMonographsBySectionId($sectionId, $issueId, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$publishedMonographs = array();

		$result =& $this->retrieve(
			'SELECT	pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM	published_monographs pa,
				monographs a,
				sections s
				LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.section_id = s.section_id
				AND pa.monograph_id = a.monograph_id
				AND a.section_id = ?
				AND pa.issue_id = ?
				AND a.status <> ' . STATUS_ARCHIVED . '
			ORDER BY pa.seq ASC',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$sectionId,
				$issueId
			)
		);

		$currSectionId = 0;
		while (!$result->EOF) {
			$publishedMonograph =& $this->$func($result->GetRowAssoc(false));
			$publishedMonographs[] = $publishedMonograph;
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $publishedMonographs;
	}

	/**
	 * Retrieve Published Monograph by pub id
	 * @param $pubId int
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph object
	 */
	function &getPublishedMonographById($pubId, $simple = false) {
		$result =& $this->retrieve(
			'SELECT * FROM published_monographs WHERE pub_id = ?', $pubId
		);
		$row = $result->GetRowAssoc(false);

		$publishedMonograph = new PublishedMonograph();
		$publishedMonograph->setPubId($row['pub_id']);
		$publishedMonograph->setMonographId($row['monograph_id']);
		$publishedMonograph->setIssueId($row['issue_id']);
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setSeq($row['seq']);
		$publishedMonograph->setViews($row['views']);
		$publishedMonograph->setAccessStatus($row['access_status']);

		if (!$simple) $publishedMonograph->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));

		$result->Close();
		unset($result);

		return $publishedMonograph;
	}

	/**
	 * Retrieve published monograph by monograph id
	 * @param $monographId int
	 * @param $pressId int optional
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph object
	 */
	function &getPublishedMonographByMonographId($monographId, $pressId = null, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$params = array(
			'title',
			$primaryLocale,
			'title',
			$locale,
			'abbrev',
			$primaryLocale,
			'abbrev',
			$locale,
			$monographId
		);
		if ($pressId) $params[] = $pressId;

		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$result =& $this->retrieve(
			'SELECT	pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM	published_monographs pa,
				monographs a
				LEFT JOIN acquisitions_arrangements s ON s.arrangement_id = a.arrangement_id
				LEFT JOIN acquisitions_arrangements_settings stpl ON (s.arrangement_id = stpl.arrangement_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings stl ON (s.arrangement_id = stl.arrangement_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sapl ON (s.arrangement_id = sapl.arrangement_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN acquisitions_arrangements_settings sal ON (s.arrangement_id = sal.arrangement_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND a.monograph_id = ?' .
				(isset($pressId)?' AND a.press_id = ?':''),
			$params
		);

		$publishedMonograph = null;
		if ($result->RecordCount() != 0) {
			$publishedMonograph =& $this->$func($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $publishedMonograph;
	}

	/**
	 * Retrieve published monograph by public monograph id
	 * @param $pressId int
	 * @param $publicMonographId string
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph object
	 */
	function &getPublishedMonographByPublicMonographId($pressId, $publicMonographId, $simple = false) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$func = $simple?'_returnSimplePublishedMonographFromRow':'_returnPublishedMonographFromRow';
		$result =& $this->retrieve(
			'SELECT	pa.*,
				a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS section_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS section_abbrev
			FROM	published_monographs pa,
				monographs a
				LEFT JOIN sections s ON s.section_id = a.section_id
				LEFT JOIN section_settings stpl ON (s.section_id = stpl.section_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN section_settings stl ON (s.section_id = stl.section_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN section_settings sapl ON (s.section_id = sapl.section_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN section_settings sal ON (s.section_id = sal.section_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND pa.public_monograph_id = ?
				AND a.press_id = ?',
			array(
				'title',
				$primaryLocale,
				'title',
				$locale,
				'abbrev',
				$primaryLocale,
				'abbrev',
				$locale,
				$publicMonographId,
				$pressId
			)
		);

		$publishedMonograph = null;
		if ($result->RecordCount() != 0) {
			$publishedMonograph =& $this->$func($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $publishedMonograph;
	}

	/**
	 * Retrieve published monograph by public monograph id or, failing that,
	 * internal monograph ID; public monograph ID takes precedence.
	 * @param $pressId int
	 * @param $monographId string
	 * @param $simple boolean Whether or not to skip fetching dependent objects; default false
	 * @return PublishedMonograph object
	 */
	function &getPublishedMonographByBestMonographId($pressId, $monographId, $simple = false) {
		$monograph =& $this->getPublishedMonographByPublicMonographId($pressId, $monographId, $simple);
		if (!isset($monograph)) $monograph =& $this->getPublishedMonographByMonographId((int) $monographId, $pressId, $simple);
		return $monograph;
	}

	/**
	 * Retrieve "monograph_id"s for published monographs for a press, sorted
	 * alphabetically.
	 * Note that if pressId is null, alphabetized monograph IDs for all
	 * presss are returned.
	 * @param $pressId int
	 * @return Array
	 */
	function &getPublishedMonographIdsAlphabetizedByPress($pressId = null, $useCache = true) {
		$params = array(
			'title', Locale::getLocale(),
			'title', Locale::getPrimaryLocale()
		);
		if (isset($pressId)) $params[] = $pressId;

		$monographIds = array();
		$functionName = $useCache?'retrieveCached':'retrieve';
		$result =& $this->$functionName(
			'SELECT	a.monograph_id AS pub_id,
				COALESCE(atl.setting_value, atpl.setting_value) AS monograph_title
			FROM	published_monographs pa,
				issues i,
				monographs a
				LEFT JOIN sections s ON s.section_id = a.section_id
				LEFT JOIN monograph_settings atl ON (a.monograph_id = atl.monograph_id AND atl.setting_name = ? AND atl.locale = ?)
				LEFT JOIN monograph_settings atpl ON (a.monograph_id = atpl.monograph_id AND atpl.setting_name = ? AND atpl.locale = ?)
			WHERE	pa.monograph_id = a.monograph_id
				AND i.issue_id = pa.issue_id
				AND i.published = 1
				AND s.section_id IS NOT NULL' .
				(isset($pressId)?' AND a.press_id = ?':'') . ' ORDER BY monograph_title',
			$params
		);

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$monographIds[] = $row['pub_id'];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographIds;
	}

	/**
	 * Retrieve "monograph_id"s for published monographs for a press, sorted
	 * by reverse publish date.
	 * Note that if pressId is null, alphabetized monograph IDs for all
	 * presss are returned.
	 * @param $pressId int
	 * @return Array
	 */
	function &getPublishedMonographIdsByPress($pressId = null, $useCache = true) {
		$monographIds = array();
		$functionName = $useCache?'retrieveCached':'retrieve';
		$result =& $this->$functionName(
			'SELECT a.monograph_id AS pub_id FROM published_monographs pa, monographs a LEFT JOIN sections s ON s.section_id = a.section_id WHERE pa.monograph_id = a.monograph_id' . (isset($pressId)?' AND a.press_id = ?':'') . ' ORDER BY pa.date_published DESC',
			isset($pressId)?$pressId:false
		);

		while (!$result->EOF) {
			$row = $result->getRowAssoc(false);
			$monographIds[] = $row['pub_id'];
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $monographIds;
	}

	/**
	 * creates and returns a published monograph object from a row, including all supp files etc.
	 * @param $row array
	 * @param $callHooks boolean Whether or not to call hooks
	 * @return PublishedMonograph object
	 */
	function &_returnPublishedMonographFromRow($row, $callHooks = true) {
		$publishedMonograph =& $this->_returnSimplePublishedMonographFromRow($row, false);

		$publishedMonograph->setSuppFiles($this->suppFileDao->getSuppFilesByMonograph($row['monograph_id']));

		if ($callHooks) HookRegistry::call('PublishedMonographDAO::_returnPublishedMonographFromRow', array(&$publishedMonograph, &$row));
		return $publishedMonograph;
	}

	/**
	 * creates and returns a published monograph object from a row, omitting supp files etc.
	 * @param $row array
	 * @param $callHooks boolean Whether or not to call hooks
	 * @return PublishedMonograph object
	 */
	function &_returnSimplePublishedMonographFromRow($row, $callHooks = true) {
		$publishedMonograph = new PublishedMonograph();
		$publishedMonograph->setPubId($row['pub_id']);
		$publishedMonograph->setIssueId($row['issue_id']);
		$publishedMonograph->setDatePublished($this->datetimeFromDB($row['date_published']));
		$publishedMonograph->setSeq($row['seq']);
		$publishedMonograph->setViews($row['views']);
		$publishedMonograph->setAccessStatus($row['access_status']);
		$publishedMonograph->setPublicMonographId($row['public_monograph_id']);

		$publishedMonograph->setGalleys($this->galleyDao->getGalleysByMonograph($row['monograph_id']));

		// Monograph attributes
		$this->monographDao->_monographFromRow($publishedMonograph, $row);

		if ($callHooks) HookRegistry::call('PublishedMonographDAO::_returnSimplePublishedMonographFromRow', array(&$publishedMonograph, &$row));

		return $publishedMonograph;
	}

	/**
	 * inserts a new published monograph into published_monographs table
	 * @param PublishedMonograph object
	 * @return pubId int
	 */

	function insertPublishedMonograph(&$publishedMonograph) {
		$this->update(
			sprintf('INSERT INTO published_monographs
				(monograph_id, issue_id, date_published, seq, access_status, public_monograph_id)
				VALUES
				(?, ?, %s, ?, ?, ?)',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getMonographId(),
				$publishedMonograph->getIssueId(),
				$publishedMonograph->getSeq(),
				$publishedMonograph->getAccessStatus(),
				$publishedMonograph->getPublicMonographId()
			)
		);

		$publishedMonograph->setPubId($this->getInsertPublishedMonographId());
		return $publishedMonograph->getPubId();
	}

	/**
	 * Get the ID of the last inserted published monograph.
	 * @return int
	 */
	function getInsertPublishedMonographId() {
		return $this->getInsertId('published_monographs', 'pub_id');
	}

	/**
	 * removes an published Monograph by id
	 * @param pubId int
	 */
	function deletePublishedMonographById($pubId) {
		$this->update(
			'DELETE FROM published_monographs WHERE pub_id = ?', $pubId
		);
	}

	/**
	 * Delete published monograph by monograph ID
	 * NOTE: This does not delete the related Monograph or any dependent entities
	 * @param $monographId int
	 */
	function deletePublishedMonographByMonographId($monographId) {
		return $this->update(
			'DELETE FROM published_monographs WHERE monograph_id = ?', $monographId
		);
	}

	/**
	 * Delete published monographs by section ID
	 * @param $sectionId int
	 */
	function deletePublishedMonographsBySectionId($sectionId) {
		$result =& $this->retrieve(
			'SELECT pa.monograph_id AS monograph_id FROM published_monographs pa, monographs a WHERE pa.monograph_id = a.monograph_id AND a.section_id = ?', $sectionId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$this->update(
				'DELETE FROM published_monographs WHERE monograph_id = ?', $row['monograph_id']
			);
		}

		$result->Close();
		unset($result);
	}

	/**
	 * Delete published monographs by issue ID
	 * @param $issueId int
	 */
	function deletePublishedMonographsByIssueId($issueId) {
		return $this->update(
			'DELETE FROM published_monographs WHERE issue_id = ?', $issueId
		);
	}

	/**
	 * updates a published monograph
	 * @param PublishedMonograph object
	 */
	function updatePublishedMonograph($publishedMonograph) {
		$this->update(
			sprintf('UPDATE published_monographs
				SET
					monograph_id = ?,
					issue_id = ?,
					date_published = %s,
					seq = ?,
					access_status = ?,
					public_monograph_id = ?
				WHERE pub_id = ?',
				$this->datetimeToDB($publishedMonograph->getDatePublished())),
			array(
				$publishedMonograph->getMonographId(),
				$publishedMonograph->getIssueId(),
				$publishedMonograph->getSeq(),
				$publishedMonograph->getAccessStatus(),
				$publishedMonograph->getPublicMonographId(),
				$publishedMonograph->getPubId()
			)
		);
	}

	/**
	 * updates a published monograph field
	 * @param $pubId int
	 * @param $field string
	 * @param $value mixed
	 */
	function updatePublishedMonographField($pubId, $field, $value) {
		$this->update(
			"UPDATE published_monographs SET $field = ? WHERE pub_id = ?", array($value, $pubId)
		);
	}

	/**
	 * Sequentially renumber published monographs in their sequence order.
	 */
	function resequencePublishedMonographs($sectionId, $issueId) {
		$result =& $this->retrieve(
			'SELECT pa.pub_id FROM published_monographs pa, monographs a WHERE a.section_id = ? AND a.monograph_id = pa.monograph_id AND pa.issue_id = ? ORDER BY pa.seq',
			array($sectionId, $issueId)
		);

		for ($i=1; !$result->EOF; $i++) {
			list($pubId) = $result->fields;
			$this->update(
				'UPDATE published_monographs SET seq = ? WHERE pub_id = ?',
				array($i, $pubId)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Retrieve all authors from published monographs
	 * @param $issueId int
	 * @return $authors array Author Objects
	 */
	function getPublishedMonographAuthors($issueId) {
		$authors = array();
		$result =& $this->retrieve(
			'SELECT aa.* FROM monograph_authors aa, published_monographs pa WHERE aa.monograph_id = pa.monograph_id AND pa.issue_id = ? ORDER BY pa.issue_id', $issueId
		);

		while (!$result->EOF) {
			$row = $result->GetRowAssoc(false);
			$author = new Author();
			$author->setId($row['author_id']);
			$author->setMonographId($row['monograph_id']);
			$author->setFirstName($row['first_name']);
			$author->setMiddleName($row['middle_name']);
			$author->setLastName($row['last_name']);
			$author->setAffiliation($row['affiliation']);
			$author->setEmail($row['email']);
			$author->setBiography($row['biography']);
			$author->setPrimaryContact($row['primary_contact']);
			$author->setSequence($row['seq']);
			$authors[] = $author;
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $authors;
	}

	/**
	 * Increment the views count for a galley.
	 * @param $monographId int
	 */
	function incrementViewsByMonographId($monographId) {
		return $this->update(
			'UPDATE published_monographs SET views = views + 1 WHERE monograph_id = ?',
			$monographId
		);
	}

	/**
	 * Checks if public identifier exists
	 * @param $publicIssueId string
	 * @return boolean
	 */
	function publicMonographIdExists($publicMonographId, $monographId, $pressId) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM published_monographs pa, monographs a WHERE pa.monograph_id = a.monograph_id AND a.press_id = ? AND pa.public_monograph_id = ? AND pa.monograph_id <> ?',
			array($pressId, $publicMonographId, $monographId)
		);
		$returner = $result->fields[0] ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}
	
	/**
	 * Return years of oldest/youngest published monograph on site or within a press
	 * @param $pressId int
	 * @return array
	 */
	function getMonographYearRange($pressId = null) {
		$result =& $this->retrieve(
			'SELECT MAX(pa.date_published), MIN(pa.date_published) FROM published_monographs pa, monographs a WHERE pa.monograph_id = a.monograph_id' . (isset($pressId)?' AND a.press_id = ?':''),
			isset($pressId)?$pressId:false
		);
		$returner = array($result->fields[0], $result->fields[1]);

		$result->Close();
		unset($result);

		return $returner;
	}
}

?>
