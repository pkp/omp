<?php

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for about functions.
 */

// $Id$


import('classes.handler.Handler');

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

			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, GROUP_CONTEXT_PEOPLE);

			$seriesDao =& DAORegistry::getDAO('SeriesDAO');
			$series =& $seriesDao->getByPressId($press->getId());

			$templateMgr->assign('seriesCount', $series->GetCount());
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
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER));

		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
		if ($subclass) $templateMgr->assign('pageHierarchy', array(array(Request::url(null, 'about'), 'about.aboutThePress')));
	}

	/**
	 * Display contact page.
	 */
	function contact() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $pressSettingsDao->getPressSettings($press->getId());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display Press Sponsorship page.
	 */
	function pressSponsorship() {
		$this->validate();
		$this->setupTemplate(true);

		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('contributorNote', $press->getLocalizedSetting('contributorNote'));
		$templateMgr->assign_by_ref('contributors', $press->getSetting('contributors'));
		$templateMgr->assign('sponsorNote', $press->getLocalizedSetting('sponsorNote'));
		$templateMgr->assign_by_ref('sponsors', $press->getSetting('sponsors'));
		$templateMgr->display('about/pressSponsorship.tpl');
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

			$seriesEditors =& $roleDao->getUsersByRoleId(ROLE_ID_SERIES_EDITOR, $press->getId());
			$seriesEditors =& $seriesEditors->toArray();

			$productionEditors =& $roleDao->getUsersByRoleId(ROLE_ID_PRODUCTION_EDITOR, $press->getId());
			$productionEditors =& $productionEditors->toArray();

			$copyEditors =& $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $press->getId());
			$copyEditors =& $copyEditors->toArray();

			$proofreaders =& $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $press->getId());
			$proofreaders =& $proofreaders->toArray();

			$templateMgr->assign_by_ref('editors', $editors);
			$templateMgr->assign_by_ref('seriesEditors', $seriesEditors);
			$templateMgr->assign_by_ref('productionEditors', $productionEditors);
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
	 * Display a biography for an editorial team member.
	 * @param $args array
	 */
	function editorialTeamBio($args) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();

		$userId = isset($args[0])?(int)$args[0]:0;

		// Make sure we're fetching a biography for
		// a user who should appear on the listing;
		// otherwise we'll be exposing user information
		// that might not necessarily be public.

		// FIXME: This is pretty inefficient. Should be cached.

		$user = null;
		if ($press->getSetting('boardEnabled') != true) {
			$editors =& $roleDao->getUsersByRoleId(ROLE_ID_EDITOR, $press->getId());
			while ($potentialUser =& $editors->next()) {
				if ($potentialUser->getId() == $userId)
					$user =& $potentialUser;
				unset($potentialUser);
			}

			$seriesEditors =& $roleDao->getUsersByRoleId(ROLE_ID_SERIES_EDITOR, $press->getId());
			while ($potentialUser =& $seriesEditors->next()) {
				if ($potentialUser->getId() == $userId)
					$user =& $potentialUser;
				unset($potentialUser);
			}

			$productionEditors =& $roleDao->getUsersByRoleId(ROLE_ID_PRODUCTION_EDITOR, $press->getId());
			while ($potentialUser =& $productionEditors->next()) {
				if ($potentialUser->getId() == $userId)
					$user = $potentialUser;
				unset($potentialUser);
			}

			$copyEditors =& $roleDao->getUsersByRoleId(ROLE_ID_COPYEDITOR, $press->getId());
			while ($potentialUser =& $copyEditors->next()) {
				if ($potentialUser->getId() == $userId)
					$user = $potentialUser;
				unset($potentialUser);
			}

			$proofreaders =& $roleDao->getUsersByRoleId(ROLE_ID_PROOFREADER, $press->getId());
			while ($potentialUser =& $proofreaders->next()) {
				if ($potentialUser->getId() == $userId)
					$user = $potentialUser;
				unset($potentialUser);
			}

		} else {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

			$allGroups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, $press->getId());
			while ($group =& $allGroups->next()) {
				if (!$group->getAboutDisplayed()) continue;
				$allMemberships =& $groupMembershipDao->getMemberships($group->getId());
				while ($membership =& $allMemberships->next()) {
					if (!$membership->getAboutDisplayed()) continue;
					$potentialUser =& $membership->getUser();
					if ($potentialUser->getId() == $userId)
						$user = $potentialUser;
					unset($membership);
				}
				unset($group);
			}
		}

		if (!$user) Request::redirect(null, 'about', 'editorialTeam');

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		if ($user && $user->getCountry() != '') {
			$country = $countryDao->getCountry($user->getCountry());
			$templateMgr->assign('country', $country);
		}

		$templateMgr->assign_by_ref('user', $user);
		$templateMgr->display('about/editorialTeamBio.tpl');

	}

	/**
	 * Display editorialPolicies page.
	 */
	function editorialPolicies() {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate(true);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$press =& Request::getPress();

		$templateMgr =& TemplateManager::getManager();
		$series =& $seriesDao->getByPressId($press->getId());
		$series =& $series->toArray();
		$templateMgr->assign_by_ref('series', $series);

		$seriesEditorEntriesBySeries = array();
		foreach ($series as $series) {
			$seriesEditorEntriesBySeries[$series->getId()] =& $seriesEditorsDao->getEditorsBySeriesId($series->getId(), $press->getId());
		}
		$templateMgr->assign_by_ref('seriesEditorEntriesBySeries', $seriesEditorEntriesBySeries);

		$templateMgr->display('about/editorialPolicies.tpl');
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
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		if ($user) {
			$userGroupsByPress = array();
			$presses =& $pressDao->getEnabledPresses();
			// Fetch the user's roles for each press
			foreach ($presses->toArray() as $press) {
				$userGroups =& $userGroupDao->getByUserId($user->getId(), $press->getId());
				if (!empty($userGroups)) {
					$userGroupsByPress[$press->getId()] =& $userGroups;
				}
			}
		}

		$presses =& $pressDao->getEnabledPresses();
		$templateMgr->assign_by_ref('presses', $presses->toArray());
		if (isset($rolesByPress)) {
			$templateMgr->assign_by_ref('userGroupsByPress', $userGroupsByPress);
		}
		if ($user) {
			if (Validation::isSiteAdmin()) {
				$adminRole = new Role(ROLE_ID_SITE_ADMIN);
				$templateMgr->assign('isSiteAdmin', $adminRole);
			}
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
}

?>
