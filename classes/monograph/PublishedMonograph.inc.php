<?php

/**
 * @file classes/monograph/PublishedMonograph.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublishedMonograph
 * @ingroup monograph
 * @see PublishedMonographDAO
 *
 * @brief Published monograph class.
 */

// $Id$


import('monograph.Monograph');

class PublishedMonograph extends Monograph {

	/**
	 * Constructor.
	 */
	function PublishedMonograph() {
		parent::Monograph();
	}

	/**
	 * Get ID of published monograph.
	 * @return int
	 */
	function getPubId() {
		return $this->getData('pubId');
	}

	/**
	 * Set ID of published monograph.
	 * @param $pubId int
	 */
	function setPubId($pubId) {
		return $this->setData('pubId', $pubId);
	}

	/**
	 * Get ID of associated monograph.
	 * @return int
	 */
	function getMonographId() {
		return $this->getData('monographId');
	}

	/**
	 * Set ID of associated monograph.
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}

	/**
	 * Get ID of the issue this monograph is in.
	 * @return int
	 */
	function getIssueId() {
		return $this->getData('issueId');
	}

	/**
	 * Set ID of the issue this monograph is in.
	 * @param $issueId int
	 */
	function setIssueId($issueId) {
		return $this->setData('issueId', $issueId);
	}

	/**
	 * Get section ID of the issue this monograph is in.
	 * @return int
	 */
	function getSectionId() {
		return $this->getData('sectionId');
	}

	/**
	 * Set section ID of the issue this monograph is in.
	 * @param $sectionId int
	 */
	function setSectionId($sectionId) {
		return $this->setData('sectionId', $sectionId);
	}

	/**
	 * Get date published.
	 * @return date
	 */

	function getDatePublished() {
		return $this->getData('datePublished');	
	}


	/**
	 * Set date published.
	 * @param $datePublished date
	 */

	function setDatePublished($datePublished) {
		return $this->SetData('datePublished', $datePublished);
	}

	/**
	 * Get sequence of monograph in table of contents.
	 * @return float
	 */
	function getSeq() {
		return $this->getData('seq');
	}

	/**
	 * Set sequence of monograph in table of contents.
	 * @param $sequence float
	 */
	function setSeq($seq) {
		return $this->setData('seq', $seq);
	}

	/**
	 * Get views of the published monograph.
	 * @return int
	 */
	function getViews() {
		return $this->getData('views');
	}

	/**
	 * Set views of the published monograph.
	 * @param $views int
	 */
	function setViews($views) {
		return $this->setData('views', $views);
	}

	/**
	 * get access status
	 * @return int
	 */
	function getAccessStatus() {
		return $this->getData('accessStatus');
	}

	/**
	 * set access status
	 * @param $accessStatus int
	 */
	function setAccessStatus($accessStatus) {
		return $this->setData('accessStatus',$accessStatus);
	}

	/**
	 * Get the galleys for an monograph.
	 * @return array MonographGalley
	 */
	function &getGalleys() {
		$galleys =& $this->getData('galleys');
		return $galleys;
	}

	/**
	 * Get the localized galleys for an monograph.
	 * @return array MonographGalley
	 */
	function &getLocalizedGalleys() {
		$primaryLocale = Locale::getPrimaryLocale();

		$allGalleys =& $this->getData('galleys');
		$galleys = array();
		foreach (array(Locale::getLocale(), Locale::getPrimaryLocale()) as $tryLocale) {
			foreach (array_keys($allGalleys) as $key) {
				if ($allGalleys[$key]->getLocale() == $tryLocale) {
					$galleys[] =& $allGalleys[$key];
				}
			}
			if (!empty($galleys)) {
				HookRegistry::call('MonographGalleyDAO::getLocalizedGalleysByMonograph', array(&$galleys, &$monographId));
				return $galleys;
			}
		}

		return $galleys;
	}

	/**
	 * Set the galleys for an monograph.
	 * @param $galleys array MonographGalley
	 */
	function setGalleys(&$galleys) {
		return $this->setData('galleys', $galleys);
	}

	/**
	 * Get supplementary files for this monograph.
	 * @return array SuppFiles
	 */
	function &getSuppFiles() {
		$returner =& $this->getData('suppFiles');
		return $returner;
	}

	/**
	 * Set supplementary file for this monograph.
	 * @param $suppFiles array SuppFiles
	 */
	function setSuppFiles($suppFiles) {
		return $this->setData('suppFiles', $suppFiles);
	}

	/**
	 * Get public monograph id
	 * @return string
	 */
	function getPublicMonographId() {
		// Ensure that blanks are treated as nulls.
		$returner = $this->getData('publicMonographId');
		if ($returner === '') return null;
		return $returner;
	}

	/**
	 * Set public monograph id
	 * @param $publicMonographId string
	 */
	function setPublicMonographId($publicMonographId) {
		return $this->setData('publicMonographId', $publicMonographId);
	}

	/**
	 * Return the "best" monograph ID -- If a public monograph ID is set,
	 * use it; otherwise use the internal monograph Id. (Checks the press
	 * settings to ensure that the public ID feature is enabled.)
	 * @param $press Object the press this monograph is in
	 * @return string
	 */
	function getBestMonographId($press = null) {
		// Retrieve the press, if necessary.
		if (!isset($press)) {
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press = $pressDao->getPress($this->getPressId());
		}

		if ($press->getSetting('enablePublicMonographId')) {
			$publicMonographId = $this->getPublicMonographId();
			if (!empty($publicMonographId)) return $publicMonographId;
		}
		return $this->getMonographId();
	}

	/**
	 * Get a DOI for this monograph.
	 */
	function getDOI() {
		$pressId = $this->getPressId();

		// Get the Press object (optimized)
		$press =& Request::getPress();
		if (!$press || $press->getPressId() != $pressId) {
			unset($press);
			$pressDao =& DAORegistry::getDAO('PressDAO');
			$press =& $pressDao->getPress($pressId);
		}

		if (($doiPrefix = $press->getSetting('doiPrefix')) == '') return null;
		$doiSuffixSetting = $press->getSetting('doiSuffix');

		// Get the issue
		$issueDao =& DAORegistry::getDAO('IssueDAO');
		$issue =& $issueDao->getIssueByMonographId($this->getMonographId());

		if (!$issue || !$press || $press->getPressId() != $issue->getPressId() ) return null;
				
		switch ( $doiSuffixSetting ) {
			case 'customIdentifier': 
				return $doiPrefix . '/' . $this->getBestMonographId();
				break;	
			case 'pattern':		
				$suffixPattern = $press->getSetting('doiSuffixPattern');
				// %j - press initials
				$suffixPattern = String::regexp_replace('/%j/', String::strtolower($press->getLocalizedSetting('initials')), $suffixPattern);
				// %v - volume number  
				$suffixPattern = String::regexp_replace('/%v/', $issue->getVolume(), $suffixPattern);
				// %i - issue number
				$suffixPattern = String::regexp_replace('/%i/', $issue->getNumber(), $suffixPattern);
				// %a - monograph id
				$suffixPattern = String::regexp_replace('/%a/', $this->getMonographId(), $suffixPattern);
				// %p - page number
				$suffixPattern = String::regexp_replace('/%p/', $this->getPages(), $suffixPattern);    
				return $doiPrefix . '/' . $suffixPattern; 														 
				break;
			default:
				return $doiPrefix . '/' . String::strtolower($press->getLocalizedSetting('initials')) . '.v' . $issue->getVolume() . 'i' . $issue->getNumber() . '.' . $this->getMonographId();
		}
	}
}

?>
