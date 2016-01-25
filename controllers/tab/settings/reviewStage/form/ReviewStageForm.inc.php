<?php

/**
 * @file controllers/tab/settings/reviewStage/form/ReviewStageForm.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewStageForm
 * @ingroup controllers_tab_settings_reviewStage_form
 *
 * @brief Form to edit review stage settings.
 */

import('lib.pkp.controllers.tab.settings.reviewStage.form.PKPReviewStageForm');

class ReviewStageForm extends PKPReviewStageForm {

	/**
	 * Constructor.
	 */
	function ReviewStageForm($wizardMode = false, $settings = array(), $template = 'controllers/tab/settings/reviewStage/form/reviewStageForm.tpl') {
		parent::PKPReviewStageForm(
			$wizardMode,
			array_merge(
				$settings,
				array(
					'internalReviewGuidelines' => 'string',
				)
			),
			$template
		);
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * @copydoc Form::getLocaleFieldNames()
	 */
	function getLocaleFieldNames() {
		return array_merge(
			parent::getLocaleFieldNames(),
			array('internalReviewGuidelines')
		);
	}
}

?>
