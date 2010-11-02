<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep3Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep3Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 3 of a review.
 */



import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep3Form extends ReviewerReviewForm {

	/** @var The review assignment object **/
	var $reviewAssignment;

	/**
	 * Constructor.
	 */
	function ReviewerReviewStep3Form($reviewerSubmission = null) {
		parent::ReviewerReviewForm($reviewerSubmission, 3);

		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$this->reviewAssignment = $reviewAssignmentDao->getReviewAssignment(
			$this->reviewerSubmission->getId(),
			$this->reviewerSubmission->getReviewerId(),
			$this->reviewerSubmission->getCurrentRound(),
			$this->reviewerSubmission->getCurrentReviewType()
		);

		// Validation checks for this form
		// FIXME #5123: Include when review form infrastructure is in place
		//$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		//$requiredReviewFormElementIds = $reviewFormElementDao->getRequiredReviewFormElementIds($this->reviewAssignment->getReviewFormId());
		//$this->addCheck(new FormValidatorCustom($this, 'reviewFormResponses', 'required', 'reviewer.monograph.reviewFormResponse.form.responseRequired', create_function('$reviewFormResponses, $requiredReviewFormElementIds', 'foreach ($requiredReviewFormElementIds as $requiredReviewFormElementId) { if (!isset($reviewFormResponses[$requiredReviewFormElementId]) || $reviewFormResponses[$requiredReviewFormElementId] == \'\') return false; } return true;'), array($requiredReviewFormElementIds)));

		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		// FIXME #5123: Include when review form infrastructure is in place
		$this->readUserVars(
			array(/*'reviewFormResponses', */ 'comments')
		);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('review');
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$press = Request::getPress();

		$templateMgr->assign_by_ref('submission', $this->reviewerSubmission);
		$templateMgr->assign_by_ref('press', $press);
		$templateMgr->assign('step', 3);

		$reviewAssignment =& $this->reviewAssignment;
		$templateMgr->assign_by_ref('reviewAssignment', $reviewAssignment);

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

		parent::display();
	}

	/**
	 * Save changes to monograph.
	 * @return int the monograph ID
	 */
	function execute() {
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignment =& $this->reviewAssignment;

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
			$commentDao =& DAORegistry::getDAO('MonographCommentDAO');
			$comment = new MonographComment();
			$comment->setCommentType(COMMENT_TYPE_PEER_REVIEW);
			$comment->setRoleId(ROLE_ID_REVIEWER);
			$comment->setAssocId($reviewAssignment->getReviewId());
			$comment->setMonographId($reviewAssignment->getSubmissionId());
			$comment->setAuthorId($reviewAssignment->getReviewerId());
			$comment->setComments($this->getData('comments'));
			$comment->setCommentTitle('');
			$comment->setViewable(true);
			$comment->setDatePosted(Core::getCurrentDate());

			$commentDao->insertMonographComment($comment);
		}

		// Set review to next step
		$reviewerSubmissionDao =& DAORegistry::getDAO('ReviewerSubmissionDAO');
		if($this->reviewerSubmission->getStep() < 4) {
			$this->reviewerSubmission->setStep(4);
		}
		$reviewerSubmissionDao->updateReviewerSubmission($this->reviewerSubmission);

		// Mark the review assignment as completed.
		$reviewAssignment->setDateCompleted(Core::getCurrentDate());
		$reviewAssignment->stampModified();
		$reviewAssignmentDao->updateObject($reviewAssignment);
	}
}

?>
