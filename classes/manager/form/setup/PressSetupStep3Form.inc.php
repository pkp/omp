<?php

/**
 * @file classes/manager/form/setup/PressSetupStep3Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep3Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 3 of press setup.
 */

// $Id$


import('manager.form.setup.PressSetupForm');

class PressSetupStep3Form extends PressSetupForm {

	var $files;
	/**
	 * Constructor.
	 */
	function PressSetupStep3Form() {
		$this->files = array(
			'pageHeaderTitleImage',
		);
		parent::PressSetupForm(
			3,
			array(
				'metaDiscipline' => 'bool',
				'metaDisciplineExamples' => 'string',
				'metaSubjectClass' => 'bool',
				'metaSubjectClassTitle' => 'string',
				'metaSubjectClassUrl' => 'string',
				'metaSubject' => 'bool',
				'metaSubjectExamples' => 'string',
				'metaCoverage' => 'bool',
				'metaCoverageGeoExamples' => 'string',
				'metaCoverageChronExamples' => 'string',
				'metaCoverageResearchSampleExamples' => 'string',
				'metaType' => 'bool',
				'metaTypeExamples' => 'string',
				'copySubmissionAckPrimaryContact' => 'bool',
				'copySubmissionAckSpecified' => 'bool',
				'copySubmissionAckAddress' => 'string'
			)
		);

		$this->addCheck(new FormValidatorEmail($this, 'copySubmissionAckAddress', 'optional', 'user.profile.form.emailRequired'));
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('metaDisciplineExamples', 'metaSubjectClassTitle', 'metaSubjectClassUrl', 'metaSubjectExamples', 'metaCoverageGeoExamples', 'metaCoverageChronExamples', 'metaCoverageResearchSampleExamples', 'metaTypeExamples');
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array('newBookFileType', 'bookFileTypeSelect'));
		parent::readInputData();
	}

	/**
	 * Display the form
	 */
	function display() {
		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();

		import('mail.MailTemplate');
		$mail = new MailTemplate('SUBMISSION_ACK');

		if ($mail->isEnabled()) {
			$templateMgr->assign('submissionAckEnabled', true);
		}

		$templateMgr->assign_by_ref('bookFileTypes', $press->getSetting('bookFileTypes'));

		parent::display();
	}
}

?>
