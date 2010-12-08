<?php

/**
 * @file plugins/generic/tinymce/TinyMCEPlugin.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TinyMCEPlugin
 * @ingroup plugins_generic_tinymce
 *
 * @brief TinyMCE WYSIWYG plugin for textareas - to allow cross-browser HTML editing
 */



import('lib.pkp.classes.plugins.GenericPlugin');

define('TINYMCE_INSTALL_PATH', 'lib' . DIRECTORY_SEPARATOR . 'pkp' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'tinymce');
define('TINYMCE_JS_PATH', TINYMCE_INSTALL_PATH . DIRECTORY_SEPARATOR . 'jscripts' . DIRECTORY_SEPARATOR . 'tiny_mce');

class TinyMCEPlugin extends GenericPlugin {
	/**
	 * Register the plugin, if enabled; note that this plugin
	 * runs under both Press and Site contexts.
	 * @param $category string
	 * @param $path string
	 * @return boolean
	 */
	function register($category, $path) {
		if (parent::register($category, $path)) {
			if ($this->isMCEInstalled() && $this->getEnabled()) {
				HookRegistry::register('TemplateManager::display',array(&$this, 'callback'));
			}
			return true;
		}
		return false;
	}

	/**
	 * Get the name of the settings file to be installed on new press
	 * creation.
	 * @return string
	 */
	function getContextSpecificPluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Get the name of the settings file to be installed site-wide when
	 * OMP is installed.
	 * @return string
	 */
	function getInstallSitePluginSettingsFile() {
		return $this->getPluginPath() . '/settings.xml';
	}

	/**
	 * Given a $page and $op, return a list of field names for which
	 * the plugin should be used.
	 * @param $templateMgr object
	 * @param $page string The requested page
	 * @param $op string The requested operation
	 * @return array
	 */
	function getEnableFields(&$templateMgr, $page, $op) {
		$formLocale = $templateMgr->get_template_vars('formLocale');
		$fields = array();
		switch ("$page/$op") {
			case 'admin/settings':
			case 'admin/saveSettings':
				$fields[] = 'intro';
				$fields[] = 'aboutField';
				break;
			case 'admin/createPress':
			case 'admin/updatePress':
			case 'admin/editPress':
				$fields[] = 'description';
				break;
			case 'author/submit':
			case 'author/saveSubmit':
				switch (array_shift(Request::getRequestedArgs())) {
					case '':
					case 1: $fields[] = 'commentsToEditor'; break;
					case 3:
						$count = max(1, count($templateMgr->get_template_vars('authors')));
						for ($i=0; $i<$count; $i++) {
							$fields[] = "authors-$i-biography";
							$fields[] = "authors-$i-competingInterests";
						}
						$fields[] = 'abstract';
						break;
				}
				break;
			case 'author/viewCopyeditComments':
			case 'author/postCopyeditComment':
			case 'author/viewLayoutComments':
			case 'author/postLayoutComment':
			case 'author/viewProofreadComments':
			case 'author/postProofreadComment':
			case 'author/editComment':
			case 'author/saveComment':
			case 'editor/viewEditorDecisionComments':
			case 'editor/postEditorDecisionComment':
			case 'editor/viewCopyeditComments':
			case 'editor/postCopyeditComment':
			case 'editor/viewLayoutComments':
			case 'editor/postLayoutComment':
			case 'editor/viewProofreadComments':
			case 'editor/postProofreadComment':
			case 'editor/editComment':
			case 'editor/saveComment':
			case 'sectionEditor/viewEditorDecisionComments':
			case 'sectionEditor/postEditorDecisionComment':
			case 'sectionEditor/viewCopyeditComments':
			case 'sectionEditor/postCopyeditComment':
			case 'sectionEditor/viewLayoutComments':
			case 'sectionEditor/postLayoutComment':
			case 'sectionEditor/viewProofreadComments':
			case 'sectionEditor/postProofreadComment':
			case 'sectionEditor/editComment':
			case 'sectionEditor/saveComment':
			case 'copyeditor/viewCopyeditComments':
			case 'copyeditor/postCopyeditComment':
			case 'copyeditor/viewLayoutComments':
			case 'copyeditor/postLayoutComment':
			case 'copyeditor/editComment':
			case 'copyeditor/saveComment':
			case 'proofreader/viewLayoutComments':
			case 'proofreader/postLayoutComment':
			case 'proofreader/viewProofreadComments':
			case 'proofreader/postProofreadComment':
			case 'proofreader/editComment':
			case 'proofreader/saveComment':
			case 'layoutEditor/viewLayoutComments':
			case 'layoutEditor/postLayoutComment':
			case 'layoutEditor/viewProofreadComments':
			case 'layoutEditor/postProofreadComment':
			case 'layoutEditor/editComment':
			case 'layoutEditor/saveComment':
				$fields[] = 'comments';
				break;
			case 'manager/createAnnouncement':
			case 'manager/editAnnouncement':
			case 'manager/updateAnnouncement':
				$fields[] = 'descriptionShort';
				$fields[] = 'description';
				break;
			case 'manager/importexport':
				$count = max(1, count($templateMgr->get_template_vars('authors')));
				for ($i=0; $i<$count; $i++) {
					$fields[] = "authors-$i-biography";
					$fields[] = "authors-$i-competingInterests";
				}
				$fields[] = 'abstract';
				break;
			case 'user/profile':
			case 'user/register':
			case 'user/saveProfile':
			case 'subscriptionManager/createUser':
			case 'subscriptionManager/updateUser':
			case 'manager/createUser':
			case 'manager/updateUser':
				$fields[] = 'mailingAddress';
				$fields[] = 'biography';
				break;
			case 'manager/setup':
			case 'manager/saveSetup':
				switch (array_shift(Request::getRequestedArgs())) {
					case 1:
						$fields[] = 'mailingAddress';
						$fields[] = 'contactMailingAddress';
						$fields[] = 'sponsorNote';
						$fields[] = 'contributorNote';
						break;
					case 2:
						$fields[] = 'focusScopeDesc';
						$fields[] = 'reviewGuidelines';
						$fields[] = 'privacyStatement';
						break;
					case 4:
						$fields[] = 'openAccessPolicy';
						$fields[] = 'announcementsIntroduction';
						break;
					case 5:
						$fields[] = 'description';
						$fields[] = 'additionalHomeContent';
						$fields[] = 'readerInformation';
						$fields[] = 'librarianInformation';
						$fields[] = 'authorInformation';
						$fields[] = 'pressPageHeader';
						$fields[] = 'pressPageFooter';
						break;
				}
				break;
			case 'reviewer/submission': 
				$fields[] = 'competingInterestsText'; 
				$fields[] = 'comments';
				break;
	
		}
		HookRegistry::call('TinyMCEPlugin::getEnableFields', array(&$this, &$fields));
		return $fields;
	}

