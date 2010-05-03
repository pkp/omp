<?php

/**
 * @file classes/submission/author/AuthorSubmissionDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmissionDAO
 * @ingroup submission
 * @see AuthorSubmission
 *
 * @brief Operations for retrieving and modifying AuthorSubmission objects.
 */

// $Id$


import('classes.submission.author.AuthorSubmission');

class AuthorSubmissionDAO extends DAO {
	var $monographDao;
	var $authorDao;
	var $userDao;
	var $reviewAssignmentDao;
	var $monographFileDao;
	var $copyeditorSubmissionDao;
	var $monographCommentDao;
	var $galleyDao;

	/**
	 * Constructor.
	 */
	function AuthorSubmissionDAO() {
		parent::DAO();
		$this->monographDao =& DAORegistry::getDAO('MonographDAO');
		$this->authorDao =& DAORegistry::getDAO('AuthorDAO');
		$this->userDao =& DAORegistry::getDAO('UserDAO');
		$this->reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$this->monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$this->copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$this->monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$this->galleyDao =& DAORegistry::getDAO('MonographGalleyDAO');
	}

	/**
	 * Retrieve a author submission by monograph ID.
	 * @param $monographId int
	 * @return AuthorSubmission
	 */
	function &getAuthorSubmission($monographId) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieve(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM monographs a
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.monograph_id = ?',
		array(
				'title',
		$primaryLocale,
				'title',
		$locale,
				'abbrev',
		$primaryLocale,
				'abbrev',
		$locale,
		$monographId,
		)
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnAuthorSubmissionFromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Internal function to return a AuthorSubmission object from a row.
	 * @param $row array
	 * @return AuthorSubmission
	 */
	function &_returnAuthorSubmissionFromRow(&$row) {
		$authorSubmission = new AuthorSubmission();

		// Monograph attributes
		$this->monographDao->_monographFromRow($authorSubmission, $row);

		// Editor Assignment
		$editAssignments =& $this->editAssignmentDao->getByMonographId($row['monograph_id']);
		$authorSubmission->setEditAssignments($editAssignments->toArray());


		$reviewRounds =& $authorSubmission->getReviewRoundsInfo();

		$authorSubmission->setDecisions($this->getEditorDecisions($row['monograph_id']));

		if (isset($reviewRounds)) {
			foreach ($reviewRounds as $reviewRound => $round) { // Review Assignments
				for ($i = 1; $i <= $round; $i++) {
					$authorSubmission->setReviewAssignments($this->reviewAssignmentDao->getByMonographId($row['monograph_id'], $reviewRound, $i), $reviewRound, $i);
				}
			}
		}



		// Comments
		$authorSubmission->setMostRecentEditorDecisionComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_EDITOR_DECISION, $row['monograph_id']));
		$authorSubmission->setMostRecentCopyeditComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_COPYEDIT, $row['monograph_id']));
		$authorSubmission->setMostRecentProofreadComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_PROOFREAD, $row['monograph_id']));
		$authorSubmission->setMostRecentLayoutComment($this->monographCommentDao->getMostRecentMonographComment($row['monograph_id'], COMMENT_TYPE_LAYOUT, $row['monograph_id']));

		// Files
		$authorSubmission->setSubmissionFile($this->monographFileDao->getMonographFile($row['submission_file_id']));
		$authorSubmission->setRevisedFile($this->monographFileDao->getMonographFile($row['revised_file_id']));

		if ($authorSubmission->getCurrentReviewType() != null) {
			$authorSubmission->setAuthorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['revised_file_id']));
			$authorSubmission->setEditorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['editor_file_id']));
		} else {
			$authorSubmission->setAuthorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['revised_file_id'], null, false));
			$authorSubmission->setEditorFileRevisions($this->monographFileDao->getMonographFileRevisions($row['editor_file_id'], null, false));
		}

		$authorSubmission->setGalleys($this->galleyDao->getByMonographId($row['monograph_id']));

		HookRegistry::call('AuthorSubmissionDAO::_returnAuthorSubmissionFromRow', array(&$authorSubmission, &$row));

		return $authorSubmission;
	}

	/**
	 * Update an existing author submission.
	 * @param $authorSubmission AuthorSubmission
	 */
	function updateAuthorSubmission(&$authorSubmission) {
		// Update monograph
		if ($authorSubmission->getId()) {
			$monograph =& $this->monographDao->getMonograph($authorSubmission->getId());

			// Only update fields that an author can actually edit.
			$monograph->setRevisedFileId($authorSubmission->getRevisedFileId());
			$monograph->setDateStatusModified($authorSubmission->getDateStatusModified());
			$monograph->setLastModified($authorSubmission->getLastModified());
			// FIXME: These two are necessary for designating the
			// original as the review version, but they are probably
			// best not exposed like this.
			$monograph->setReviewFileId($authorSubmission->getReviewFileId());
			$monograph->setEditorFileId($authorSubmission->getEditorFileId());

			$this->monographDao->updateMonograph($monograph);
		}

	}

	/**
	 * Get all author submissions for an author.
	 * @param $authorId int
	 * @return DAOResultFactory continaing AuthorSubmissions
	 */
	function &getAuthorSubmissions($authorId, $pressId, $active = true, $rangeInfo = null) {
		$primaryLocale = Locale::getPrimaryLocale();
		$locale = Locale::getLocale();
		$result =& $this->retrieveRange(
			'SELECT	a.*,
				COALESCE(stl.setting_value, stpl.setting_value) AS series_title,
				COALESCE(sal.setting_value, sapl.setting_value) AS series_abbrev
			FROM monographs a
				LEFT JOIN series s ON (s.series_id = a.series_id)
				LEFT JOIN series_settings stpl ON (s.series_id = stpl.series_id AND stpl.setting_name = ? AND stpl.locale = ?)
				LEFT JOIN series_settings stl ON (s.series_id = stl.series_id AND stl.setting_name = ? AND stl.locale = ?)
				LEFT JOIN series_settings sapl ON (s.series_id = sapl.series_id AND sapl.setting_name = ? AND sapl.locale = ?)
				LEFT JOIN series_settings sal ON (s.series_id = sal.series_id AND sal.setting_name = ? AND sal.locale = ?)
			WHERE	a.user_id = ? AND a.press_id = ? AND ' .
		($active?'a.status = 1':'(a.status <> 1 AND a.submission_progress = 0)'),
		array(
				'title',
		$primaryLocale,
				'title',
		$locale,
				'abbrev',
		$primaryLocale,
				'abbrev',
		$locale,
		$authorId,
		$pressId
		),
		$rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnAuthorSubmissionFromRow');
		return $returner;
	}

	//
	// Miscellaneous
	//

	/**
	 * Get the editor decisions for a review round of a monograph.
	 * @param $monographId int
	 * @param $round int
	 */
	function getEditorDecisions($monographId) {
		$decisions = array();

		$result =& $this->retrieve(
				'SELECT edit_decision_id, editor_id, decision, date_decided, review_type, round FROM edit_decisions WHERE monograph_id = ? ORDER BY date_decided ASC', $monographId);

		while (!$result->EOF) {
			$decisions[$result->fields['review_type']][$result->fields['round']][] = array(
				'editDecisionId' => $result->fields['edit_decision_id'],
				'editorId' => $result->fields['editor_id'],
				'decision' => $result->fields['decision'],
				'dateDecided' => $this->datetimeFromDB($result->fields['date_decided'])
			);
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $decisions;
	}

	/**
	 * Get count of active and complete assignments
	 * @param authorId int
	 * @param pressId int
	 */
	function getSubmissionsCount($authorId, $pressId) {
		$submissionsCount = array();
		$submissionsCount[0] = 0;
		$submissionsCount[1] = 0;

		$sql = 'SELECT count(*), status FROM monographs m
			LEFT JOIN series aa ON (aa.series_id = m.series_id) 
			WHERE m.press_id = ? AND 
				m.user_id = ? 
			GROUP BY m.status';

		$result =& $this->retrieve($sql, array($pressId, $authorId));

		while (!$result->EOF) {
			if ($result->fields['status'] != 1) {
				$submissionsCount[1] += $result->fields[0];
			} else {
				$submissionsCount[0] += $result->fields[0];
			}
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $submissionsCount;
	}
}

?>
