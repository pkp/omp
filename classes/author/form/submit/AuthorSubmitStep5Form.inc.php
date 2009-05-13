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
 * @brief Form for Step 5 of author article submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep5Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep5Form() {
		parent::AuthorSubmitForm();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$user =& Request::getUser();		
		$templateMgr =& TemplateManager::getManager();

		// Get article file for this article
		$monographFileDao =& DAORegistry::getDAO('MonographFileDAO');
		$monographFiles =& $monographFileDao->getMonographFilesByMonograph($this->sequence->monograph->getMonographId());

		$templateMgr->assign_by_ref('files', $monographFiles);
		$templateMgr->assign_by_ref('press', Request::getPress());

		// Set up required Payment Related Information
/*		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		if ( $paymentManager->submissionEnabled() || $paymentManager->fastTrackEnabled() || $paymentManager->publicationEnabled()) {
			$templateMgr->assign('authorFees', true);
			$completedPaymentDAO =& DAORegistry::getDAO('OJSCompletedPaymentDAO');
			$monographId = $this->monographId;
			
			if ( $paymentManager->submissionEnabled() ) {
				$templateMgr->assign_by_ref('submissionPayment', $completedPaymentDAO->getSubmissionCompletedPayment ( $press->getJournalId(), $monographId ));
				$templateMgr->assign('manualPayment', $press->getSetting('paymentMethodPluginName') == 'ManualPayment');
			}
			
			if ( $paymentManager->fastTrackEnabled()  ) {
				$templateMgr->assign_by_ref('fastTrackPayment', $completedPaymentDAO->getFastTrackCompletedPayment ( $press->getJournalId(), $monographId ));
			}	   
		}
		*/
		parent::display();
	}
	function getHelpTopicId() {
		return 'submission.supplementaryFiles';
	}
	function getTemplateFile() {
		return 'author/submit/step5.tpl';
	}
	/**
	 * Initialize form data from current article.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'commentsToEditor' => $this->monograph->getCommentsToEditor()
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('qualifyForWaiver', 'commentsToEditor'));
	}	

	/**
	 * Validate the form
	 */
	function validate() {
/*		import('payment.ojs.OJSPaymentManager');
		$paymentManager =& OJSPaymentManager::getManager();
		if ( $paymentManager->submissionEnabled() ) {
			if ( !$this->isValid() ) return false;
	
			$press =& Request::getJournal();
			$pressId = $press->getJournalId();
			$monographId = $this->monographId;							
			$user =& Request::getUser();
			
			$completedPaymentDAO =& DAORegistry::getDAO('OJSCompletedPaymentDAO');
			if ( $completedPaymentDAO->hasPaidSubmission ( $pressId, $monographId )  ) {
				return parent::validate();		
			} elseif ( Request::getUserVar('qualifyForWaiver') && Request::getUserVar('commentsToEditor') != '') {  
				return parent::validate();
			} elseif ( Request::getUserVar('paymentSent') ) {
				return parent::validate();
			} else {				
				$queuedPayment =& $paymentManager->createQueuedPayment($pressId, PAYMENT_TYPE_SUBMISSION, $user->getId(), $monographId, $press->getSetting('submissionFee'));
				$queuedPaymentId = $paymentManager->queuePayment($queuedPayment);
		
				$paymentManager->displayPaymentForm($queuedPaymentId, $queuedPayment);
				exit;	
			}
		} else {*/
			return parent::validate();
	//	}	
	}
	
	/**
	 * Save changes to article.
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		$press = Request::getPress();

		// Update article		
		$monograph =& $this->sequence->monograph;

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
/*		$copyeditorSubmissionDao =& DAORegistry::getDAO('CopyeditorSubmissionDAO');
		$copyeditorSubmission =& new CopyeditorSubmission();
		$copyeditorSubmission->setArticleId($monograph->getArticleId());
		$copyeditorSubmission->setCopyeditorId(0);
		$copyeditorSubmissionDao->insertCopyeditorSubmission($copyeditorSubmission);

		$proofAssignmentDao =& DAORegistry::getDAO('ProofAssignmentDAO');
		$proofAssignment =& new ProofAssignment();
		$proofAssignment->setArticleId($monograph->getArticleId());
		$proofAssignment->setProofreaderId(0);
		$proofAssignmentDao->insertProofAssignment($proofAssignment);
*/
/*
		$layoutDao =& DAORegistry::getDAO('LayoutAssignmentDAO');
		$layoutAssignment =& new LayoutAssignment();
		$layoutAssignment->setArticleId($monograph->getArticleId());
		$layoutAssignment->setEditorId(0);
		$layoutDao->insertLayoutAssignment($layoutAssignment);

		$sectionEditors = $this->assignEditors($monograph);
*/
		$user =& Request::getUser();

		// Update search index
/*		import('search.ArticleSearchIndex');
		ArticleSearchIndex::indexArticleMetadata($monograph);
		ArticleSearchIndex::indexArticleFiles($monograph);

		// Send author notification email
		import('mail.ArticleMailTemplate');
		$mail =& new ArticleMailTemplate($monograph, 'SUBMISSION_ACK');
		$mail->setFrom($press->getSetting('contactEmail'), $press->getSetting('contactName'));
		if ($mail->isEnabled()) {
			$mail->addRecipient($user->getEmail(), $user->getFullName());
			// If necessary, BCC the acknowledgement to someone.
			if($press->getSetting('copySubmissionAckPrimaryContact')) {
				$mail->addBcc(
					$press->getSetting('contactEmail'),
					$press->getSetting('contactName')
				);
			}
			if($press->getSetting('copySubmissionAckSpecified')) {
				$copyAddress = $press->getSetting('copySubmissionAckAddress');
				if (!empty($copyAddress)) $mail->addBcc($copyAddress);
			}

			// Also BCC automatically assigned section editors
			foreach ($sectionEditors as $sectionEditorEntry) {
				$sectionEditor =& $sectionEditorEntry['user'];
				$mail->addBcc($sectionEditor->getEmail(), $sectionEditor->getFullName());
				unset($sectionEditor);
			}

			$mail->assignParams(array(
				'authorName' => $user->getFullName(),
				'authorUsername' => $user->getUsername(),
				'editorialContactSignature' => $press->getSetting('contactName') . "\n" . $press->getJournalTitle(),
				'submissionUrl' => Request::url(null, 'author', 'submission', $monograph->getArticleId())
			));
			$mail->send();
		}

		import('article.log.ArticleLog');
		import('article.log.ArticleEventLogEntry');
		ArticleLog::logEvent($this->monographId, ARTICLE_LOG_ARTICLE_SUBMIT, ARTICLE_LOG_TYPE_AUTHOR, $user->getId(), 'log.author.submitted', array('submissionId' => $monograph->getArticleId(), 'authorName' => $user->getFullName()));
*/
		return $monograph->getMonographId();
	}

}

?>
