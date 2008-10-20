<?php

/**
 * @defgroup monograph
 */
 
/**
 * @file classes/monograph/Monograph.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Monograph
 * @ingroup monograph
 * @see MonographDAO
 *
 * @brief Class for a Monograph.
 */

// $Id$


define('ISSUE_DEFAULT', 0);
define('OPEN_ACCESS', 1);
define('SUBSCRIPTION', 2);

class Monograph extends DataObject {
	/**
	 * get monograph id
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * set monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * get press id
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * set press id
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get the localized title
	 * @return string
	 */
	function getMonographTitle() {
		return $this->getLocalizedData('title');
	}

	/**
	 * get title
	 * @param $locale string
	 * @return string
	 */
	function getTitle($locale) {
		return $this->getData('title', $locale);
	}

	/**
	 * set title
	 * @param $title string
	 * @param $locale string
	 */
	function setTitle($title, $locale) {
		return $this->setData('title', $title, $locale);
	}

	/**
	 * get status
	 * @return int
	 */
	function getStatus() {
		return $this->getData('status');
	}

	/**
	 * set status
	 * @param $current int
	 */
	function setStatus($current) {
		return $this->setData('current', $current);
	}

 	/**
	 * get date published
	 * @return date
	 */
	function getDatePublished() {
		return $this->getData('datePublished');
	}

	/**
	 * set date published
	 * @param $datePublished date
	 */
	function setDatePublished($datePublished) {
		return $this->setData('datePublished', $datePublished);
	}

	/**
	 * Get the localized description
	 * @return string
	 */
	function getMonographDescription() {
		return $this->getLocalizedData('description');
	}

	/**
	 * get description
	 * @param $locale string
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getData('description', $locale);
	}

	/**
	 * set description
	 * @param $description string
	 * @param $locale string
	 */
	function setDescription($description, $locale) {
		return $this->setData('description', $description, $locale);
	}

	/**
	 * get public issue id
	 * @return string
	 */
	function getPublicMonographId() {
		// Ensure that blanks are treated as nulls
		$returner = $this->getData('publicMonographId');
		if ($returner === '') return null;
		return $returner;
	}

	/**
	 * set public issue id
	 * @param $publicIssueId string
	 */
	function setPublicMonographId($publicMonographId) {
		return $this->setData('publicMonographId', $publicMonographId);
	}

	/**
	 * Return the "best" issue ID -- If a public issue ID is set,
	 * use it; otherwise use the internal issue Id. (Checks the monograph
	 * settings to ensure that the public ID feature is enabled.)
	 * @param $monograph object The press that is preparing this monograph
	 * @return string
	 */
	function getBestMonographId($press = null) {
		// Retrieve the press object, if necessary.
		if (!isset($press)) {
			$pressDao = &DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getPress($this->getPressId());
		}

		if ($press->getSetting('enablePublicIssueId')) {
			$publicIssueId = $this->getPublicIssueId();
			if (!empty($publicIssueId)) return $publicIssueId;
		}
		return $this->getMonographId();
	}
}

?>
