<?php 
/**
 * @file pages/dashboard/DashboardHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class DashboardHandler
 * @ingroup pages_dashboard
 *
 * @brief Handle requests for user's dashboard.
 */
 
import('handler.Handler');

class DashboardHandler extends Handler {
    /**
     * Constructor
     */
    function DashboardHandler() {
        parent::Handler();
    }
    
    /**
     * Display about index page.
     */
    function index(&$request, $args) {
        $templateMgr = &TemplateManager::getManager();
        $this->setupTemplate();
        $templateMgr->display('dashboard/index.tpl');
    }
	
	
	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER));

		if ($subclass) $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'dashboard'), 'dashboard.dashboard')));
	}
}

?>
