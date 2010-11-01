<?php

/**
 * @file classes/manager/form/setup/PressSetupStep4Form.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSetupStep4Form
 * @ingroup manager_form_setup
 *
 * @brief Form for Step 4 of the press setup.
 */

// $Id$


import('classes.manager.form.setup.PressSetupForm');

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
				'restrictMonographAccess' => 'bool',
				'showGalleyLinks' => 'bool',
				'openAccessPolicy' => 'string',
				'enableAnnouncements' => 'bool',
				'enableAnnouncementsHomepage' => 'bool',
				'numAnnouncementsHomepage' => 'int',
				'announcementsIntroduction' => 'string',
				'volumePerYear' => 'int',
				'enablePublicMonographId' => 'bool',
				'enablePublicGalleyId' => 'bool',
				'enablePageNumber' => 'bool',
				'searchDescription' => 'string',
				'searchKeywords' => 'string',
				'customHeaders' => 'string'
			)
		);
	}

	/**
	 * Get the list of field names for which localized settings are used.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('pubFreqPolicy', 'openAccessPolicy', 'announcementsIntroduction', 'searchDescription', 'searchKeywords', 'customHeaders');
	}
}

?>
