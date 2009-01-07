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
 * @brief Form for Step 1 of author article submission.
 */

// $Id$


import('author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep1Form extends AuthorSubmitForm {

	/**
	 * Display the form.
	 */
	function display() {
		parent::display();
	}

	/**
	 * Initialize form data from current article.
	 */
	function initData() {
		if (isset($this->sequence->monograph)) {
			$this->_data = array(
				'isEditedVolume' => $this->sequence->monograph->getWorkType(),
				'commentsToEditor' => $this->sequence->monograph->getCommentsToEditor(),
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('submissionChecklist', 'isEditedVolume', 'copyrightNoticeAgree', 'commentsToEditor'));
	}	

	function getTemplateFile() {
		return 'author/submit/step1.tpl';
	}

	/**
	 * Save changes to article.
	 * @return int the article ID
	 */
	function execute() {
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		if (isset($this->sequence->monograph)) {
			// Update existing article

			$this->sequence->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			if ($this->sequence->monograph->getSubmissionProgress() <= $this->sequence->currentStep) {
				$this->sequence->monograph->stampStatusModified();
				$this->sequence->monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			}
			$this->sequence->monograph->setWorkType($this->getData('isEditedVolume') ? EDITED_VOLUME :0);

			$monographId = $this->sequence->monograph->getMonographId();
			$monographDao->updateMonograph($this->sequence->monograph);

		} else {
			// Insert new article
			$press =& Request::getPress();
			$user =& Request::getUser();

			$this->monograph =& new Monograph();
			$this->monograph->setUserId($user->getUserId());
			$this->monograph->setPressId($press->getPressId());
			//$this->monograph->setSectionId(1);
			$this->monograph->stampStatusModified();
			$this->monograph->setSubmissionProgress($this->sequence->currentStep + 1);
			$this->monograph->setLanguage(String::substr($press->getPrimaryLocale(), 0, 2));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			$this->monograph->setWorkType($this->getData('isEditedVolume') ? EDITED_VOLUME : 0);
			// Set user to initial author

/*			$author =& new Author();
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
*/
			$monographId = $monographDao->insertMonograph($this->monograph);
		}

		return $monographId;
	}
}

?>