	/**
	 * Hook callback function for TemplateManager::display
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callback($hookName, $args) {
		// Only pages requests interest us here
		$request =& Registry::get('request');
		if (!is_a($request->getRouter(), 'PKPPageRouter')) return null;

		$templateManager =& $args[0];

		$page = Request::getRequestedPage();
		$op = Request::getRequestedOp();
		$enableFields = $this->getEnableFields($templateManager, $page, $op);

		if (!empty($enableFields)) {
			$baseUrl = $templateManager->get_template_vars('baseUrl');
			$additionalHeadData = $templateManager->get_template_vars('additionalHeadData');
			$enableFields = join(',', $enableFields);
			$allLocales = Locale::getAllLocales();
			$localeList = array();
			foreach ($allLocales as $key => $locale) {
				$localeList[] = String::substr($key, 0, 2);
			}

			$tinymceScript = '
			<script language="javascript" type="text/javascript" src="'.$baseUrl.'/'.TINYMCE_JS_PATH.'/tiny_mce_gzip.js"></script>
			<script language="javascript" type="text/javascript">
				<!--
				tinyMCE_GZ.init({
					relative_urls : "false",
					plugins : "paste,ibrowser,fullscreen",
					themes : "advanced",
					languages : "' . join(',', $localeList) . '",
					disk_cache : true
				});
				// -->
			</script>
			<script language="javascript" type="text/javascript">
				<!--
				tinyMCE.init({
					entity_encoding : "raw",
					plugins : "paste,ibrowser,fullscreen",
					mode : "exact",
					language : "' . String::substr(Locale::getLocale(), 0, 2) . '",
					elements : "' . $enableFields . '",
					relative_urls : false,
					forced_root_block : false,
					paste_auto_cleanup_on_paste : true,
					apply_source_formatting : false,
					theme : "advanced",
					theme_advanced_buttons1 : "cut,copy,paste,|,bold,italic,underline,bullist,numlist,|,link,unlink,help,code,fullscreen,ibrowser",
					theme_advanced_buttons2 : "",
					theme_advanced_buttons3 : ""
				});
				// -->
			</script>';

			$templateManager->assign('additionalHeadData', $additionalHeadData."\n".$tinymceScript);
		} 
		return false;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return Locale::translate('plugins.generic.tinymce.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		if ($this->isMCEInstalled()) return Locale::translate('plugins.generic.tinymce.description');
		return Locale::translate('plugins.generic.tinymce.descriptionDisabled', array('tinyMcePath' => TINYMCE_INSTALL_PATH));
	}

	/**
	 * Check whether or not the TinyMCE library is installed
	 * @return boolean
	 */
	function isMCEInstalled() {
		return file_exists(TINYMCE_JS_PATH . '/tiny_mce.js');
	}

	/**
	 * Get a list of available management verbs for this plugin
	 * @return array
	 */
	function getManagementVerbs() {
		$verbs = array();
		if ($this->isMCEInstalled()) $verbs = parent::getManagementVerbs();
		return $verbs;
	}
}
?>
