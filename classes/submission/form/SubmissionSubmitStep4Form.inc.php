<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep4Form.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep4Form
 * @ingroup submission_form
 *
 * @brief Form for Step 4 of author submission.
 */

import('lib.pkp.classes.submission.form.PKPSubmissionSubmitStep4Form');

class SubmissionSubmitStep4Form extends PKPSubmissionSubmitStep4Form {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep4Form($context, $submission) {
		parent::PKPSubmissionSubmitStep4Form($context, $submission);
	}

	/**
	 * Save changes to submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the submission ID
	 */
	function execute($args, $request) {
		parent::execute($args, $request);

		// Send author notification email
		import('classes.mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($this->submission, 'SUBMISSION_ACK', null, null, false);
		$authorMail = new MonographMailTemplate($this->submission, 'SUBMISSION_ACK_NOT_USER', null, null, false);

		$context = $request->getContext();
		$router = $request->getRouter();
		if ($mail->isEnabled()) {
			// submission ack emails should be from the contact.
			$mail->setReplyTo($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));
			$authorMail->setReplyTo($this->context->getSetting('contactEmail'), $this->context->getSetting('contactName'));

			$user = $request->getUser();
			$primaryAuthor = $this->submission->getPrimaryAuthor();
			if (!isset($primaryAuthor)) {
				$authors = $this->submission->getAuthors();
				$primaryAuthor = $authors[0];
			}
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			if ($context->getSetting('copySubmissionAckPrimaryContact')) {
				$mail->addBcc(
					$context->getSetting('contactEmail'),
					$context->getSetting('contactName')
				);
			}
			if ($copyAddress = $context->getSetting('copySubmissionAckAddress')) {
				$mail->addBcc($copyAddress);
			}

			if ($user->getEmail() != $primaryAuthor->getEmail()) {
				$authorMail->addRecipient($primaryAuthor->getEmail(), $primaryAuthor->getFullName());
			}

			$assignedAuthors = $this->submission->getAuthors();

			foreach ($assignedAuthors as $author) {
				$authorEmail = $author->getEmail();
				// only add the author email if they have not already been added as the primary author
				// or user creating the submission.
				if ($authorEmail != $primaryAuthor->getEmail() && $authorEmail != $user->getEmail()) {
					$authorMail->addRecipient($author->getEmail(), $author->getFullName());
				}
			}
			$mail->bccAssignedSeriesEditors($this->submissionId, WORKFLOW_STAGE_ID_SUBMISSION);

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $context->getSetting('contactName') . "\n" . $context->getLocalizedName(),
				'submissionUrl' => $router->url($request, null, 'authorDashboard', 'submission', $this->submissionId),
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
		SubmissionLog::logEvent($request, $this->submission, SUBMISSION_LOG_SUBMISSION_SUBMIT, 'submission.event.submissionSubmitted');

		return $this->submissionId;
	}
}

?>
