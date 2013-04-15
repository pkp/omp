<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author submission.
 */

import('lib.pkp.classes.submission.form.SubmissionSubmitForm');
import('classes.submission.SubmissionMetadataFormImplementation');

class SubmissionSubmitStep3Form extends SubmissionSubmitForm {

	/** @var SubmissionMetadataFormImplementation */
	var $_metadataFormImplem;

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($context, $submission) {
		parent::SubmissionSubmitForm($context, $submission, 3);

		$this->_metadataFormImplem = new SubmissionMetadataFormImplementation($this);

		$this->_metadataFormImplem->addChecks($submission);
	}

	/**
	 * Initialize form data from current submission.
	 */
	function initData() {

		$this->_metadataFormImplem->initData($this->submission);

		return parent::initData();
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {

		$this->_metadataFormImplem->readInputData();

		// Include category information.
		$this->readUserVars(array('categories'));

		// Load the series. This is used in the step 3 form to
		// determine whether or not to display indexing options.
		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$this->_data['series'] = $seriesDao->getById($this->submission->getSeriesId(), $this->submission->getContextId());
	}

	/**
	 * Fetch the form
	 */
	function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);

		$templateMgr->assign('isEditedVolume', $this->submission->getWorkType() == WORK_TYPE_EDITED_VOLUME);

		// If categories are configured, present the LB.
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$templateMgr->assign('categoriesExist', $categoryDao->getCountByPressId($this->context->getId()) > 0);

