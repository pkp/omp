<?php

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 */



import('lib.pkp.classes.admin.form.PKPSiteSettingsForm');

class SiteSettingsForm extends PKPSiteSettingsForm {

	/**
	 * Constructor.
	 */
	function SiteSettingsForm() {
		parent::PKPSiteSettingsForm();
	}

	/**
	 * Display the form.
	 */
	function display() {
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPressNames();
		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('redirectOptions', $presses);
		return parent::display();
	}
}

?>
