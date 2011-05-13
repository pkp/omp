<?php

/**
 * @file controllers/tab/settings/ProcessSettingsTabHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProcessSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Publication Process page.
 */

// Import the base Handler.
import('controllers.tab.settings.SettingsTabHandler');

class ProcessSettingsTabHandler extends SettingsTabHandler {


	/**
	 * Constructor
	 */
	function ProcessSettingsTabHandler() {
		parent::SettingsTabHandler();
		$pageTabs = array(
			'general' => 'controllers/tab/settings/generalSettings.tpl',
			'submissionStage' => 'controllers.tab.settings.submissionStage.form.SubmissionStageForm',
			'reviewStage' => 'controllers.tab.settings.reviewStage.form.ReviewStageForm',
			'editorialStage' => 'controllers/tab/settings/editorialStage.tpl',
			'productionStage' => 'controllers/tab/settings/productionStage.tpl',
			'emailTemplates' => 'controllers.tab.settings.emailTemplates.form.EmailTemplatesForm'
		);
		$this->setPageTabs($pageTabs);

		// import the file type constants
		import('classes.press.LibraryFile');
	}
}

?>
