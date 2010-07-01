<?php

/**
 * @file classes/author/form/submit/AuthorSubmitStep1Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitStep1Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 1 of author monograph submission.
 */

// $Id$


import('classes.author.form.submit.AuthorSubmitForm');

class AuthorSubmitStep1Form extends AuthorSubmitForm {

	/**
	 * Constructor.
	 */
	function AuthorSubmitStep1Form($monograph = null) {
		parent::AuthorSubmitForm($monograph, 1);

		$press =& Request::getPress();

	}

	/**
	 * Display the form.
	 */
	function display() {
		$press =& Request::getPress();
		$user =& Request::getUser();

		$templateMgr =& TemplateManager::getManager();

		// Get series for this press
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');

		// FIXME: If this user is a series editor or an editor, they are allowed
		// to submit to series flagged as "editor-only" for submissions.
		// Otherwise, display only series they are allowed to submit to.
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR) || $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_SERIES_EDITOR);

		$seriesOptions = array('0' => Locale::translate('author.submit.selectSeries')) + $seriesDao->getTitlesByPressId($press->getId());
		$templateMgr->assign('seriesOptions', $seriesOptions);
		parent::display();
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'seriesId' => $this->monograph->getSeriesId(),
				'isEditedVolume' => $this->monograph->getWorkType(),
				'commentsToEditor' => $this->monograph->getCommentsToEditor()
			);
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('submissionChecklist', 'isEditedVolume', 'copyrightNoticeAgree', 'seriesId', 'commentsToEditor'));
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
			$this->monograph->setSeriesId($this->getData('seriesId'));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			if ($this->monograph->getSubmissionProgress() <= $this->step) {
				$this->monograph->stampStatusModified();
				$this->monograph->setSubmissionProgress($this->step + 1);
			}
			$monographDao->updateMonograph($this->monograph);

		} else {
			$press =& Request::getPress();
			$user =& Request::getUser();

			// Get the session and the user group id currently used
			$sessionMgr =& SessionManager::getManager();
			$session =& $sessionMgr->getUserSession();
			$actingAsUserGroupId = $session->getActingAsUserGroupId();

			// Create new monograph
			$this->monograph = new Monograph();
			$this->monograph->setLocale($press->getPrimaryLocale()); // FIXME in bug #5543
			$this->monograph->setUserId($user->getId());
			$this->monograph->setUserGroupId($actingAsUserGroupId);
			$this->monograph->setPressId($press->getId());
			$this->monograph->setSeriesId($this->getData('seriesId'));
			$this->monograph->stampStatusModified();
			$this->monograph->setSubmissionProgress($this->step + 1);
			$this->monograph->setLanguage(String::substr($press->getPrimaryLocale(), 0, 2));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			$this->monograph->setWorkType($this->getData('isEditedVolume') ? WORK_TYPE_EDITED_VOLUME : 0);

			// Get a default user group id for an Author
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$defaultAuthorGroup =& $userGroupDao->getDefaultByRoleId($press->getId(), ROLE_ID_AUTHOR);

			// Set user to initial author
			$authorDao =& DAORegistry::getDAO('AuthorDAO');
			$user =& Request::getUser();
			$author = new Author();
			$author->setFirstName($user->getFirstName());
			$author->setMiddleName($user->getMiddleName());
			$author->setLastName($user->getLastName());
			$author->setAffiliation($user->getAffiliation());
			$author->setCountry($user->getCountry());
			$author->setEmail($user->getEmail());
			$author->setUrl($user->getUrl());
			$author->setUserGroupId($defaultAuthorGroup->getId());
			$author->setBiography($user->getBiography(null), null);
			$author->setPrimaryContact(1);

			$monographDao->insertMonograph($this->monograph);
			$this->monographId = $this->monograph->getId();
			$author->setSubmissionId($this->monographId);
			$authorDao->insertAuthor($author);
		}

		return $this->monographId;
	}

}

?>
