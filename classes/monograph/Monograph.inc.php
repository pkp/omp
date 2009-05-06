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

// Submission status constants
define('STATUS_ARCHIVED', 0);
define('STATUS_QUEUED', 1);
define('STATUS_PUBLISHED', 3);
define('STATUS_DECLINED', 4);

define ('STATUS_QUEUED_UNASSIGNED', 5);
define ('STATUS_QUEUED_REVIEW', 6);
define ('STATUS_QUEUED_EDITING', 7);
define ('STATUS_INCOMPLETE', 8);

define('ISSUE_DEFAULT', 0);
define('OPEN_ACCESS', 1);
define('SUBSCRIPTION', 2);

define('EDITED_VOLUME', 1);

import('submission.Submission');
import('monograph.Author');

class Monograph extends Submission {
	/**
	 * get monograph id
	 * @return int
	 */

	function getMonographId() {
		return $this->getData('monographId');
	}
	function addMonographComponent($component) {
		if ($component->getSequence() == null) {
			$component->setSequence(count($this->getData('components')) + 1);
		}
		$components = $this->getData('components');
		array_push($components, $component);
		$this->setData('components', $components);
	}
	function setMonographComponents($components) {
		$this->setData('components', $components);
	}
	function setAcquisitionsArrangementId($id) {
		 $this->setData('acquisitions_arrangement_id', $id);
	}
	function getAcquisitionsArrangementId() {
		 return $this->getData('acquisitions_arrangement_id');
	}
	function getAcquisitionsArrangementAbbrev() {
		 return $this->getData('arrangementAbbrev');
	}
	function setAcquisitionsArrangementAbbrev($value) {
		 $this->setData('arrangementAbbrev', $value);
	}
	function getAcquisitionsArrangementTitle() {
		 return $this->getData('arrangementTitle');
	}
	function setAcquisitionsArrangementTitle($value) {
		 $this->setData('arrangementTitle', $value);
	}
	function setCompletedProspectusFileId(&$id) {
		 $this->setData('prospectus_file_id', $id);
	}
	function &getCompletedProspectusFileId() {
		 return $this->getData('prospectus_file_id');
	}

	function setCurrentReviewRound($round) {
		 $this->setData('currentReviewRound', $round);
	}
	function getCurrentReviewRound() {
		 return $this->getData('currentReviewRound');
	}
	function setCurrentReviewType($reviewType) {
		 $this->setData('currentReviewType', $reviewType);
	}
	function getCurrentReviewType() {
		 return $this->getData('currentReviewType');
	}

	function setReviewRoundsInfo($reviewRoundsInfo) {
		 $this->setData('reviewRoundsInfo', $reviewRoundsInfo);
	}
	function getReviewRoundsInfo() {
		 return $this->getData('reviewRoundsInfo');
	}

	/**
	 * Get a signoff for this monograph
	 * @param $signoffType string
	 * @return Signoff
	 */
	function getSignoff($signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		return $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());
	}

	/**
	 * Get the file for this article at a given signoff stage
	 * @param $signoffType string
	 * @param $idOnly boolean Return only file ID
	 * @return ArticleFile
	 */
	function getFileBySignoffType($signoffType, $idOnly = false) {
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$signoff = $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());
		if (!$signoff) return false;

		if ($idOnly) return $signoff->getFileId();

		$monographFile =& $monographFileDao->getMonographFile($signoff->getFileId(), $signoff->getFileRevision());
		return $monographFile;
	}

	/**
	 * Get the user associated with a given signoff and this article
	 * @param $signoffType string
	 * @return User
	 */
	function getUserBySignoffType($signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$signoff =& $signoffDao->build($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());

		if (!$signoff) return false;
		$user =& $userDao->getUser($signoff->getUserId());

		return $user;
	}
	/**
	 * Get the user id associated with a given signoff and this article
	 * @param $signoffType string
	 * @return int
	 */
	function getUserIdBySignoffType($signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());
		if (!$signoff) return false;
		
		return $signoff->getUserId();
	}


	/**
	 * set monograph id
	 * @param $monographId int
	 */
	function setMonographId($monographId) {
		return $this->setData('monographId', $monographId);
	}
	function getMonographComponents() {

		return $this->getData('components');
	}
	function getWorkType() {
		return $this->getData('workType');
	}
	function setWorkType($type) {
		$this->setData('workType', $type);
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

	/**
	 * Get comments to editor.
	 * @return string
	 */
	function getCommentsToEditor() {
		return $this->getData('commentsToEditor');
	}

	/**
	 * Set comments to editor.
	 * @param $commentsToEditor string
	 */
	function setCommentsToEditor($commentsToEditor) {
		return $this->setData('commentsToEditor', $commentsToEditor);
	}
	/**
	 * Get editor file id.
	 * @return int
	 */
	function getEditorFileId() {
		return $this->getData('editorFileId');
	}

	/**
	 * Set editor file id.
	 * @param $editorFileId int
	 */
	function setEditorFileId($editorFileId) {
		return $this->setData('editorFileId', $editorFileId);
	}

	/**
	 * Get layout file id.
	 * @return int
	 */
	function getLayoutFileId() {
		return $this->getData('layoutFileId');
	}

	/**
	 * Set layout file id.
	 * @param $layoutFileId int
	 */
	function setLayoutFileId($layoutFileId) {
		return $this->setData('layoutFileId', $layoutFileId);
	}

	/**
	 * get expedited
	 * @return boolean
	 */
	function getFastTracked() {
		return $this->getData('fastTracked');
	}
	 
	/**
	 * set fastTracked
	 * @param $fastTracked boolean
	 */
	function setFastTracked($fastTracked) {
		return $this->setData('fastTracked',$fastTracked);
	}
	/**
	 * Return boolean indicating if author should be hidden in issue ToC.
	 * @return boolean
	 */
	function getHideAuthor() {
		return $this->getData('hideAuthor');
	}

	/**
	 * Set if author should be hidden in issue ToC.
	 * @param $hideAuthor boolean
	 */
	function setHideAuthor($hideAuthor) {
		return $this->setData('hideAuthor', $hideAuthor);
	}
	function setEditedVolume($isVolume) {
		$this->setData('edited_volume', $isVolume);
	}
	function getEditedVolume() {
		$this->getData('edited_volume');
	}
	function resetAuthors() {
		unset($this->authors);
		$this->authors = array();
	}
}	

?>
