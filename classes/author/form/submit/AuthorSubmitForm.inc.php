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
 * @brief Base class for author submit forms.
 */

// $Id$

import('submission.common.SequenceForm');

class AuthorSubmitForm extends SequenceForm {
	/** @var int the ID of the monograph */
	var $monographId;

	/** @var Monograph current monograph */
	var $monograph;

	/**
	 * Constructor.
	 * @param $monograph object
	 * @param $step int
	 */
	function AuthorSubmitForm($monograph) {
		parent::SequenceForm();
		$this->addCheck(new FormValidatorPost($this));

		$this->monograph =& $monograph;
		$this->monographId = $monograph ? $monograph->getMonographId() : null;

	}

	/**
	 * Display the form.
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('submitStep', $this->sequence->currentStep);

		if (isset($this->monograph)) {
			$templateMgr->assign('submissionProgress', $this->monograph->getSubmissionProgress());
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
	 * Assign Acquisition Editors to new submissions.
	 * @param $monograph object
	 * @return array of acquisitions editors
	 */
	function assignEditors(&$monograph) {
		$acquisitionsArrangementEditorsDao =& DAORegistry::getDAO('AcquisitionsArrangementEditorsDAO');
		$editAssignmentDao =& DAORegistry::getDAO('EditAssignmentDAO');
		$press =& Request::getPress();

		$acquisitionsArrangementId = $monograph->getAcquisitionsArrangementId();

		$acquisitionsEditors =& $acquisitionsArrangementEditorsDao->getEditorsByAcquisitionsArrangementId($press->getId(), $acquisitionsArrangementId);

		foreach ($acquisitionsEditors as $acquisitionsEditor) {
			$editAssignment = new EditAssignment();
			$editAssignment->setMonographId($monograph->getMonographId());
			$editAssignment->setEditorId($acquisitionsEditor['user']->getId());
			$editAssignment->setCanReview($acquisitionsEditor['canReview']);
			$editAssignment->setCanEdit($acquisitionsEditor['canEdit']);
			$editAssignmentDao->insertEditAssignment($editAssignment);
			unset($editAssignment);
		}

		return $acquisitionsEditors;
	}
}

?>