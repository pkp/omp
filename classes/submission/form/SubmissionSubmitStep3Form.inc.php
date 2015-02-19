<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep3Form.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep3Form
 * @ingroup submission_form
 *
 * @brief Form for Step 3 of author submission.
 */

import('lib.pkp.classes.submission.form.PKPSubmissionSubmitStep3Form');
import('classes.submission.SubmissionMetadataFormImplementation');

class SubmissionSubmitStep3Form extends PKPSubmissionSubmitStep3Form {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep3Form($context, $submission) {
		parent::PKPSubmissionSubmitStep3Form(
			$context,
			$submission,
			new SubmissionMetadataFormImplementation($this)
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		parent::readInputData();

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

		// If categories are configured, present the LB.
		$categoryDao = DAORegistry::getDAO('CategoryDAO');
		$templateMgr->assign('categoriesExist', $categoryDao->getCountByPressId($this->context->getId()) > 0);

		return parent::fetch($request);
	}

	/**
	 * Save changes to submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the submission ID
	 */
	function execute($args, $request) {
		parent::execute($args, $request);

		// handle category assignment.
		ListbuilderHandler::unpack($request, $this->getData('categories'));

		$submissionDao = Application::getSubmissionDAO();
		$submission = $submissionDao->getById($this->submissionId);

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($submission, 'SUBMISSION_ACK', null, null, null, false);
		$authorMail = new MonographMailTemplate($submission, 'SUBMISSION_ACK_NOT_USER', null, null, null, false);

		$context = $request->getContext();
		$router = $request->getRouter();
		if ($mail->isEnabled()) {
			// submission ack emails should be from the contact.
			$mail->setReplyTo($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));
			$authorMail->setReplyTo($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));

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

		// Log submission.
		import('lib.pkp.classes.log.SubmissionLog');
		import('classes.log.SubmissionEventLogEntry'); // constants
		SubmissionLog::logEvent($request, $submission, SUBMISSION_LOG_SUBMISSION_SUBMIT, 'submission.event.submissionSubmitted');

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
