<?php
/**
 * @file pages/settings/SettingsHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SettingsHandler
 * @ingroup pages_settings
 *
 * @brief Handle requests for OMP settings.
 */

import('handler.Handler');

class SettingsHandler extends Handler {
    /**
     * Constructor
     */
    function SettingsHandler() {
        parent::Handler();
    }

    /**
     * Display settings index page.
     */
    function index(&$request, &$args) {
        $templateMgr =& TemplateManager::getManager();
        $this->setupTemplate();
        $templateMgr->display('settings/index.tpl');
    }

    /**
     * Display settings index page.
     */
    function data(&$request, &$args) {
        $templateMgr =& TemplateManager::getManager();
        $this->setupTemplate();
        $templateMgr->display('settings/data.tpl');
    }

    /**
     * Display settings index page.
     */
    function system(&$request, &$args) {
        $templateMgr =& TemplateManager::getManager();
        $this->setupTemplate();
        $templateMgr->display('settings/system.tpl');
    }

    /**
     * Display settings index page.
     */
    function users(&$request, &$args) {
        $templateMgr =& TemplateManager::getManager();
        $this->setupTemplate();
        $templateMgr->display('settings/userManagement.tpl');
    }

	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_SETTINGS));

		if ($subclass) {
			$templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'settings'), 'settings.pressManagement')));
		}
	}
}

?>