		return parent::fetch($request);
	}

	/**
	 * Get the names of fields for which data should be localized
	 * @return array
	 */
	function getLocaleFieldNames() {
		$this->_metadataFormImplem->getLocaleFieldNames();
	}

	/**
	 * Save changes to submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the submission ID
	 */
	function execute($args, $request) {

		// Execute submission metadata related operations.
		$this->_metadataFormImplem->execute($this->submission, $request);

		// handle category assignment.
		ListbuilderHandler::unpack($request, $this->getData('categories'));

		// Get an updated version of the submission.
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$submission = $submissionDao->getById($this->submissionId);

		// Set other submission data.
		if ($submission->getSubmissionProgress() <= $this->step) {
			$submission->setDateSubmitted(Core::getCurrentDate());
			$submission->stampStatusModified();
			$submission->setSubmissionProgress(0);
		}

		// Save the submission.
		$submissionDao->updateObject($submission);

		// Assign the default users to the submission workflow stage
		import('classes.submission.seriesEditor.SeriesEditorAction');
		$seriesEditorAction = new SeriesEditorAction();
		$seriesEditorAction->assignDefaultStageParticipants($submission, WORKFLOW_STAGE_ID_SUBMISSION, $request);

		//
		// Send a notification to associated users
		//

		$roleDao = DAORegistry::getDAO('RoleDAO'); /* @var $roleDao RoleDAO */

		// Get the managers.
		$managers = $roleDao->getUsersByRoleId(ROLE_ID_MANAGER, $submission->getContextId());

		$managersArray = $managers->toAssociativeArray();

		$allUserIds = array_keys($managersArray);

		$notificationManager = new NotificationManager();
		foreach ($allUserIds as $userId) {
			$notificationManager->createNotification(
				$request, $userId, NOTIFICATION_TYPE_SUBMISSION_SUBMITTED,
				$submission->getContextId(), ASSOC_TYPE_SUBMISSION, $submission->getId()
			);

			// Add TASK notification indicating that a submission is unassigned
			$notificationManager->createNotification(
				$request,
				$userId,
				NOTIFICATION_TYPE_EDITOR_ASSIGNMENT_REQUIRED,
				$submission->getContextId(),
				ASSOC_TYPE_MONOGRAPH,
				$submission->getId(),
				NOTIFICATION_LEVEL_TASK
			);
		}

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($submission, 'SUBMISSION_ACK');
		$authorMail = new MonographMailTemplate($submission, 'SUBMISSION_ACK_NOT_USER');

		$context = $request->getContext();
		$router = $request->getRouter();
		if ($mail->isEnabled()) {
			// submission ack emails should be from the contact.
			$mail->setFrom($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));
			$authorMail->setFrom($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));

			$user = $submission->getUser();
			$primaryAuthor = $submission->getPrimaryAuthor();
			if (!isset($primaryAuthor)) {
				$authors = $submission->getAuthors();
				$primaryAuthor = $authors[0];
			}
			$mail->addRecipient($user->getEmail(), $user->getFullName());

			if ($user->getEmail() != $primaryAuthor->getEmail()) {
				$authorMail->addRecipient($primaryAuthor->getEmail(), $primaryAuthor->getFullName());
			}
			if ($context->getSetting('copySubmissionAckPrimaryContact')) {
				$authorMail->addBcc(
					$context->getSetting('contactEmail'),
					$context->getSetting('contactName')
				);
			}
			if ($copyAddress = $context->getSetting('copySubmissionAckAddress')) {
				$authorMail->addBcc($copyAddress);
			}

			$assignedAuthors = $submission->getAuthors();

			foreach ($assignedAuthors as $author) {
				$authorEmail = $author->getEmail();
				// only add the author email if they have not already been added as the primary author
				// or user creating the submission.
				if ($authorEmail != $primaryAuthor->getEmail() && $authorEmail != $user->getEmail()) {
					$authorMail->addRecipient($author->getEmail(), $author->getFullName());
				}
			}
			$mail->bccAssignedSeriesEditors($submission->getId(), WORKFLOW_STAGE_ID_SUBMISSION);

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $context->getSetting('contactName') . "\n" . $context->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'submission', $submission->getId()),
			));

			$authorMail->assignParams(array(
				'submitterName' => $user->getFullName(),
				'editorialContactSignature' => $context->getSetting('contactName') . "\n" . $context->getLocalizedName(),
			));

			$mail->send($request);

			$recipients = $authorMail->getRecipients();
			if (!empty($recipients)) {
				$authorMail->send($request);
			}
		}

		$notificationManager->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$submission->getId()
		);

		// Log submission.
		import('classes.log.MonographLog');
		MonographLog::logEvent($request, $submission, SUBMISSION_LOG_SUBMISSION_SUBMIT, 'submission.event.monographSubmitted');

		return $this->submissionId;
	}

	/**
	 * Associate a category with a submission.
	 * @see ListbuilderHandler::insertEntry
	 */
	function insertEntry($request, $newRowId) {

		$application = PKPApplication::getApplication();
		$request = $application->getRequest();

		$categoryId = $newRowId['name'];
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$submissionDao = DAORegistry::getDAO('MonographDAO');
		$context = $request->getContext();
		$submission = $this->submission;

		$category = $categoryDao->getById($categoryId, $context->getId());
		if (!$category) return true;

		// Associate the category with the submission
		$submissionDao->addCategory(
				$submission->getId(),
				$categoryId
		);
	}

	/**
	 * Delete a category association.
	 * @see ListbuilderHandler::deleteEntry
	 */
	function deleteEntry($request, $rowId) {
		if ($rowId) {
			$categoryDao = DAORegistry::getDAO('CategoryDAO');
			$submissionDao = DAORegistry::getDAO('MonographDAO');
			$category = $categoryDao->getById($rowId);
			if (!is_a($category, 'Category')) {
				assert(false);
				return false;
			}
			$submission = $this->submission;
			$submissionDao->removeCategory($submission->getId(), $rowId);
		}

		return true;
	}

	/**
	 * Update a category association.
	 * @see ListbuilderHandler::updateEntry
	 */
	function updateEntry($request, $rowId, $newRowId) {

		$this->deleteEntry($request, $rowId);
		$this->insertEntry($request, $newRowId);
	}
}

?>
