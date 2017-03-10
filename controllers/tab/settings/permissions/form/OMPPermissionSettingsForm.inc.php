<?php

/**
 * @file controllers/tab/settings/permissions/form/OMPPermissionSettingsForm.inc.php
 *
 * Copyright (c) 2016-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class OMPPermissionSettingsForm
 * @ingroup controllers_tab_settings_indexing_form
 *
 * @brief Form to edit content permission settings. (Extends the pkp-lib form.)
 */

import('lib.pkp.controllers.tab.settings.permissions.form.PermissionSettingsForm');

class OMPPermissionSettingsForm extends PermissionSettingsForm {

	/**
	 * Constructor.
	 */
	function __construct($wizardMode = false) {
		parent::__construct(
			array(),
			$wizardMode
		);
	}
}

?>
