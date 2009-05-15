<?php

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for about functions. 
 */

// $Id$


import('handler.Handler');

class AboutHandler extends Handler {
	/**
	 * Constructor
	 */
	function AboutHandler() {
		parent::Handler();
	}

	/**
	 * Display about index page.
	 */
	function index() {
		$this->validate();
		$this->setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressPath = Request::getRequestedPressPath();

		if ($pressPath != 'index' && $pressDao->pressExistsByPath($pressPath)) {
			$press =& Request::getPress();

			$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
			$templateMgr->assign_by_ref('pressSettings', $pressSettingsDao->getPressSettings($press->getId()));

			$customAboutItems =& $pressSettingsDao->getSetting($press->getId(), 'customAboutItems');
			if (isset($customAboutItems[Locale::getLocale()])) $templateMgr->assign('customAboutItems', $customAboutItems[Locale::getLocale()]);
			elseif (isset($customAboutItems[Locale::getPrimaryLocale()])) $templateMgr->assign('customAboutItems', $customAboutItems[Locale::getPrimaryLocale()]);

			foreach ($this->getPublicStatisticsNames() as $name) {
				if ($press->getSetting($name)) {
					$templateMgr->assign('publicStatisticsEnabled', true);
					break;
				} 
			}

			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, GROUP_CONTEXT_PEOPLE);

			$templateMgr->assign_by_ref('peopleGroups', $groups);
			$templateMgr->assign('helpTopicId', 'user.about');
			$templateMgr->display('about/index.tpl');
		} else {
			$site =& Request::getSite();
			$about = $site->getLocalizedAbout();
			$templateMgr->assign('about', $about);

			$presses =& $pressDao->getEnabledPresses(); //Enabled Added
			$templateMgr->assign_by_ref('presses', $presses);
			$templateMgr->display('about/site.tpl');
		}
	}


	/**
	 * Setup common template variables.
	 * @param $subclass boolean set to true if caller is below this handler in the hierarchy
	 */
	function setupTemplate($subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$press =& Request::getPress();

		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
		if($subclass)$templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'about'), 'about.aboutThePress')));
	}

	/**
	 * Display contact page.
	 */
	function contact() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();

		$this->setupTemplate(true);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$monograph =& Request::getMonograph();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $pressSettingsDao->getPressSettings($monograph->getMonographId());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display editorialTeam page.
	 */
	function editorialTeam() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$press =& Request::getPress();
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		// FIXME: This is pretty inefficient; should probably be cached.
		if ($press->getSetting('boardEnabled') != true) {
			// Don't use the Editorial Team feature. Generate
			// Editorial Team information using Role info.
			$roleDao =& DAORegistry::getDAO('RoleDAO');

			$editors =& $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $press->getId());
			$editors =& $editors->toArray();

			$seriesEditors =& $roleDao->getUsersByRoleId(ROLE_ID_ACQUISITIONS_EDITOR, $press->getId());
			$seriesEditors =& $seriesEditors->toArray();

			$layoutEditors =& $roleDao->getUsersByRoleId(ROLE_ID_DESIGNER, $press->getId());
			$layoutEditors =& $layoutEditors->toArray();

			$copyEditors =& $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $press->getId());
			$copyEditors =& $copyEditors->toArray();

			$proofreaders =& $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $press->getId());
			$proofreaders =& $proofreaders->toArray();

			$templateMgr->assign_by_ref('editors', $editors);
			$templateMgr->assign_by_ref('seriesEditors', $seriesEditors);
			$templateMgr->assign_by_ref('layoutEditors', $layoutEditors);
			$templateMgr->assign_by_ref('copyEditors', $copyEditors);
			$templateMgr->assign_by_ref('proofreaders', $proofreaders);
			$templateMgr->display('about/editorialTeam.tpl');
		} else {
			// The Editorial Team feature has been enabled.
			// Generate information using Group data.
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

			$allGroups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, GROUP_CONTEXT_EDITORIAL_TEAM);
			$teamInfo = array();
			$groups = array();
			while ($group =& $allGroups->next()) {
				if (!$group->getAboutDisplayed()) continue;
				$memberships = array();
				$allMemberships =& $groupMembershipDao->getMemberships($group->getId());
				while ($membership =& $allMemberships->next()) {
					if (!$membership->getAboutDisplayed()) continue;
					$memberships[] =& $membership;
					unset($membership);
				}
				if (!empty($memberships)) $groups[] =& $group;
				$teamInfo[$group->getId()] = $memberships;
				unset($group);
			}

			$templateMgr->assign_by_ref('groups', $groups);
			$templateMgr->assign_by_ref('teamInfo', $teamInfo);
			$templateMgr->display('about/editorialTeamBoard.tpl');
		}

	}

	/**
	 * Display group info for a particular group.
	 * @param $args array
	 */
	function displayMembership($args) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

	}

	/**
	 * Display a biography for an editorial team member.
	 * @param $args array
	 */
	function editorialTeamBio($args) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

	}

	/**
	 * Display editorialPolicies page.
	 */
	function editorialPolicies() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);


	}

	/**
	 * Display subscriptions page.
	 */
	function subscriptions() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$templateMgr->display('about/subscriptions.tpl');
	}

	/**
	 * Display subscriptions page.
	 */
	function memberships() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);
		
	}

	/**
	 * Display submissions page.
	 */
	function submissions() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $settingsDao->getPressSettings($press->getId());
		$submissionChecklist = $press->getLocalizedSetting('submissionChecklist');
		if (!empty($submissionChecklist)) {
			ksort($submissionChecklist);
			reset($submissionChecklist);
		}
		$templateMgr->assign('submissionChecklist', $submissionChecklist);
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->assign('helpTopicId','submission.authorGuidelines');
		$templateMgr->display('about/submissions.tpl');
	}

	/**
	 * Display siteMap page.
	 */
	function siteMap() {
		$this->validate();
		$this->setupTemplate(true);
		
		$templateMgr =& TemplateManager::getManager();

		$pressDao =& DAORegistry::getDAO('PressDAO');

		$user =& Request::getUser();
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($user) {
			$rolesByPress = array();
			$presses =& $pressDao->getEnabledPresses();
			// Fetch the user's roles for each press
			foreach ($presses->toArray() as $press) {
				$roles =& $roleDao->getRolesByUserId($user->getId(), $press->getId());
				if (!empty($roles)) {
					$rolesByPress[$press->getId()] =& $roles;
				}
			}
		}

		$presses =& $pressDao->getEnabledPresses();
		$templateMgr->assign_by_ref('presses', $presses->toArray());
		if (isset($rolesByPress)) {
			$templateMgr->assign_by_ref('rolesByPress', $rolesByPress);
		}
		if ($user) {
			$templateMgr->assign('isSiteAdmin', $roleDao->getRole(0, $user->getId(), ROLE_ID_SITE_ADMIN));
		}

		$templateMgr->display('about/siteMap.tpl');
	}

	/**
	 * Display aboutThisPublishingSystem page.
	 */
	function aboutThisPublishingSystem() {
		$this->validate();
		$this->setupTemplate(true);

		$versionDao =& DAORegistry::getDAO('VersionDAO');
		$version =& $versionDao->getCurrentVersion();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('ompVersion', $version->getVersionString());

		foreach (array(Locale::getLocale(), $primaryLocale = Locale::getPrimaryLocale(), 'en_US') as $locale) {
			$pubProcessFile = 'locale/'.$locale.'/pubprocesslarge.png';
			if (file_exists($pubProcessFile)) break;
		}
		$templateMgr->assign('pubProcessFile', $pubProcessFile);

		$templateMgr->display('about/aboutThisPublishingSystem.tpl');
	}

	/**
	 * Display a list of public stats for the current press.
	 * WARNING: This implementation should be kept roughly synchronized
	 * with the reader's statistics view in the About pages.
	 */
	function statistics() {
		$this->validate();
		$this->setupTemplate(true);


		$templateMgr->display('about/statistics.tpl');
	}

	function getPublicStatisticsNames() {
		import ('pages.manager.ManagerHandler');
		import ('pages.manager.StatisticsHandler');
		return StatisticsHandler::getPublicStatisticsNames();
	}

}

?>
