<?php

/**
 * @file classes/submission/form/SubmissionSubmitStep1Form.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionSubmitStep1Form
 * @ingroup submission_form
 *
 * @brief Form for Step 1 of author monograph submission.
 */


import('classes.submission.form.SubmissionSubmitForm');

class SubmissionSubmitStep1Form extends SubmissionSubmitForm {
	/**
	 * Constructor.
	 */
	function SubmissionSubmitStep1Form($press, $monograph = null) {
		parent::SubmissionSubmitForm($press, $monograph, 1);

		// Validation checks for this form
		$supportedSubmissionLocales = $press->getSetting('supportedSubmissionLocales');
		if (!is_array($supportedSubmissionLocales) || count($supportedSubmissionLocales) < 1) $supportedSubmissionLocales = array($press->getPrimaryLocale());
		$this->addCheck(new FormValidatorInSet($this, 'locale', 'required', 'submission.submit.form.localeRequired', $supportedSubmissionLocales));
		if ((boolean) $press->getSetting('copyrightNoticeAgree')) {
			$this->addCheck(new FormValidator($this, 'copyrightNoticeAgree', 'required', 'submission.submit.copyrightNoticeAgreeRequired'));
		}
		$this->addCheck(new FormValidator($this, 'authorUserGroupId', 'required', 'user.authorization.userGroupRequired'));


		foreach ($press->getLocalizedSetting('submissionChecklist') as $key => $checklistItem) {
			$this->addCheck(new FormValidator($this, "checklist-$key", 'required', 'submission.submit.checklistErrors'));
		}
	}

