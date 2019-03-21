<?php

/**
 * @defgroup oai_omp OMP OAI concerns
 */

/**
 * @file classes/oai/omp/PressOAI.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressOAI
 * @ingroup oai_omp
 * @see OAIDAO
 *
 * @brief OMP-specific OAI interface.
 * Designed to support both a site-wide and press-specific OAI interface
 * (based on where the request is directed).
 */

import('lib.pkp.classes.oai.OAI');
import('classes.oai.omp.OAIDAO');

class PressOAI extends OAI {
	/** @var $site Site associated site object */
	var $site;

	/** @var $press Press associated press object */
	var $press;

	/** @var $pressId int null if no press */
	var $pressId;

	/** @var $dao OAIDAO DAO for retrieving OAI records/tokens from database */
	var $dao;


	/**
	 * @see OAI#OAI
	 */
	function __construct($config) {
		parent::__construct($config);

		$request = Application::get()->getRequest();

		$this->site = $request->getSite();
		$this->press = $request->getPress();
		$this->pressId = isset($this->press) ? $this->press->getId() : null;
		$this->dao = DAORegistry::getDAO('OAIDAO');
		$this->dao->setOAI($this);
	}

	/**
	 * Return a list of ignorable GET parameters.
	 * @return array
	 */
	function getNonPathInfoParams() {
		return array('press', 'page');
	}

	/**
	 * Convert monograph ID to OAI identifier.
	 * @param $publicationFormatId int
	 * @return string
	 */
	function publicationFormatIdToIdentifier($publicationFormatId) {
		return $this->_getIdentifierPrefix() . $publicationFormatId;
	}

	/**
	 * Convert OAI identifier to monograph ID.
	 * @param $identifier string
	 * @return int
	 */
	function identifierToPublicationFormatId($identifier) {
		$prefix = $this->_getIdentifierPrefix();
		if (strstr($identifier, $prefix)) {
			return (int) str_replace($prefix, '', $identifier);
		} else {
			return false;
		}
	}

	/**
	 * Get press ID and series ID corresponding to a set specifier.
	 * @param $setSpec string
	 * @param $pressId int
	 * @return array
	 */
	function setSpecToSeriesId($setSpec, $pressId = null) {
		$tmpArray = preg_split('/:/', $setSpec);
		if (count($tmpArray) == 1) {
			list($pressSpec) = $tmpArray;
			$pressSpec = urldecode($pressSpec);
			$seriesSpec = null;
		} else if (count($tmpArray) == 2) {
			list($pressSpec, $seriesSpec) = $tmpArray;
			$pressSpec = urldecode($pressSpec);
			$seriesSpec = urldecode($seriesSpec);
		} else {
			return array(0, 0);
		}
		return $this->dao->getSetPressSeriesId($pressSpec, $seriesSpec, $this->pressId);
	}


	//
	// OAI interface functions
	//

	/**
	 * @see OAI#repositoryInfo
	 */
	function repositoryInfo() {
		$info = new OAIRepository();

		if (isset($this->press)) {
			$info->repositoryName = $this->press->getLocalizedName();
			$info->adminEmail = $this->press->getSetting('contactEmail');

		} else {
			$info->repositoryName = $this->site->getLocalizedTitle();
			$info->adminEmail = $this->site->getLocalizedContactEmail();
		}

		$info->sampleIdentifier = $this->publicationFormatIdToIdentifier(1);
		$info->earliestDatestamp = $this->dao->getEarliestDatestamp(array($this->pressId));

		$info->toolkitTitle = 'Open Monograph Press';
		$versionDao = DAORegistry::getDAO('VersionDAO');
		$currentVersion = $versionDao->getCurrentVersion();
		$info->toolkitVersion = $currentVersion->getVersionString(false);
		$info->toolkitURL = 'http://pkp.sfu.ca/omp/';

		return $info;
	}

	/**
	 * @see OAI#validIdentifier
	 */
	function validIdentifier($identifier) {
		return $this->identifierToPublicationFormatId($identifier) !== false;
	}

	/**
	 * @see OAI#identifierExists
	 */
	function identifierExists($identifier) {
		$recordExists = false;
		$publicationFormatId = $this->identifierToPublicationFormatId($identifier);
		if ($publicationFormatId) {
			$recordExists = $this->dao->recordExists($publicationFormatId, array($this->pressId));
		}
		return $recordExists;
	}

	/**
	 * @see OAI#record
	 */
	function record($identifier) {
		$publicationFormatId = $this->identifierToPublicationFormatId($identifier);
		if ($publicationFormatId) {
			$record = $this->dao->getRecord($publicationFormatId, array($this->pressId));
		}
		if (!isset($record)) {
			$record = false;
		}
		return $record;
	}

	/**
	 * @see OAI#records
	 */
	function records($metadataPrefix, $from, $until, $set, $offset, $limit, &$total) {
		$records = null;
		if (!HookRegistry::call('PressOAI::records', array(&$this, $from, $until, $set, $offset, $limit, $total, &$records))) {
			$seriesId = null;
			if (isset($set)) {
				list($pressId, $seriesId) = $this->setSpecToSeriesId($set);
			} else {
				$pressId = $this->pressId;
			}
			$records = $this->dao->getRecords(array($pressId, $seriesId), $from, $until, $set, $offset, $limit, $total);
		}
		return $records;
	}

	/**
	 * @see OAI#identifiers
	 */
	function identifiers($metadataPrefix, $from, $until, $set, $offset, $limit, &$total) {
		$records = null;
		if (!HookRegistry::call('PressOAI::identifiers', array(&$this, $from, $until, $set, $offset, $limit, $total, &$records))) {
			$seriesId = null;
			if (isset($set)) {
				list($pressId, $seriesId) = $this->setSpecToSeriesId($set);
			} else {
				$pressId = $this->pressId;
			}
			$records = $this->dao->getIdentifiers(array($pressId, $seriesId), $from, $until, $set, $offset, $limit, $total);
		}
		return $records;
	}

	/**
	 * @see OAI#sets
	 */
	function sets($offset, $limit, &$total) {
		$sets = null;
		if (!HookRegistry::call('PressOAI::sets', array(&$this, $offset, $limit, $total, &$sets))) {
			$sets = $this->dao->getSets($this->pressId, $offset, $limit, $total);
		}
		return $sets;
	}

	/**
	 * @see OAI#resumptionToken
	 */
	function resumptionToken($tokenId) {
		$this->dao->clearTokens();
		$token = $this->dao->getToken($tokenId);
		if (!isset($token)) {
			$token = false;
		}
		return $token;
	}

	/**
	 * @see OAI#saveResumptionToken
	 */
	function saveResumptionToken($offset, $params) {
		$token = new OAIResumptionToken(null, $offset, $params, time() + $this->config->tokenLifetime);
		$this->dao->insertToken($token);
		return $token;
	}


	//
	// Private helper methods
	//
	/**
	 * Get the OAI identifier prefix.
	 * @return string
	 */
	function _getIdentifierPrefix() {
		return 'oai:' . $this->config->repositoryId . ':' . 'publicationFormat/';
	}
}


