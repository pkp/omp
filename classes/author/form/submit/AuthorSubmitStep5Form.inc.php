<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep5Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep5Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 5 of author monograph submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep5Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep5Form($monograph) {
		parent::AuthorSubmitForm($monograph);
	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$user =& Request::getUser();		
		$templateMgr =& TemplateManager::getManager();

		// Get monograph file for this monograph
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getByMonographId($this->monograph->getMonographId());

		$templateMgr->assign_by_ref('files', $monographFiles);
		$templateMgr->assign_by_ref('press', Request::getPress());

		parent::display();
	}
	function getHelpTopicId() {
		return 'submission.supplementaryFiles';
	}
	function getTemplateFile() {
		return 'author/submit/step5.tpl';
	}
	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'commentsToEditor' => $this->monograph->getCommentsToEditor()
			);
		}
	}

	/**
	 * Save changes to monograph.
	 */
	function execute() {		
	
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$press = Request::getPress();
		$user =& Request::getUser();
		// Update monograph		
		$monograph =& $this->monograph;

		if ($this->getData('commentsToEditor') != '') {
			$monograph->setCommentsToEditor($this->getData('commentsToEditor'));
		}

		$monograph->setDateSubmitted(Core::getCurrentDate());
		$monograph->setSubmissionProgress(0);
		$monograph->stampStatusModified();
		$monographDao->updateMonograph($monograph);

		// Designate this as the review version by default.
		$authorSubmissionDao =& DAORegistry::getDAO('AuthorSubmissionDAO');
		$authorSubmission =& $authorSubmissionDao->getAuthorSubmission($monograph->getMonographId());
		AuthorAction::designateReviewVersion($authorSubmission, true);
		unset($authorSubmission);

		// Create additional submission mangement records
		$copyeditInitialSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_INITIAL', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$copyeditAuthorSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$copyeditFinalSignoff = $signoffDao->build('SIGNOFF_COPYEDITING_FINAL', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$copyeditInitialSignoff->setUserId(0);
		$copyeditAuthorSignoff->setUserId($user->getId());
		$copyeditFinalSignoff->setUserId(0);
		$signoffDao->updateObject($copyeditInitialSignoff);
		$signoffDao->updateObject($copyeditAuthorSignoff);
		$signoffDao->updateObject($copyeditFinalSignoff);

		$layoutSignoff = $signoffDao->build('SIGNOFF_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$layoutSignoff->setUserId(0);
		$signoffDao->updateObject($layoutSignoff);

		$proofAuthorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_AUTHOR', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$proofProofreaderSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_PROOFREADER', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$proofLayoutEditorSignoff = $signoffDao->build('SIGNOFF_PROOFREADING_LAYOUT', ASSOC_TYPE_MONOGRAPH, $monograph->getMonographId());
		$proofAuthorSignoff->setUserId($user->getId());
		$proofProofreaderSignoff->setUserId(0);
		$proofLayoutEditorSignoff->setUserId(0);
		$signoffDao->updateObject($proofAuthorSignoff);
		$signoffDao->updateObject($proofProofreaderSignoff);
		$signoffDao->updateObject($proofLayoutEditorSignoff);

		$arrangementEditors = $this->assignEditors($monograph);

		$user =& Request::getUser();

		// Update search index
		import('search.MonographSearchIndex');
		MonographSearchIndex::indexMonographMetadata($monograph);
		MonographSearchIndex::indexMonographFiles($monograph);
		
		// Send author notification email
		import('mail.MonographMailTemplate');
		$mail = new MonographMailTemplate($monograph, 'SUBMISSION_ACK');
		$mail->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
		if ($mail->isEnabled()) {
			$mail->addRecipient($user->getEmail(), $user->getFullName());

			// Also BCC automatically assigned acquisitions editors
			foreach ($arrangementEditors as $acquisitionsEditorEntry) {
				$acquisitionsEditor =& $acquisitionsEditorEntry['user'];
				$mail->addBcc($acquisitionsEditor->getEmail(), $acquisitionsEditor->getFullName());
				unset($acquisitionsEditor);
			}

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getLocalizedName(),
				'submissionUrl' => Request::url(null, 'author', 'submission', $monograph->getMonographId())
			));
			$mail->send();
		}

		import('monograph.log.MonographLog');
		import('monograph.log.MonographEventLogEntry');
		MonographLog::logEvent($monograph->getMonographId(), MONOGRAPH_LOG_MONOGRAPH_SUBMIT, MONOGRAPH_LOG_TYPE_AUTHOR, $user->getId(), 'log.author.submitted', array('submissionId' => $monograph->getMonographId(), 'authorName' => $user->getFullName()));

		return $monograph->getMonographId();
	}
}

?>