	/**
	 * Display the form.
	 */
	function display($request) {
		$user =& $request->getUser();

		$templateMgr =& TemplateManager::getManager();

		// FIXME: If this user is a series editor, they are allowed
		// to submit to series flagged as "editor-only" for
		// submissions. Otherwise, display only series they are allowed
		// to submit to.
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$isEditor = $roleDao->userHasRole($this->press->getId(), $user->getId(), ROLE_ID_SERIES_EDITOR);

		// Get series for this press
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesOptions = array('0' => __('submission.submit.selectSeries')) + $seriesDao->getTitlesByPressId($this->press->getId());
		$templateMgr->assign('seriesOptions', $seriesOptions);

		// Provide available submission languages. (Convert the array
		// of locale symbolic names xx_XX into an associative array
		// of symbolic names => readable names.)
		$supportedSubmissionLocales = $this->press->getSetting('supportedSubmissionLocales');
		if (empty($supportedSubmissionLocales)) $supportedSubmissionLocales = array($this->press->getPrimaryLocale());
		$templateMgr->assign(
			'supportedSubmissionLocaleNames',
			array_flip(array_intersect(
				array_flip(AppLocale::getAllLocales()),
				$supportedSubmissionLocales
			))
		);

		// if this press has a copyright notice that the author must agree to, present the form items.
		$press =& $request->getPress();
		if ((boolean) $press->getSetting('copyrightNoticeAgree')) {
			$templateMgr->assign('copyrightNotice', $press->getLocalizedSetting('copyrightNotice'));
			$templateMgr->assign('copyrightNoticeAgree', true);
		}

		// Get list of user's author user groups.  If its more than one, we'll need to display an author user group selector
		$userGroupAssignmentDao =& DAORegistry::getDAO('UserGroupAssignmentDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$authorUserGroupAssignments =& $userGroupAssignmentDao->getByUserId($user->getId(), $this->press->getId(), ROLE_ID_AUTHOR);
		if(!$authorUserGroupAssignments->wasEmpty()) {
			$authorUserGroupNames = array();
			while($authorUserGroupAssignment =& $authorUserGroupAssignments->next()) {
				$authorUserGroup =& $userGroupDao->getById($authorUserGroupAssignment->getUserGroupId());
				if ($userGroupDao->userGroupAssignedToStage($authorUserGroup->getId(), WORKFLOW_STAGE_ID_SUBMISSION)) {
					$authorUserGroupNames[$authorUserGroup->getId()] = $authorUserGroup->getLocalizedName();
				}
				unset($authorUserGroupAssignment);
			}
			$templateMgr->assign('authorUserGroupOptions', $authorUserGroupNames);
		} else {
			// The user doesn't have any author user group assignments.  They should be either a manager.
			$userGroupNames = array();

			// Add all manager user groups
			$managerUserGroupAssignments =& $userGroupAssignmentDao->getByUserId($user->getId(), $this->press->getId(), ROLE_ID_PRESS_MANAGER);
			if($managerUserGroupAssignments) while($managerUserGroupAssignment =& $managerUserGroupAssignments->next()) {
				$managerUserGroup =& $userGroupDao->getById($managerUserGroupAssignment->getUserGroupId());
				$userGroupNames[$managerUserGroup->getId()] = $managerUserGroup->getLocalizedName();
				unset($managerUserGroupAssignment);
			}

			$templateMgr->assign('authorUserGroupOptions', $userGroupNames);
		}

		parent::display($request);
	}

	/**
	 * Initialize form data from current monograph.
	 */
	function initData() {
		if (isset($this->monograph)) {
			$this->_data = array(
				'seriesId' => $this->monograph->getSeriesId(),
				'seriesPosition' => $this->monograph->getSeriesPosition(),
				'locale' => $this->monograph->getLocale(),
				'workType' => $this->monograph->getWorkType(),
				'commentsToEditor' => $this->monograph->getCommentsToEditor()
			);
		} else {
			$supportedSubmissionLocales = $this->press->getSetting('supportedSubmissionLocales');
			// Try these locales in order until we find one that's
			// supported to use as a default.
			$tryLocales = array(
				$this->getFormLocale(), // Current form locale
				AppLocale::getLocale(), // Current UI locale
				$this->press->getPrimaryLocale(), // Press locale
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
		$vars = array(
			'authorUserGroupId', 'locale', 'workType', 'copyrightNoticeAgree', 'seriesId', 'seriesPosition', 'commentsToEditor', 'copyrightNoticeAgree'
		);
		foreach ($this->press->getLocalizedSetting('submissionChecklist') as $key => $checklistItem) {
			$vars[] = "checklist-$key";
		}

		$this->readUserVars($vars);
	}

	/**
	 * Save changes to submission.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return int the monograph ID
	 */
	function execute($args, &$request) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		if (isset($this->monograph)) {
			// Update existing monograph
			$this->monograph->setSeriesId($this->getData('seriesId'));
			$this->monograph->setSeriesPosition($this->getData('seriesPosition'));
			$this->monograph->setLocale($this->getData('locale'));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
            $this->monograph->setWorkType($this->getData('workType'));
			if ($this->monograph->getSubmissionProgress() <= $this->step) {
				$this->monograph->stampStatusModified();
				$this->monograph->setSubmissionProgress($this->step + 1);
			}
			$monographDao->updateMonograph($this->monograph);
		} else {
			$user =& $request->getUser();
			$press =& $request->getPress();

			// Create new monograph
			$this->monograph = new Monograph();
			$this->monograph->setLocale($this->getData('locale'));
			$this->monograph->setUserId($user->getId());
			$this->monograph->setPressId($this->press->getId());
			$this->monograph->setSeriesId($this->getData('seriesId'));
			$this->monograph->setSeriesPosition($this->getData('seriesPosition'));
			$this->monograph->stampStatusModified();
			$this->monograph->setSubmissionProgress($this->step + 1);
			$this->monograph->setLanguage(String::substr($this->monograph->getLocale(), 0, 2));
			$this->monograph->setCommentsToEditor($this->getData('commentsToEditor'));
			$this->monograph->setWorkType($this->getData('workType'));
			$this->monograph->setStageId(WORKFLOW_STAGE_ID_SUBMISSION);
			$this->monograph->setCopyrightNotice($press->getLocalizedSetting('copyrightNotice'), $this->getData('locale'));
			// Insert the monograph
			$this->monographId = $monographDao->insertMonograph($this->monograph);

			// Set user to initial author
			$authorDao =& DAORegistry::getDAO('AuthorDAO');
			$author = new Author();
			$author->setFirstName($user->getFirstName());
			$author->setMiddleName($user->getMiddleName());
			$author->setLastName($user->getLastName());
			$author->setAffiliation($user->getAffiliation(null), null);
			$author->setCountry($user->getCountry());
			$author->setEmail($user->getEmail());
			$author->setUrl($user->getUrl());
			$author->setBiography($user->getBiography(null), null);
			$author->setPrimaryContact(1);

			// Get the user group to display the submitter as
			$authorUserGroupId = (int) $this->getData('authorUserGroupId');
			$author->setUserGroupId($authorUserGroupId);

			$author->setSubmissionId($this->monographId);
			$authorDao->insertAuthor($author);

			// Assign the user author to the stage
			$stageAssignmentDao =& DAORegistry::getDAO('StageAssignmentDAO');
			$stageAssignmentDao->build($this->monographId, $authorUserGroupId, $user->getId());
		}

		return $this->monographId;
	}
}

?>
