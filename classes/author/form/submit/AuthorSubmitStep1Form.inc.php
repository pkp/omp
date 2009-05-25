<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep1Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep1Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 1 of author monograph submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep1Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep1Form($monograph = null) {
		parent::AuthorSubmitForm($monograph);
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();
		// Get arrangements for this press
		$arrangementDao =& DAORegistry::getDAO('AcquisitionsArrangementDAO');
		$templateMgr->assign('arrangementOptions', array('0' => Locale::translate('author.submit.selectSection')) + $arrangementDao->getAcquisitionsArrangementsTitles($press->getId(), !true));
		parent::display();
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'arrangementId' => $this->monograph->getAcquisitionsArrangementId(),
				'isEditedVolume' => $this->monograph->getWorkType(),
				'commentsToEditor' => $this->monograph->getCommentsToEditor(),
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('submissionChecklist', 'isEditedVolume', 'copyrightNoticeAgree', 'arrangementId', 'commentsToEditor'));
	}	

	function getTemplateFile() {
		return 'author/submit/step1.tpl';
	}

	/**
	 * Save changes to submission.
	 * @return int the monograph ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		if (isset($this->monograph)) {
			// Update existing monograph

			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			if ($this->monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
				$this->monograph->stampStatusModified();
				$this->monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			}
			$this->monograph->setWorkType($this->getData('isEditedVolume') ? EDITED_VOLUME :0);
			$this->monograph->setAcquisitionsArrangementId($this->getData('arrangementId'));
			$monographId = $this->monograph->getMonographId();
			$monographDao->updateMonograph($this->monograph);

		} else {
			// Insert new monograph
			$press =& Request::getPress();
			$user =& Request::getUser();

			$this->monograph =& new Monograph();
			$this->monograph->setUserId($user->getId());
			$this->monograph->setPressId($press->getId());
			$this->monograph->stampStatusModified();
			$this->monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			$this->monograph->setLanguage(String::substr($press->getPrimaryLocale(), 0, 2));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			$this->monograph->setWorkType($this->getData('isEditedVolume') ? EDITED_VOLUME : 0);
			$this->monograph->setAcquisitionsArrangementId($this->getData('arrangementId'));

			// Set user to initial author
			$author =& new Author();
			$author->setFirstName($user->getFirstName());
			$author->setMiddleName($user->getMiddleName());
			$author->setLastName($user->getLastName());
			$author->setAffiliation($user->getAffiliation());
			$author->setCountry($user->getCountry());
			$author->setEmail($user->getEmail());
			$author->setUrl($user->getUrl());
			$author->setBiography($user->getBiography(null), null);
			$author->setPrimaryContact(1);
			$this->monograph->addAuthor($author);

			$monographId = $monographDao->insertMonograph($this->monograph);
		}

		return $monographId;
	}
}

?>
