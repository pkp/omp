<?php

/**
 * @defgroup monograph
 */

/**
 * @file classes/monograph/Monograph.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Monograph
 * @ingroup monograph
 * @see MonographDAO
 *
 * @brief Class for a Monograph.
 */


// Submission status constants
define('STATUS_ARCHIVED', 0);
define('STATUS_QUEUED', 1);
define('STATUS_PUBLISHED', 3);
define('STATUS_DECLINED', 4);

define ('STATUS_QUEUED_UNASSIGNED', 5);
define ('STATUS_QUEUED_REVIEW', 6);
define ('STATUS_QUEUED_EDITING', 7);
define ('STATUS_INCOMPLETE', 8);

define('WORK_TYPE_EDITED_VOLUME', 1);
define('WORK_TYPE_AUTHORED_WORK', 2);

import('lib.pkp.classes.submission.Submission');
import('classes.monograph.Author');

class Monograph extends Submission {
	/**
	 * get monograph id
	 * @return int
	 * Constructor.
	 */
	function Monograph() {
		// Switch on meta-data adapter support.
		$this->setHasLoadableAdapters(true);

		parent::Submission();
	}

	/**
	 * @see Submission::getAssocType()
	 */
	function getAssocType() {
		return ASSOC_TYPE_MONOGRAPH;
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
	 * Get the series id.
	 * @return int
	 */
	function getSeriesId() {
		return $this->getData('seriesId');
	}

	/**
	 * Set the series id.
	 * @param $id int
	 */
	function setSeriesId($id) {
		$this->setData('seriesId', $id);
	}

	/**
	 * Get the series's title.
	 * @return string
	 */
	function getSeriesTitle() {
		return $this->getData('seriesTitle');
	}

	/**
	 * Set the series title.
	 * @param $title string
	 */
	function setSeriesTitle($title) {
		$this->setData('seriesTitle', $title);
	}

	/**
	 * Get the series's abbreviated identifier.
	 * @return string
	 */
	function getSeriesAbbrev() {
		return $this->getData('seriesAbbrev');
	}

	/**
	 * Set the series's abbreviated identifier.
	 * @param $abbrev string
	 */
	function setSeriesAbbrev($abbrev) {
		$this->setData('seriesAbbrev', $abbrev);
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
	 * Return boolean indicating if author should be hidden in contributor statement.
	 * @return boolean
	 */
	function getHideAuthor() {
		return $this->getData('hideAuthor');
	}

	/**
	 * Set if author should be hidden in the contributor statement.
	 * @param $hideAuthor boolean
	 */
	function setHideAuthor($hideAuthor) {
		return $this->setData('hideAuthor', $hideAuthor);
	}

	/**
	 * Get the monograph's current publication stage ID
	 * @return int
	 */
	function getStageId() {
		return $this->getData('stageId');
	}

	/**
	 * Set the monograph's current publication stage ID
	 * @param $stageId int
	 */
	function setStageId($stageId) {
		return $this->setData('stageId', $stageId);
	}


	//
	// Peer Review
	//

	/**
	 * Set the current review round
	 * @param $rount int
	 */
	function setCurrentRound($round) {
		$this->setData('currentRound', $round);
	}

	/**
	 * Get the current review round
	 * @return int
	 */
	function getCurrentRound() {
		return $this->getData('currentRound');
	}

	/**
	 * Get the work type (constant in WORK_TYPE_...)
	 * @return int
	 */
	function getWorkType() {
		return $this->getData('workType');
	}

	/**
	 * Set the work type (constant in WORK_TYPE_...)
	 * @param $workType int
	 */
	function setWorkType($workType) {
		$this->setData('workType', $workType);
	}

	/**
	 * Get the localized version of the subtitle
	 * @return string
	 */
	function getLocalizedSubtitle() {
		return $this->getLocalizedData('subtitle');
	}

	/**
	 * Get the subtitle for a given locale
	 * @param string $locale
	 * @return string
	 */
	function getSubtitle($locale) {
		return $this->getData('subtitle', $locale);
	}

	/**
	 * Set the subtitle for a locale
	 * @param string $subtitle
	 * @param string $locale
	 */
	function setSubtitle($subtitle, $locale) {
		return $this->setData('subtitle', $subtitle, $locale);
	}

	/**
	 * Get localized supporting agencies array.
	 * @return array
	 */
	function getLocalizedSupportingAgencies() {
		return $this->getLocalizedData('supportingAgencies');
	}

	/**
	 * Get supporting agencies.
	 * @param $locale
	 * @return array
	 */
	function getSupportingAgencies($locale) {
		return $this->getData('supportingAgencies', $locale);
	}

	/**
	 * Set supporting agencies.
	 * @param $supportingAgencies array
	 * @param $locale
	 */
	function setSupportingAgencies($title, $locale) {
		return $this->setData('supportingAgencies', $title, $locale);
	}

	/**
	 * Return absolute path to the files of this
	 * monograph on the host filesystem.
	 * @return string
	 */
	function getFilePath() {
		return Config::getVar('files', 'files_dir') . '/presses/' . $this->getPressId() .
				'/monographs/' . $this->getId() . '/';
	}
}

?>
