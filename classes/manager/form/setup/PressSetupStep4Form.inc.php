<?php

/**
 * @file classes/manager/form/setup/PressSetupStep4Form.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep4Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 4 of the press setup.
 */

// $Id$


import("manager.form.setup.PressSetupForm");

class PressSetupStep4Form extends PressSetupForm {
	/**
	 * Constructor.
	 */
	function PressSetupStep4Form() {
		parent::PressSetupForm(
			4,
			array(
				'disableUserReg' => 'bool',
				'allowRegReader' => 'bool',
				'allowRegAuthor' => 'bool',
				'allowRegReviewer' => 'bool',
				'restrictSiteAccess' => 'bool',
				'restrictArticleAccess' => 'bool',
				'articleEventLog' => 'bool',
				'articleEmailLog' => 'bool',
				'publicationFormatVolume' => 'bool',
				'publicationFormatNumber' => 'bool',
				'publicationFormatYear' => 'bool',
				'publicationFormatTitle' => 'bool',
				'initialVolume' => 'int',
				'initialNumber' => 'int',
				'initialYear' => 'int',
				'pubFreqPolicy' => 'string',
				'useCopyeditors' => 'bool',
				'copyeditInstructions' => 'string',
				'useLayoutEditors' => 'bool',
				'layoutInstructions' => 'string',
				'provideRefLinkInstructions' => 'bool',
				'refLinkInstructions' => 'string',
				'useProofreaders' => 'bool',
				'proofInstructions' => 'string',
				'enableSubscriptions' => 'bool',
				'showGalleyLinks' => 'bool',
				'openAccessPolicy' => 'string',
				'enableAnnouncements' => 'bool',
				'enableAnnouncementsHomepage' => 'bool',
				'numAnnouncementsHomepage' => 'int',
				'announcementsIntroduction' => 'string',
				'volumePerYear' => 'int',
				'issuePerVolume' => 'int',
				'enablePublicIssueId' => 'bool',
				'enablePublicArticleId' => 'bool',
				'enablePublicGalleyId' => 'bool',
				'enablePublicSuppFileId' => 'bool',
				'enablePageNumber' => 'bool'
			)
		);
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('pubFreqPolicy', 'copyeditInstructions', 'layoutInstructions', 'refLinkInstructions', 'proofInstructions', 'openAccessPolicy', 'announcementsIntroduction');
	}
}

?>
