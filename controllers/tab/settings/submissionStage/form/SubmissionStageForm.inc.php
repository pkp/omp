<?php

/**
 * @file controllers/tab/settings/submissionStage/form/SubmissionStageForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionStageForm
 * @ingroup controllers_tab_settings_submissionStage_form
 *
 * @brief Form to edit press submission stage information.
 */


// Import the base Form.
import('controllers.tab.settings.form.PressSettingsForm');

class SubmissionStageForm extends PressSettingsForm {

	/**
	 * Constructor.
	 */
	function SubmissionStageForm() {
		$settings = array();

		parent::PressSettingsForm($settings, 'controllers/tab/settings/submissionStage/form/submissionStageForm.tpl');
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @see Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array('focusScopeDesc');
	}
}

