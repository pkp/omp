<?php

/**
 * @file plugins/generic/tinymce/TinyMCEPlugin.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class TinyMCEPlugin
 * @ingroup plugins_generic_tinymce
 *
 * @brief TinyMCE WYSIWYG plugin for textareas - to allow cross-browser HTML editing
 */

import('lib.pkp.classes.plugins.GenericPlugin');

define('TINYMCE_INSTALL_PATH', 'lib/pkp/lib/vendor/tinymce/tinymce');
define('TINYMCE_JS_PATH', TINYMCE_INSTALL_PATH);

define('ENVIRONMENT', dirname(__FILE__) . '/config'); // For jbimages configuration

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
			if ($this->getEnabled()) {
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
	 * Hook callback function for TemplateManager::display
	 * @param $hookName string
	 * @param $args array
	 * @return boolean
	 */
	function callback($hookName, $args) {
		$request =& Registry::get('request');
		$templateManager =& $args[0];

		$baseUrl = $templateManager->get_template_vars('baseUrl');
		$additionalHeadData = $templateManager->get_template_vars('additionalHeadData');
		$allLocales = AppLocale::getAllLocales();
		$localeList = array();
		foreach ($allLocales as $key => $locale) {
			$localeList[] = String::substr($key, 0, 2);
		}

		$useMinifiedJs = $templateManager->get_template_vars('useMinifiedJavaScript');
		$tinymceScript = '
		<script type="text/javascript" src="'.$baseUrl.'/'.TINYMCE_JS_PATH.'/'.($useMinifiedJs?'tinymce.min.js':'tinymce.js').'"></script>
		<script type="text/javascript">
			tinymce.PluginManager.load(\'jbimages\', \'' . $baseUrl . '/plugins/generic/tinymce/plugins/justboil.me/'.($useMinifiedJs?'plugin.min.js':'plugin.js').'\');
			tinymce.init({
				width: "100%",
				entity_encoding: "raw",
				plugins: "paste,fullscreen,link,code,-jbimages",
				language: "' . String::substr(AppLocale::getLocale(), 0, 2) . '",
				relative_urls: false,
				forced_root_block: "p",
				paste_auto_cleanup_on_paste: true,
				apply_source_formatting: false,
				theme : "modern",
				menubar: false,
				statusbar: false,
				toolbar: "cut copy paste | bold italic underline bullist numlist | link unlink code fullscreen | jbimages",
				init_instance_callback: $.pkp.controllers.SiteHandler.prototype.triggerTinyMCEInitialized,
				setup: $.pkp.controllers.SiteHandler.prototype.triggerTinyMCESetup
			});
		</script>';

		$templateManager->assign('additionalHeadData', $additionalHeadData."\n".$tinymceScript);
		return false;
	}

	/**
	 * Get the display name of this plugin
	 * @return string
	 */
	function getDisplayName() {
		return __('plugins.generic.tinymce.name');
	}

	/**
	 * Get the description of this plugin
	 * @return string
	 */
	function getDescription() {
		return __('plugins.generic.tinymce.description');
	}
}

?>
