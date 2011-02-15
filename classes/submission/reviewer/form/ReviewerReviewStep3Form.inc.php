<?php

/**
 * @file classes/submission/reviewer/form/ReviewerReviewStep3Form.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerReviewStep3Form
 * @ingroup submission_reviewer_form
 *
 * @brief Form for Step 3 of a review.
 */


import('classes.submission.reviewer.form.ReviewerReviewForm');

class ReviewerReviewStep3Form extends ReviewerReviewForm {

	/** @var ReviewAssignment The review assignment object **/
	var $_reviewAssignment;

	/**
	 * Constructor.
	 * @param $reviewerSubmission ReviewerSubmission
	 * @param $reviewAssignment ReviewAssignment
	 */
	function ReviewerReviewStep3Form($reviewerSubmission, $reviewAssignment) {
		parent::ReviewerReviewForm($reviewerSubmission, 3);
		$this->_reviewAssignment =& $reviewAssignment;

		// Validation checks for this form
		// FIXME #5123: Include when review form infrastructure is in place
		//$reviewFormElementDao =& DAORegistry::getDAO('ReviewFormElementDAO');
		//$requiredReviewFormElementIds = $reviewFormElementDao->getRequiredReviewFormElementIds($this->reviewAssignment->getReviewFormId());
		//$this->addCheck(new FormValidatorCustom($this, 'reviewFormResponses', 'required', 'reviewer.monograph.reviewFormResponse.form.responseRequired', create_function('$reviewFormResponses, $requiredReviewFormElementIds', 'foreach ($requiredReviewFormElementIds as $requiredReviewFormElementId) { if (!isset($reviewFormResponses[$requiredReviewFormElementId]) || $reviewFormResponses[$requiredReviewFormElementId] == \'\') return false; } return true;'), array($requiredReviewFormElementIds)));

		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Setters and Getters
	//
	/**
	 * Get the review assignment
	 * @return ReviewAssignment
	 */
	function &getReviewAssignment() {
		return $this->_reviewAssignment;
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
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('review');
	}

	/**
	 * @see Form::display()
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();

		// Assign the press to the template.
		$press = Request::getPress();
		$templateMgr->assign_by_ref('press', $press);

		// Add the review assignment to the template.
		$reviewAssignment =& $this->getReviewAssignment();
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
	 * @see Form::execute()
	 */
	function execute() {
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
		}

		// Set review to next step.
		$this->updateReviewStepAndSaveSubmission($this->getReviewerSubmission());

		// Mark the review assignment as completed.
		$reviewAssignment->setDateCompleted(Core::getCurrentDate());
		$reviewAssignment->stampModified();

		// Persist the updated review assignment.
		$reviewAssignmentDao =& DAORegistry::getDAO('ReviewAssignmentDAO');
		$reviewAssignmentDao->updateObject($reviewAssignment);
	}
}

?>
