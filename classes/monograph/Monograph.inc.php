<?php

/**
 * @defgroup monograph
 */

/**
 * @file classes/monograph/Monograph.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
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
		return $this->getLocalizedData('abstract');
	}

	/**
	 * get description
	 * @param $locale string
	 * @return string
	 */
	function getDescription($locale) {
		return $this->getData('abstract', $locale);
	}

	/**
	 * set description
	 * @param $description string
	 * @param $locale string
	 */
	function setDescription($description, $locale) {
		return $this->setData('abstract', $description, $locale);
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

	/**
	 * Get the monograph's current publication stage ID
	 * @return int
	 */
	function getCurrentStageId() {
		return $this->getData('stageId');
	}

	/**
	 * Set the monograph's current publication stage ID
	 * @param $stageId int
	 */
	function setCurrentStageId($stageId) {
		return $this->setData('stageId', $stageId);
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
		return $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getId());
	}

	/**
	 * Get an array of user IDs associated with this monograph
	 * @param $includeReviewers boolean Include reviewers in the array
	 * @param $userGroupIds array Only look up the user group IDs in the array
	 * @return array User IDs
	 */
	function getAssociatedUserIds($includeReviewers = false, $userGroupIds = null) {
		$monographId = $this->getId();
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		$userIds = array();

		// If $userGroupIds is set, iterate through them, adding getUsers to array (with keys as userId)
		if (is_array($userGroupIds)) {
			foreach($userGroupIds as $userGroupId) {
				$users =& $signoffDao->getUsersBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, null, $userGroupId);
				while ($user =& $users->next()) {
					$userId = $user->getUserId();
					if ($userId) $userIds[$userId] = array('id' => $userId);
					unset($user);
				}
				unset($users);
			}
		} else {
				$users =& $signoffDao->getUsersBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId);

				while ($user =& $users->next()) {
					$userId = $user->getUserId();
					if ($userId) $userIds[$userId] = array('id' => $userId);
					unset($user);
				}

		}

		// Get reviewers if necessary
		if($includeReviewers) {
			$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
			$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($monographId);
			foreach ($reviewAssignments as $reviewAssignment) {
				$userId = $reviewAssignment->getReviewerId();
				if ($userId) $userIds[$userId] = array('id' => $userId, 'role' => 'reviewer');
				unset($reviewAssignment);
			}
		}

		return $userIds;
	}

	/**
	 * Get the file for this monograph at a given signoff stage
	 *
	 * FIXME: Move to some DAO, initialize on load or implement
	 * via lazy-load pattern to remove coupling of domain object
	 * with DAOs.
	 *
	 * @param $signoffType string
	 * @param $idOnly boolean Return only file ID
	 * @return MonographFile
	 */
	function &getFileBySignoffType($signoffType, $idOnly = false) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$signoff = $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getId());
		if (!$signoff) {
			$returner = false;
			return $returner;
		}

		if ($idOnly) {
			$returner = $signoff->getFileId();
			return $returner;
		}

		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		assert(is_numeric($signoff->getFileId()) && is_numeric($signoff->getFileRevision()));
		$monographFile =& $submissionFileDao->getRevision($signoff->getFileId(), $signoff->getFileRevision());
		return $monographFile;
	}

	/**
	 * Get the user associated with a given signoff and this monograph
	 * @param $signoffType string
	 * @return User
	 */
	function &getUserBySignoffType($signoffType) {
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
		$signoff = $signoffDao->getBySymbolic($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getId());
		if (!$signoff) return false;

		return $signoff->getUserId();
	}

	function getWorkType() {
		return $this->getData('workType');
	}

	function setWorkType($type) {
		$this->setData('workType', $type);
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
	 * FIXME: Should be moved back to Submission class, see #5913
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
	 * FIXME: Should be moved back to Submission class, see #5913
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
	 * FIXME: should be in Submission, see #5913
	 * @return array Authors
	 */
	function &getAuthors() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		return $authorDao->getAuthorsByMonographId($this->getId());
	}

	/**
	 * Get the primary author of this submission.
	 * FIXME: should be in Submission, see #5913
	 * @return Author
	 */
	function &getPrimaryAuthor() {
		$authorDao =& DAORegistry::getDAO('AuthorDAO');
		return $authorDao->getPrimaryAuthorByMonographId($this->getId());
	}

	/**
	 * Get the current review type based on the current stage
	 * @return int
	 */
	function getCurrentReviewType() {
		import('classes.monograph.reviewRound.ReviewRound');
		switch($this->getCurrentStageId()) {
			case WORKFLOW_STAGE_ID_INTERNAL_REVIEW:
				return REVIEW_TYPE_INTERNAL;

			case WORKFLOW_STAGE_ID_EXTERNAL_REVIEW:
			default:
				return REVIEW_TYPE_EXTERNAL;
		}
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
