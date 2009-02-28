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
	function setCompletedProspectusFileId($id) {
		 $this->setData('prospectus_file_id', $id);
	}
	function getCompletedProspectusFileId() {
		 return $this->getData('prospectus_file_id');
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
	 * Get the localized title
	 * @return string
	 */
	function getLocalizedTitle() {
		return $this->getLocalizedData('title');
	}
	function getMonographSponsor() {
		return $this->getLocalizedData('sponsor');
	}
	function getMonographAbstract() {
		return $this->getLocalizedData('abstract');
	}
	/**
	 * Return the localized discipline
	 * @return string
	 */
	function getMonographDiscipline() {
		return $this->getLocalizedData('discipline');
	}

	/**
	 * Return the localized subject classification
	 * @return string
	 */
	function getMonographSubjectClass() {
		return $this->getLocalizedData('subjectClass');
	}

	/**
	 * Return the localized subject
	 * @return string
	 */
	function getMonographSubject() {
		return $this->getLocalizedData('subject');
	}

	/**
	 * Return the localized geographical coverage
	 * @return string
	 */
	function getMonographCoverageGeo() {
		return $this->getLocalizedData('coverageGeo');
	}

	/**
	 * Return the localized chronological coverage
	 * @return string
	 */
	function getMonographCoverageChron() {
		return $this->getLocalizedData('coverageChron');
	}

	/**
	 * Return the localized sample coverage
	 * @return string
	 */
	function getMonographCoverageSample() {
		return $this->getLocalizedData('coverageSample');
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
	 * Get current review round.
	 * @return int
	 */
	function getCurrentRound() {
		return $this->getData('currentRound');
	}

	/**
	 * Set current review round.
	 * @param $currentRound int
	 */
	function setCurrentRound($currentRound) {
		return $this->setData('currentRound', $currentRound);
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
	 * Get copyedit file id.
	 * @return int
	 */
	function getCopyeditFileId() {
		return $this->getData('copyeditFileId');
	}

	/**
	 * Set copyedit file id.
	 * @param $copyeditFileId int
	 */
	function setCopyeditFileId($copyeditFileId) {
		return $this->setData('copyeditFileId', $copyeditFileId);
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
