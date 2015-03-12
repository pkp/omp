<?php

/**
 * @file controllers/tab/settings/appearance/form/AppearanceForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AppearanceForm
 * @ingroup controllers_tab_settings_appearance_form
 *
 * @brief Form to edit appearance settings.
 */

import('lib.pkp.controllers.tab.settings.appearance.form.PKPAppearanceForm');

class AppearanceForm extends PKPAppearanceForm {
	/**
	 * Constructor.
	 */
	function AppearanceForm($wizardMode = false) {
		parent::PKPAppearanceForm($wizardMode, array(
			'displayNewReleases' => 'bool',
			'displayFeaturedBooks' => 'bool',
			'displayInSpotlight' => 'bool',
		));
	}
}

?>
