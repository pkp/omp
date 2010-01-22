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

import('submission.Submission');
import('monograph.Author');

class Monograph extends Submission {

	var $components;
	var $removedComponents;

 	/**
	 * get monograph id
	 * @return int
	 * Constructor.
 	 */
	function Monograph() {
		parent::Submission();
		$this->components = array();
		$this->removedComponents = array();
 	}

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
	 * Get a specific monograph component.
	 * @param $componentId int
	 * @return MonographComponent
	 */
	function &getComponent($componentId) {
		$component = null;

		if (!empty($componentId)) {
			for ($i=0, $count=count($this->components); $i < $count && $components == null; $i++) {
				if ($this->components[$i]->getId() == $componentId) {
					$component =& $this->components[$i];
				}
			}
		}
		return $component;
	}

	/**
	 * Add a monograph component.
	 * @param $component MonographComponent
	 */
	function addComponent($component) {
		if ($component->getSequence() == null) {
			$component->setSequence(count($this->components) + 1);
		}
		array_push($this->components, $component);
	}

	/**
	 * Remove a monograph component.
	 * @param $componentId ID of the component to remove
	 * @return boolean component was removed
	 */
	function removeComponent($componentId) {
		$found = false;

		if (!empty($componentId)) {
			// FIXME maintain a hash of ID to component for quicker get/remove
			$components = array();
			for ($i=0, $count=count($this->components); $i < $count; $i++) {
				if ($this->components[$i]->getId() == $componentId) {
					array_push($this->removedComponents, $componentId);
					$found = true;
				} else {
					array_push($components, $this->components[$i]);
				}
			}
			$this->components = $components;
 		}
		return $found;
 	}

	/**
	 * Get the IDs of all components removed from this submission.
	 * @return array int
	 */
	function &getRemovedComponents() {
		return $this->removedComponents;
 	}

	/**
	 * Set monograph components.
	 * @param $components array MonographComponent
	 */
	function setComponents($components) {
		$this->components = $components;
	}

	/**
	 * Get all monograph components.
	 * @return array MonographComponent
	 */
	function &getComponents() {
		return $this->components;
	}

	//
	// Acquisitions Arrangements
	//

	/**
	 * Get the acquisitions arrangement id.
	 * @return int
	 */
	function getArrangementId() {
		 return $this->getData('arrangementId');
	}

	/**
	 * Set the acquisitions arrangement id.
	 * @param $id int
	 */
	function setArrangementId($id) {
		 $this->setData('arrangementId', $id);
	}

	/**
	 * Get the arrangement's abbreviated identifier.
	 * @return string
	 */
	function getArrangementAbbrev() {
		 return $this->getData('arrangementAbbrev');
	}

	/**
	 * Set the arrangement's abbreviated identifier.
	 * @param $abbrev string
	 */
	function setArrangementAbbrev($abbrev) {
		 $this->setData('arrangementAbbrev', $abbrev);
	}

	/**
	 * Get the arrangement's title.
	 * @return string
	 */
	function getArrangementTitle() {
		 return $this->getData('arrangementTitle');
	}

	/**
	 * Set the arrangement title.
	 * @param $title string
	 */
	function setArrangementTitle($title) {
		 $this->setData('arrangementTitle', $title);
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
		$monographId = $this->getMonographId();
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

		if($copyeditor) {
			$copyedSignoff = $signoffDao->getBySymbolic('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monographId);
			$userId = $copyedSignoff->getUserId();
			if ($userId) $userIds[] = array('id' => $userId, 'role' => 'copyeditor');
		}

/*		if($layoutEditor) {
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
			$reviewAssignments =& $reviewAssignmentDao->getByMonographId($monographId);
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

		$signoff =& $signoffDao->build($signoffType, ASSOC_TYPE_MONOGRAPH, $this->getMonographId());

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
	 * get public monograph id
	 * @return string
	 */
	function getPublicMonographId() {
		// Ensure that blanks are treated as nulls
		$returner = $this->getData('publicMonographId');
		if ($returner === '') return null;
		return $returner;
	}

	/**
	 * set public monograph id
	 * @param $publicMonographId string
	 */
	function setPublicMonographId($publicMonographId) {
		return $this->setData('publicMonographId', $publicMonographId);
	}

	/**
	 * Return the "best" monograph ID -- If a public monograph ID is set,
	 * use it; otherwise use the internal monograph Id. (Checks the monograph
	 * settings to ensure that the public ID feature is enabled.)
	 * @param $monograph object The press that is preparing this monograph
	 * @return string
	 */
	function getBestMonographId($press = null) {
		// Retrieve the press object, if necessary.
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
