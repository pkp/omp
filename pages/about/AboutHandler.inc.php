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


import('core.PKPHandler');

class AboutHandler extends PKPHandler {

	/**
	 * Display about index page.
	 */
	function index() {
		parent::validate();
		AboutHandler::setupTemplate();

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

			foreach (AboutHandler::getPublicStatisticsNames() as $name) {
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
			$about = $site->getSiteAbout();
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
		parent::validate();

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
		parent::validate(true);

		AboutHandler::setupTemplate(true);

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
		parent::validate(true);
		AboutHandler::setupTemplate(true);

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

			$seriesEditors =& $roleDao->getUsersByRoleId(ROLE_ID_SERIES_EDITOR, $press->getId());
			$seriesEditors =& $seriesEditors->toArray();

			$layoutEditors =& $roleDao->getUsersByRoleId(ROLE_ID_LAYOUT_EDITOR, $press->getId());
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
				$allMemberships =& $groupMembershipDao->getMemberships($group->getGroupId());
				while ($membership =& $allMemberships->next()) {
					if (!$membership->getAboutDisplayed()) continue;
					$memberships[] =& $membership;
					unset($membership);
				}
				if (!empty($memberships)) $groups[] =& $group;
				$teamInfo[$group->getGroupId()] = $memberships;
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
		parent::validate(true);
		AboutHandler::setupTemplate(true);

	}

	/**
	 * Display a biography for an editorial team member.
	 * @param $args array
	 */
	function editorialTeamBio($args) {
		parent::validate(true);
		AboutHandler::setupTemplate(true);

	}

	/**
	 * Display editorialPolicies page.
	 */
	function editorialPolicies() {
		parent::validate(true);
		AboutHandler::setupTemplate(true);


	}

	/**
	 * Display subscriptions page.
	 */
	function subscriptions() {
		parent::validate(true);
		AboutHandler::setupTemplate(true);

		$templateMgr->display('about/subscriptions.tpl');
	}

	/**
	 * Display subscriptions page.
	 */
	function memberships() {
		parent::validate(true);
		AboutHandler::setupTemplate(true);
		
	}

	/**
	 * Display submissions page.
	 */
	function submissions() {
		parent::validate(true);
		AboutHandler::setupTemplate(true);

	}

	/**
	 * Display siteMap page.
	 */
	function siteMap() {
		parent::validate();

		AboutHandler::setupTemplate(true);
		$templateMgr =& TemplateManager::getManager();

		$pressDao =& DAORegistry::getDAO('PressDAO');

		$user =& Request::getUser();
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		if ($user) {
			$rolesByPress = array();
			$presses =& $pressDao->getEnabledPresses();
			// Fetch the user's roles for each press
			foreach ($presses->toArray() as $press) {
				$roles =& $roleDao->getRolesByUserId($user->getUserId(), $press->getId());
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
			$templateMgr->assign('isSiteAdmin', $roleDao->getRole(0, $user->getUserId(), ROLE_ID_SITE_ADMIN));
		}

		$templateMgr->display('about/siteMap.tpl');
	}

	/**
	 * Display aboutThisPublishingSystem page.
	 */
	function aboutThisPublishingSystem() {
		parent::validate();

		AboutHandler::setupTemplate(true);

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
		parent::validate();
		AboutHandler::setupTemplate(true);


		$templateMgr->display('about/statistics.tpl');
	}

	function getPublicStatisticsNames() {
		import ('pages.manager.ManagerHandler');
		import ('pages.manager.StatisticsHandler');
		return StatisticsHandler::getPublicStatisticsNames();
	}

}

?>
