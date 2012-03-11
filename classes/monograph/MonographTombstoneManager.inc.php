<?php

/**
 * @file classes/monograph/MonographTombstoneManager.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class MonographTombstoneManager
 * @ingroup monograph
 *
 * @brief Class defining basic operations for monograph tombstones.
 */


class MonographTombstoneManager {
	/**
	 * Constructor
	 */
	function MonographTombstoneManager() {
	}

	function insertMonographTombstone(&$monograph, &$press) {
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$series =& $seriesDao->getById($monograph->getSeriesId());

		$monographTombstoneDao =& DAORegistry::getDAO('MonographTombstoneDAO');
		// delete monograph tombstone -- to ensure that there aren't more than one tombstone for this monograph
		$monographTombstoneDao->deleteBySubmissionId($monograph->getId());
		// insert monograph tombstone
		if (is_a($series, 'Series')) {
			$setSpec = urlencode($press->getPath()) . ':' . urlencode($series->getPath());
			$setName = $series->getLocalizedTitle();
		} else {
			$setSpec = urlencode($press->getPath());
			$setName = $press->getLocalizedName();
		}
		$oaiIdentifier = 'oai:' . Config::getVar('oai', 'repository_id') . ':' . 'monograph/' . $monograph->getId();

		$monographTombstone = $monographTombstoneDao->newDataObject();
		$monographTombstone->setPressId($monograph->getPressId());
		$monographTombstone->setSubmissionId($monograph->getId());
		$monographTombstone->stampDateDeleted();
		$monographTombstone->setSeriesId($monograph->getSeriesId());
		$monographTombstone->setSetSpec($setSpec);
		$monographTombstone->setSetName($setName);
		$monographTombstone->setOAIIdentifier($oaiIdentifier);
		$tombstoneId = $monographTombstoneDao->insertObject($monographTombstone);

		if (HookRegistry::call('MonographTombstoneManager::insertArticleTombstone', array(&$monographTombstone, &$monograph, &$press))) return;
	}
}

?>
