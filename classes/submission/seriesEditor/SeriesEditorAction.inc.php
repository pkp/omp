<?php

/**
 * @file classes/submission/seriesEditor/SeriesEditorAction.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesEditorAction
 * @ingroup submission
 *
 * @brief SeriesEditorAction class.
 */


// Access decision actions constants.
import('classes.workflow.EditorDecisionActionsManager');
import('lib.pkp.classes.submission.action.PKPAction');

class SeriesEditorAction extends PKPAction {

	/**
	 * Constructor.
	 */
	function SeriesEditorAction() {
		parent::PKPAction();
	}

	//
	// Actions.
	//

	/**
	 * Assign the default participants to a workflow stage.
	 * @param $monograph Monograph
	 * @param $stageId int
	 * @param $request Request
	 */
	function assignDefaultStageParticipants($submission, $stageId, $request) {
		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');

		// Managerial roles are skipped -- They have access by default and
		//  are assigned for informational purposes only

		// Series editor roles are skipped -- They are assigned by PM roles
		//  or by other series editors

		// Press roles -- For each press role user group assigned to this
		//  stage in setup, iff there is only one user for the group,
		//  automatically assign the user to the stage
		// But skip authors and reviewers, since these are very submission specific
		$stageAssignmentDao = DAORegistry::getDAO('StageAssignmentDAO');
		$submissionStageGroups = $userGroupDao->getUserGroupsByStage($submission->getContextId(), $stageId, true, true);
		while ($userGroup = $submissionStageGroups->next()) {
			$users = $userGroupDao->getUsersById($userGroup->getId());
			if($users->getCount() == 1) {
				$user = $users->next();
				$stageAssignmentDao->build($submission->getId(), $userGroup->getId(), $user->getId());
			}
		}

		$notificationMgr = new NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_SUBMISSION,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_INTERNAL_REVIEW,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EXTERNAL_REVIEW,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_EDITING,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_PRODUCTION),
			null,
			ASSOC_TYPE_SUBMISSION,
			$submission->getId()
		);

		// Reviewer roles -- Do nothing. Reviewers are not included in the stage participant list, they
		// are administered via review assignments.

		// Author roles
		// Assign only the submitter in whatever ROLE_ID_AUTHOR capacity they were assigned previously
		$submitterAssignments = $stageAssignmentDao->getBySubmissionAndStageId($submission->getId(), null, null, $submission->getUserId());
		while ($assignment = $submitterAssignments->next()) {
			$userGroup = $userGroupDao->getById($assignment->getUserGroupId());
			if ($userGroup->getRoleId() == ROLE_ID_AUTHOR) {
				$stageAssignmentDao->build($submission->getId(), $userGroup->getId(), $assignment->getUserId());
				// Only assign them once, since otherwise we'll one assignment for each previous stage.
				// And as long as they are assigned once, they will get access to their submission.
				break;
			}
		}
	}

	/**
	 * Increment a submission's workflow stage.
	 * @param $submission Submission
	 * @param $newStage integer One of the WORKFLOW_STAGE_* constants.
	 * @param $request Request
	 */
	function incrementWorkflowStage($submission, $newStage, $request) {
		// Change the submission's workflow stage.
		$submission->setStageId($newStage);
		$submissionDao = Application::getSubmissionDAO();
		$submissionDao->updateObject($submission);

		// Assign the default users to the next workflow stage.
		$this->assignDefaultStageParticipants($submission, $newStage, $request);
	}

	/**
	 * Get the text of all peer reviews for a submission
	 * @param $seriesEditorSubmission SeriesEditorSubmission
	 * @param $reviewRoundId int
	 * @return string
	 */
	function getPeerReviews($seriesEditorSubmission, $reviewRoundId) {
		$reviewAssignmentDao = DAORegistry::getDAO('ReviewAssignmentDAO');
		$submissionCommentDao = DAORegistry::getDAO('SubmissionCommentDAO');
		$reviewFormResponseDao = DAORegistry::getDAO('ReviewFormResponseDAO');
		$reviewFormElementDao = DAORegistry::getDAO('ReviewFormElementDAO');

		$reviewAssignments =& $reviewAssignmentDao->getBySubmissionId($seriesEditorSubmission->getId(), $reviewRoundId);
		$reviewIndexes =& $reviewAssignmentDao->getReviewIndexesForRound($seriesEditorSubmission->getId(), $reviewRoundId);
		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_SUBMISSION);

		$body = '';
		$textSeparator = "------------------------------------------------------";
		foreach ($reviewAssignments as $reviewAssignment) {
			// If the reviewer has completed the assignment, then import the review.
			if ($reviewAssignment->getDateCompleted() != null && !$reviewAssignment->getCancelled()) {
				// Get the comments associated with this review assignment
				$submissionComments = $submissionCommentDao->getSubmissionComments($seriesEditorSubmission->getId(), COMMENT_TYPE_PEER_REVIEW, $reviewAssignment->getId());

				$body .= "\n\n$textSeparator\n";
				// If it is not a double blind review, show reviewer's name.
				if ($reviewAssignment->getReviewMethod() != SUBMISSION_REVIEW_METHOD_DOUBLEBLIND) {
					$body .= $reviewAssignment->getReviewerFullName() . "\n";
				} else {
					$body .= __('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n";
				}

				while ($comment = $submissionComments->next()) {
					// If the comment is viewable by the author, then add the comment.
					if ($comment->getViewable()) {
						$body .= String::html2text($comment->getComments()) . "\n\n";
					}
				}
				$body .= "$textSeparator\n\n";

				if ($reviewFormId = $reviewAssignment->getReviewFormId()) {
					$reviewId = $reviewAssignment->getId();


					$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewFormId);
					if(!$submissionComments) {
						$body .= "$textSeparator\n";

						$body .= __('submission.comments.importPeerReviews.reviewerLetter', array('reviewerLetter' => String::enumerateAlphabetically($reviewIndexes[$reviewAssignment->getId()]))) . "\n\n";
					}
					foreach ($reviewFormElements as $reviewFormElement) {
						$body .= String::html2text($reviewFormElement->getLocalizedQuestion()) . ": \n";
						$reviewFormResponse = $reviewFormResponseDao->getReviewFormResponse($reviewId, $reviewFormElement->getId());

						if ($reviewFormResponse) {
							$possibleResponses = $reviewFormElement->getLocalizedPossibleResponses();
							if (in_array($reviewFormElement->getElementType(), $reviewFormElement->getMultipleResponsesElementTypes())) {
								if ($reviewFormElement->getElementType() == REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES) {
									foreach ($reviewFormResponse->getValue() as $value) {
										$body .= "\t" . String::htmltext($possibleResponses[$value-1]['content']) . "\n";
									}
								} else {
									$body .= "\t" . String::html2text($possibleResponses[$reviewFormResponse->getValue()-1]['content']) . "\n";
								}
								$body .= "\n";
							} else {
								$body .= "\t" . String::html2text($reviewFormResponse->getValue()) . "\n\n";
							}
						}

					}
					$body .= "$textSeparator\n\n";

				}


			}
		}

		return $body;
	}
}

?>
