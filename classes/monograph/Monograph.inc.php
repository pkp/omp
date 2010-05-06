<?php

/**
 * @defgroup monograph
 */

/**
 * @file classes/monograph/Monograph.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
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
		parent::Submission();
 	}


	/**
	 * Set the submitter user group Id
	 * @param $userGroupId int
	 */
	function setUserGroupId($userGroupId) {
		$this->setData('userGroupId', $userGroupId);
	}

	/**
	 * Get the submitter user group Id
	 * @return int
	 */
	function &getUserGroupId() {
		return $this->getData('userGroupId');
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

	//
	// Peer Review
	//

	function setCurrentRound($round) {
		 $this->setData('currentRound', $round);
	}
	function getCurrentRound() {
		 return $this->getData('currentRound');
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
	 * Get an array of user IDs associated with this monograph
	 * @param $authors boolean
	 * @param $reviewers boolean
	 * @param $editors boolean
	 * @param $proofreader boolean
	 * @param $copyeditor boolean
	 * @param $layoutEditor boolean
	 * @return array User IDs
	 */
	function getAssociatedUserIds($authors = true, $reviewers = true, $editors = true, $proofreader = true, $copyeditor = true, $layoutEditor = true) {
		$monographId = $this->getId();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');

		$userIds = array();

		if($authors) {
			$userId = $this->getUserId();
			if ($userId) $userIds[] = array('id' => $userId, 'role' => 'author');
		}

		if($editors) {
			$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
			$editAssignments =& $editAssignmentDao->getByMonographId($monographId);
			while ($editAssignment =& $editAssignments->next()) {
				$userId = $editAssignment->getEditorId();
				if ($userId) $userIds[] = array('id' => $userId, 'role' => 'editor');
				unset($editAssignment);
			}
		}

/*		if($copyeditor) {
			$copyedSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monographId);
			$userId = $copyedSignoff->getUserId();
			if ($userId) $userIds[] = array('id' => $userId, 'role' => 'copyeditor');
		}

		if($layoutEditor) {
			$layoutSignoff = $signoffDao->getBySymbolic('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monographId);
			$userId = $layoutSignoff->getUserId();
			if ($userId) $userIds[] = array('id' => $userId, 'role' => 'layoutEditor');
		}

		if($proofreader) {
			$proofSignoff = $signoffDao->getBySymbolic('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_MONOGRAPH, $monographId);
			$userId = $proofSignoff->getUserId();
			if ($userId) $userIds[] = array('id' => $userId, 'role' => 'proofreader');
		}
*/
		if($reviewers) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($monographId);
			foreach ($reviewAssignments as $reviewAssignment) {
				$userId = $reviewAssignment->getReviewerId();
				if ($userId) $userIds[] = array('id' => $userId, 'role' => 'reviewer');
				unset($reviewAssignment);
			}
		}

		return $userIds;
	}

	/**
	 * Get the file for this monograph at a given signoff stage
	 * @param $signoffType string
	 * @param $idOnly boolean Return only file ID
	 * @return MonographFile
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
	 * Get the user associated with a given signoff and this monograph
	 * @param $signoffType string
	 * @return User
	 */
	function getUserBySignoffType($signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$signoff =& $signoffDao->build($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getId());

		if (!$signoff) return false;
		$user =& $userDao->getUser($signoff->getUserId());

		return $user;
	}
	/**
	 * Get the user id associated with a given signoff and this monograph
	 * @param $signoffType string
	 * @return int
	 */
	function getUserIdBySignoffType($signoffType) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());
		if (!$signoff) return false;

		return $signoff->getUserId();
	}

	function getWorkType() {
		return $this->getData('workType');
	}

	function setWorkType($type) {
		$this->setData('workType', $type);
	}

	function setEditedVolume($isVolume) {
		$this->setData('edited_volume', $isVolume);
	}

	function getEditedVolume() {
		$this->getData('edited_volume');
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
	 * Return string of author names, separated by the specified token
	 * FIXME: Should be moved back to Submission class
	 * @param $lastOnly boolean return list of lastnames only (default false)
	 * @param $separator string separator for names (default comma+space)
	 * @return string
	 */
	function getAuthorString($lastOnly = false, $separator = ', ') {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors = $authorDao->getAuthorsByMonographId($this->getId());

		$str = '';
		while($author =& $authors->next()) {
			if (!empty($str)) {
				$str .= $separator;
			}
			$str .= $lastOnly ? $author->getLastName() : $author->getFullName();
			unset($author);
		}
		return $str;
	}

	/**
	 * Return a list of author email addresses.
	 * FIXME: Should be moved back to Submission class
	 * @return array
	 */
	function getAuthorEmails() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		$authors = $authorDao->getAuthorsByMonographId($this->getId());

		import('lib.pkp.classes.mail.Mail');
		$returner = array();
		while($author =& $authors->next()) {
			$returner[] = Mail::encodeDisplayName($author->getFullName()) . ' <' . $author->getEmail() . '>';
			unset($author);
		}
		return $returner;
	}

	/**
	 * Get all authors of this submission.
	 * @return array Authors
	 */
	function &getAuthors() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		return $authorDao->getAuthorsByMonographId($this->getId());
	}
}

?>
