<?php

/**
 * @defgroup author_form_submit
 */
 
/**
 * @file classes/author/form/submit/AuthorSubmitForm.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorSubmitForm
 * @ingroup author_form_submit
 *
 * @brief Base class for journal author submit forms.
 */

// $Id$

import('submission.common.SequenceForm');

class AuthorSubmitForm extends SequenceForm {

	/**
	 * Constructor.
	 * @param $monograph object
	 * @param $step int
	 */
	function AuthorSubmitForm() {
		parent::SequenceForm();
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('submitStep', $this->sequence->currentStep);

		if (isset($this->monograph)) {
			$templateMgr->assign('submissionProgress', $this->sequence->monograph->getSubmissionProgress());
		}

		$templateMgr->assign('helpTopicId', $this->getHelpTopicId());

		$press =& Request::getPress();
		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$templateMgr->assign_by_ref('pressSettings', $settingsDao->getPressSettings($press->getId()));

		parent::display();
	}
	function getHelpTopicId() {
		return 'submission.index';
	}



	/**
	 * Automatically assign Series/Acquisition Editors to new submissions.
	 * @param $monograph object
	 * @return array of section editors
	 */
	function assignEditors(&$monograph) {
		$sectionId = $monograph->getSeriesId();
		$press =& Request::getPress();

		$seriesEditorsDao =& DAORegistry::getDAO('SectionEditorsDAO');
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$sectionEditors =& $sectionEditorsDao->getEditorsBySectionId($press->getId(), $sectionId);

		foreach ($sectionEditors as $sectionEditorEntry) {
			$editAssignment =& new EditAssignment();
			$editAssignment->setArticleId($monograph->getArticleId());
			$editAssignment->setEditorId($sectionEditorEntry['user']->getId());
			$editAssignment->setCanReview($sectionEditorEntry['canReview']);
			$editAssignment->setCanEdit($sectionEditorEntry['canEdit']);
			$editAssignmentDao->insertEditAssignment($editAssignment);
			unset($editAssignment);
		}

		return $sectionEditors;
	}

}

?>
