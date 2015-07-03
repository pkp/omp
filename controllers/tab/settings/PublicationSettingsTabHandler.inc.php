<?php

/**
 * @file controllers/tab/settings/PublicationSettingsTabHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationSettingsTabHandler
 * @ingroup controllers_tab_settings
 *
 * @brief Handle AJAX operations for tabs on Publication Process page.
 */

// Import the base Handler.
import('lib.pkp.controllers.tab.settings.ManagerSettingsTabHandler');

class PublicationSettingsTabHandler extends ManagerSettingsTabHandler {


	/**
	 * Constructor
	 */
	function PublicationSettingsTabHandler() {
		parent::ManagerSettingsTabHandler();
		$this->setPageTabs(array(
			'genres' => 'controllers/tab/settings/genres.tpl',
			'submissionStage' => 'lib.pkp.controllers.tab.settings.submissionStage.form.SubmissionStageForm',
			'reviewStage' => 'controllers.tab.settings.reviewStage.form.ReviewStageForm',
			'library' => 'controllers/tab/settings/library.tpl',
			'productionStage' => 'controllers.tab.settings.productionStage.form.ProductionStageForm',
			'emailTemplates' => 'lib.pkp.controllers.tab.settings.emailTemplates.form.EmailTemplatesForm'
		));

		// import the file type constants
		import('lib.pkp.classes.context.LibraryFile');
	}
}

?>
