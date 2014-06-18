<?php

/**
 * @file classes/publicationFormat/PublicationFormatTombstoneManager.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2000-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatTombstoneManager
 * @ingroup publicationFormat
 *
 * @brief Class defining basic operations for publication format tombstones.
 */


class PublicationFormatTombstoneManager {
	/**
	 * Constructor
	 */
	function PublicationFormatTombstoneManager() {
	}

	/**
	 * Insert a tombstone for the passed publication format.
	 * @param $publicationFormat PublicationFormat
	 * @param $press Press
	 */
	function insertTombstoneByPublicationFormat(&$publicationFormat, &$press) {
		$monographDao = DAORegistry::getDAO('MonographDAO');
		$monograph = $monographDao->getById($publicationFormat->getMonographId());
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$series = $seriesDao->getById($monograph->getSeriesId());

		$dataObjectTombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
		// delete publication format tombstone to ensure that there aren't
		// more than one tombstone for this publication format
		$dataObjectTombstoneDao->deleteByDataObjectId($publicationFormat->getId());
		// insert publication format tombstone
		if (is_a($series, 'Series')) {
			$setSpec = urlencode($press->getPath()) . ':' . urlencode($series->getPath());
			$setName = $series->getLocalizedTitle();
		} else {
			$setSpec = urlencode($press->getPath());
			$setName = $press->getLocalizedName();
		}
		$oaiIdentifier = 'oai:' . Config::getVar('oai', 'repository_id') . ':' . 'publicationFormat/' . $publicationFormat->getId();
		$OAISetObjectsIds = array(
			ASSOC_TYPE_PRESS => $monograph->getPressId(),
			ASSOC_TYPE_SERIES => $monograph->getSeriesId()
		);

		$publicationFormatTombstone = $dataObjectTombstoneDao->newDataObject(); /* @var $publicationFormatTombstone DataObjectTombstone */
		$publicationFormatTombstone->setDataObjectId($publicationFormat->getId());
		$publicationFormatTombstone->stampDateDeleted();
		$publicationFormatTombstone->setSetSpec($setSpec);
		$publicationFormatTombstone->setSetName($setName);
		$publicationFormatTombstone->setOAIIdentifier($oaiIdentifier);
		$publicationFormatTombstone->setOAISetObjectsIds($OAISetObjectsIds);
		$dataObjectTombstoneDao->insertObject($publicationFormatTombstone);

		if (HookRegistry::call('PublicationFormatTombstoneManager::insertPublicationFormatTombstone', array(&$publicationFormatTombstone, &$publicationFormat, &$press))) return;
	}

	/**
	 * Insert tombstone for every publication format inside
	 * the passed array.
	 * @param $publicationFormats array
	 */
	function insertTombstonesByPublicationFormats($publicationFormats, $press) {
		foreach($publicationFormats as $publicationFormat) {
			$this->insertTombstoneByPublicationFormat($publicationFormat, $press);
		}
	}

	/**
	 * Insert tombstone for every publication format of the
	 * published monographs inside the passed press.
	 * @param $press
	 */
	function insertTombstonesByPress($press) {
		$publishedMonographFactory = $this->_getPublishedMonographFactoryByPressId($press->getId());
		while ($publishedMonograph = $publishedMonographFactory->next()) { /* @var $publishedMonograph PublishedMonograph */
			$publicationFormats = $publishedMonograph->getPublicationFormats();
			$this->insertTombstonesByPublicationFormats($publicationFormats, $press);
		}
	}

	/**
	 * Delete tombstone for every passed publication format.
	 * @param $publicationFormats array
	 */
	function deleteTombstonesByPublicationFormats($publicationFormats) {
		foreach ($publicationFormats as $publicationFormat) {
			$tombstoneDao = DAORegistry::getDAO('DataObjectTombstoneDAO');
			$tombstoneDao->deleteByDataObjectId($publicationFormat->getId());
		}
	}

	/**
	 * Delete tombstone for every publication format inside the passed press.
	 * @param $pressId int
	 */
	function deleteTombstonesByPressId($pressId) {
		$publishedMonographFactory = $this->_getPublishedMonographFactoryByPressId($pressId);
		while ($publishedMonograph = $publishedMonographFactory->next()) {
			$publicationFormats = $publishedMonograph->getPublicationFormats();
			$this->deleteTombstonesByPublicationFormats($publicationFormats);
		}
	}


	//
	// Private helper methods.
	//
	/**
	 * Get the published monograph factory for the passed press id.
	 * @param $pressId int
	 * @return DAOResultFactory
	 */
	function _getPublishedMonographFactoryByPressId($pressId) {
		$publishedMonographDao = DAORegistry::getDAO('PublishedMonographDAO');
		return $publishedMonographDao->getByPressId($pressId);
	}
}

?>
