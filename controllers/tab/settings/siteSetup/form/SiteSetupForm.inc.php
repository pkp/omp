<?php

/**
 * @file classes/admin/form/SiteSettingsForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SiteSettingsForm
 * @ingroup admin_form
 * @see PKPSiteSettingsForm
 *
 * @brief Form to edit site settings.
 */


import('lib.pkp.classes.admin.form.PKPSiteSettingsForm');

class SiteSetupForm extends PKPSiteSettingsForm {

	/**
	 * Constructor.
	 */
	function SiteSetupForm() {
		parent::PKPSiteSettingsForm();
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch(&$request, $params = null) {
		$site =& $request->getSite();
		$publicFileManager = new PublicFileManager();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPressNames();
		$siteStyleFilename = $publicFileManager->getSiteFilesPath() . '/' . $site->getSiteStyleFilename();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('redirectOptions', $presses);
		$templateMgr->assign('originalStyleFilename', $site->getOriginalStyleFilename());
		$templateMgr->assign('pageHeaderTitleImage', $site->getSetting('pageHeaderTitleImage'));
		$templateMgr->assign('styleFilename', $site->getSiteStyleFilename());
		$templateMgr->assign('publicFilesDir', $request->getBasePath() . '/' . $publicFileManager->getSiteFilesPath());
		$templateMgr->assign('dateStyleFileUploaded', file_exists($siteStyleFilename)?filemtime($siteStyleFilename):null);
		$templateMgr->assign('siteStyleFileExists', file_exists($siteStyleFilename));
		$templateMgr->assign('helpTopicId', 'site.siteManagement');

		return parent::fetch(&$request);
	}
}

?>
