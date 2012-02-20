<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep3Form.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep3Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 3 of a review.
 */


import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep3Form extends ReviewerReviewForm {
	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 * @param $reviewAssignment ReviewAssignment
	 */
	function ReviewerReviewStep3Form($request, $reviewerSubmission, $reviewAssignment) {
		parent::ReviewerReviewForm($request, $reviewerSubmission, $reviewAssignment, 3);

		// Validation checks for this form
		// FIXME #5123: Include when review form infrastructure is in place
		//$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		//$requiredReviewFormElementIds = $reviewFormElementDao->getRequiredReviewFormElementIds($this->reviewAssignment->getReviewFormId());
		//$this->addCheck(new FormValidatorCustom($this, 'reviewFormResponses', 'required', 'reviewer.monograph.reviewFormResponse.form.responseRequired', create_function('$reviewFormResponses, $requiredReviewFormElementIds', 'foreach ($requiredReviewFormElementIds as $requiredReviewFormElementId) { if (!isset($reviewFormResponses[$requiredReviewFormElementId]) || $reviewFormResponses[$requiredReviewFormElementId] == \'\') return false; } return true;'), array($requiredReviewFormElementIds)));

		$this->addCheck(new FormValidatorPost($this));
	}


	function initData() {
		$templateMgr =& TemplateManager::getManager();
		$reviewAssignment =& $this->getReviewAssignment();
		// Retrieve reviewer comment.
		$monographCommentDao =& DAORegistry::getDAO('MonographCommentDAO');
		$monographComments =& $monographCommentDao->getReviewerCommentsByReviewerId($reviewAssignment->getReviewerId(), $reviewAssignment->getSubmissionId(), $reviewAssignment->getId());
		$templateMgr->assign_by_ref('reviewerComment', $monographComments[0]);
	}

	//
	// Implement protected template methods from Form
	//
	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		// FIXME #5123: Include when review form infrastructure is in place
		$this->readUserVars(
			array(/*'reviewFormResponses', */ 'comments')
		);
	}

	/**
	 * @see Form::display()
	 */
	function display(&$request) {
		$templateMgr =& TemplateManager::getManager();

		$reviewAssignment =& $this->getReviewAssignment();
		$reviewRoundId = $reviewAssignment->getReviewRoundId();

		// Assign the objects and data to the template.
		$press = $this->request->getPress();
		$templateMgr->assign_by_ref('press', $press);
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);
		$templateMgr->assign('reviewRoundId', $reviewRoundId);

		/*  FIXME #5123: Include when review form infrastructure is in place
		if($reviewAssignment->getReviewFormId()) {

			// Get the review form components
			$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
			$reviewFormElements =& $reviewFormElementDao->getReviewFormElements($reviewAssignment->getReviewFormId());
			$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
			$reviewFormResponses =& $reviewFormResponseDao->getReviewReviewFormResponseValues($reviewAssignment->getReviewId());
			$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
			$reviewformid = $reviewAssignment->getReviewFormId();
			$reviewForm =& $reviewFormDao->getReviewForm($reviewAssignment->getReviewFormId(), ASSOC_TYPE_PRESS, $press->getId());

			$templateMgr->assign_by_ref('reviewForm', $reviewForm);
			$templateMgr->assign('reviewFormElements', $reviewFormElements);
			$templateMgr->assign('reviewFormResponses', $reviewFormResponses);
			$templateMgr->assign('isLocked', isset($reviewAssignment) && $reviewAssignment->getDateCompleted() != null);
		}*/

		//
		// Assign the link actions
		//
		import('controllers.confirmationModal.linkAction.ViewReviewGuidelinesLinkAction');
		$viewReviewGuidelinesAction = new ViewReviewGuidelinesLinkAction($request);
		$templateMgr->assign('viewGuidelinesAction', $viewReviewGuidelinesAction);

		parent::display($request);
	}

	/**
	 * @see Form::execute()
	 * @param $request PKPRequest
	 */
	function execute(&$request) {
		$reviewAssignment =& $this->getReviewAssignment();
		if($reviewAssignment->getReviewFormId()) {
			$reviewFormResponseDao =& DAORegistry::getDAO('ReviewFormResponseDAO');
			/* FIXME #5123: Include when review form infrastructure is in place
			$reviewFormResponses = $this->getData('reviewFormResponses');
			if (is_array($reviewFormResponses)) foreach ($reviewFormResponses as $reviewFormElementId => $reviewFormResponseValue) {
				$reviewFormResponse =& $reviewFormResponseDao->getReviewFormResponse($reviewAssignment->getReviewId(), $reviewFormElementId);
				if (!isset($reviewFormResponse)) {
					$reviewFormResponse = new ReviewFormResponse();
				}
				$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
				$reviewFormElement = $reviewFormElementDao->getReviewFormElement($reviewFormElementId);
				$elementType = $reviewFormElement->getElementType();
				switch ($elementType) {
					case REVIEW_FORM_ELEMENT_TYPE_SMALL_TEXT_FIELD:
					case REVIEW_FORM_ELEMENT_TYPE_TEXT_FIELD:
					case REVIEW_FORM_ELEMENT_TYPE_TEXTAREA:
						$reviewFormResponse->setResponseType('string');
						$reviewFormResponse->setValue($reviewFormResponseValue);
						break;
					case REVIEW_FORM_ELEMENT_TYPE_RADIO_BUTTONS:
					case REVIEW_FORM_ELEMENT_TYPE_DROP_DOWN_BOX:
						$reviewFormResponse->setResponseType('int');
						$reviewFormResponse->setValue($reviewFormResponseValue);
						break;
					case REVIEW_FORM_ELEMENT_TYPE_CHECKBOXES:
						$reviewFormResponse->setResponseType('object');
						$reviewFormResponse->setValue($reviewFormResponseValue);
						break;
				}
				if ($reviewFormResponse->getReviewFormElementId() != null && $reviewFormResponse->getReviewId() != null) {
					$reviewFormResponseDao->updateObject($reviewFormResponse);
				} else {
					$reviewFormResponse->setReviewFormElementId($reviewFormElementId);
					$reviewFormResponse->setReviewId($reviewAssignment->getReviewId());
					$reviewFormResponseDao->insertObject($reviewFormResponse);
				}
			} */
		} else {
			// Create a monograph comment with the review.
			$comment = new MonographComment();
			$comment->setCommentType(COMMENT_TYPE_PEER_REVIEW);
			$comment->setRoleId(ROLE_ID_REVIEWER);
			$comment->setAssocId($reviewAssignment->getId());
			$comment->setMonographId($reviewAssignment->getSubmissionId());
			$comment->setAuthorId($reviewAssignment->getReviewerId());
			$comment->setComments($this->getData('comments'));
			$comment->setCommentTitle('');
			$comment->setViewable(true);
			$comment->setDatePosted(Core::getCurrentDate());

			// Persist the monograph comment.
			$commentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$commentDao->insertMonographComment($comment);

			$monographDao =& DAORegistry::getDAO('MonographDAO');
			$monograph =& $monographDao->getById($reviewAssignment->getSubmissionId());

			$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
			$stageAssignments =& $stageAssignmentDao->getBySubmissionAndStageId($monograph->getId(), $monograph->getStageId());

			$notificationManager = new NotificationManager();
			while ($stageAssignment =& $stageAssignments->next()) {
				$notificationManager->createNotification(
					$request, $stageAssignment->getUserId(), NOTIFICATION_TYPE_REVIEWER_COMMENT,
					$monograph->getPressId(), ASSOC_TYPE_REVIEW_ASSIGNMENT, $reviewAssignment->getId()
				);
				unset($stageAssignment);
			}
		}

		// Set review to next step.
		$this->updateReviewStepAndSaveSubmission($this->getReviewerSubmission());

		// Mark the review assignment as completed.
		$reviewAssignment->setDateCompleted(Core::getCurrentDate());
		$reviewAssignment->stampModified();

		// Persist the updated review assignment.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO'); /* @var $reviewAssignmentDao ReviewAssignmentDAO */
		$reviewAssignmentDao->updateObject($reviewAssignment);

		// Update the review round status.
		$reviewRoundDao =& DAORegistry::getDAO('ReviewRoundDAO'); /* @var $reviewRoundDao ReviewRoundDAO */
		$reviewRoundDao->updateStatus($reviewAssignment->getReviewRoundId());

		// Remove the task
		$notificationDao =& DAORegistry::getDAO('NotificationDAO');
		$notifications =& $notificationDao->getNotificationsByAssoc(
			ASSOC_TYPE_REVIEW_ASSIGNMENT,
			$reviewAssignment->getId(),
			$reviewAssignment->getReviewerId(),
			NOTIFICATION_TYPE_REVIEW_ASSIGNMENT
		);
		while ($notification =& $notifications->next()) {
			$notificationDao->deleteNotificationById($notification->getId());
			unset($notification);
		}
	}
}

?>
