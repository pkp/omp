<?php

/**
 * @file classes/author/form/submit/SubmissionSubmitStep1Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep1Form
 * @ingroup author_form_submit
 *
 * @brief Form for Step 1 of author monograph submission.
 */


import('classes.submission.form.submit.SubmissionSubmitForm');

class SubmissionSubmitStep1Form extends SubmissionSubmitForm {

	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep1Form($monograph = null) {
		parent::SubmissionSubmitForm($monograph, 1);

		$press =& Request::getPress();

		// Validation checks for this form
		$supportedSubmissionLocales = $press->getSetting('supportedSubmissionLocales');
		if (!is_array($supportedSubmissionLocales) || count($supportedSubmissionLocales) < 1) $supportedSubmissionLocales = array($press->getPrimaryLocale());
		$this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'submission.submit.form.localeRequired', $supportedSubmissionLocales));
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

		// FIXME: If this user is a series editor or an editor, they are
		// allowed to submit to series flagged as "editor-only" for
		// submissions. Otherwise, display only series they are allowed
		// to submit to.
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_EDITOR) || $roleDao->userHasRole($press->getId(), $user->getId(), ROLE_ID_SERIES_EDITOR);

		$seriesOptions = array('0' => Locale::translate('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($press->getId());
		$templateMgr->assign('seriesOptions', $seriesOptions);

		// Provide available submission languages. (Convert the array
		// of locale symbolic names xx_XX into an associative array
		// of symbolic names => readable names.)
		$supportedSubmissionLocales = $press->getSetting('supportedSubmissionLocales');
		if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = array($press->getPrimaryLocale());
		$templateMgr->assign(
			'supportedSubmissionLocaleNames',
			array_flip(array_intersect(
				array_flip(Locale::getAllLocales()),
				$supportedSubmissionLocales
			))
		);

		parent::display();
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'seriesId' => $this->monograph->getSeriesId(),
				'locale' => $this->monograph->getLocale(),
				'isEditedVolume' => $this->monograph->getWorkType() == WORK_TYPE_EDITED_VOLUME ? true : false,
				'commentsToEditor' => $this->monograph->getCommentsToEditor()
			);
		} else {
			$press =& Request::getPress();
			$supportedSubmissionLocales = $press->getSetting('supportedSubmissionLocales');
			// Try these locales in order until we find one that's
			// supported to use as a default.
			$tryLocales = array(
				$this->getFormLocale(), // Current form locale
				Locale::getLocale(), // Current UI locale
				$press->getPrimaryLocale(), // Press locale
				$supportedSubmissionLocales[array_shift(array_keys($supportedSubmissionLocales))] // Fallback: first one on the list
			);
			$this->_data = array();
			foreach ($tryLocales as $locale) {
				if (in_array($locale, $supportedSubmissionLocales)) {
					// Found a default to use
					$this->_data['locale'] = $locale;
					break;
				}
			}
		}
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('locale', 'submissionChecklist', 'isEditedVolume', 'copyrightNoticeAgree', 'seriesId', 'commentsToEditor'));
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
			$this->monograph->setLocale($this->getData('locale'));
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
			$this->monograph->setLocale($this->getData('locale'));
			$this->monograph->setUserId($user->getId());
			$this->monograph->setUserGroupId($actingAsUserGroupId);
			$this->monograph->setPressId($press->getId());
			$this->monograph->setSeriesId($this->getData('seriesId'));
			$this->monograph->stampStatusModified();
			$this->monograph->setSubmissionProgress($this->step + 1);
			$this->monograph->setLanguage(String::substr($press->getPrimaryLocale(), 0, 2));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			$this->monograph->setWorkType($this->getData('workType') ? WORK_TYPE_EDITED_VOLUME : 0);

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
			$author->setAffiliation($user->getAffiliation(null), null);
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
