<?php

/**
 * @file pages/about/AboutHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AboutHandler
 * @ingroup pages_about
 *
 * @brief Handle requests for about functions.
 */



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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function index($args, &$request) {
		$this->validate();
		$this->setupTemplate($request);

		$templateMgr =& TemplateManager::getManager();
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$pressPath = $request->getRequestedPressPath();

		if ($pressPath != 'index' && $pressDao->pressExistsByPath($pressPath)) {
			$press =& $request->getPress();

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
			$site =& $request->getSite();
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
	function setupTemplate($request, $subclass = false) {
		parent::setupTemplate();

		$templateMgr =& TemplateManager::getManager();
		$press =& $request->getPress();
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_MANAGER, LOCALE_COMPONENT_PKP_MANAGER));

		if (!$press || !$press->getSetting('restrictSiteAccess')) {
			$templateMgr->setCacheability(CACHEABILITY_PUBLIC);
		}
		if ($subclass) $templateMgr->assign('pageHierarchy', array(array($request->url(null, 'about'), 'about.aboutThePress')));
	}

	/**
	 * Display contact page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function contact($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request, true);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$pressSettings =& $pressSettingsDao->getPressSettings($press->getId());
		$templateMgr->assign_by_ref('pressSettings', $pressSettings);
		$templateMgr->display('about/contact.tpl');
	}

	/**
	 * Display Press Sponsorship page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function pressSponsorship($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign_by_ref('contributorNote', $press->getLocalizedSetting('contributorNote'));
		$templateMgr->assign_by_ref('contributors', $press->getSetting('contributors'));
		$templateMgr->assign('sponsorNote', $press->getLocalizedSetting('sponsorNote'));
		$templateMgr->assign_by_ref('sponsors', $press->getSetting('sponsors'));
		$templateMgr->display('about/pressSponsorship.tpl');
	}

	/**
	 * Display editorialTeam page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialTeam($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request, true);

		$press =& $request->getPress();
		$templateMgr =& TemplateManager::getManager();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();
		$templateMgr->assign_by_ref('countries', $countries);

		// FIXME: This is pretty inefficient; should probably be cached.
		if ($press->getSetting('boardEnabled') != true) {
			// Don't use the Editorial Team feature. Generate
			// Editorial Team information using Role info.
			$roleDao =& DAORegistry::getDAO('RoleDAO');

			$seriesEditors =& $roleDao->getUsersByRoleId(ROLE_ID_SERIES_EDITOR, $press->getId());
			$seriesEditors =& $seriesEditors->toArray();

			$templateMgr->assign_by_ref('seriesEditors', $seriesEditors);
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
	 * @param $request PKPRequest
	 */
	function editorialTeamBio($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request, true);

		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$press =& $request->getPress();

		$templateMgr =& TemplateManager::getManager();

		$userId = isset($args[0])?(int)$args[0]:0;

		// Make sure we're fetching a biography for
		// a user who should appear on the listing;
		// otherwise we'll be exposing user information
		// that might not necessarily be public.

		// FIXME: This is pretty inefficient. Should be cached.

		$user = null;
		if ($press->getSetting('boardEnabled') != true) {
			$roles =& $roleDao->getRolesByUserId($userId, $press->getId());
			$acceptableRoles = array(
				ROLE_ID_SERIES_EDITOR
			);
			foreach ($roles as $role) {
				$roleId = $role->getRoleId();
				if (in_array($roleId, $acceptableRoles)) {
					$userDao =& DAORegistry::getDAO('UserDAO');
					$user =& $userDao->getUser($userId);
					break;
				}
			}

			// Currently we always publish emails in this mode.
			$publishEmail = true;
		} else {
			$groupDao =& DAORegistry::getDAO('GroupDAO');
			$groupMembershipDao =& DAORegistry::getDAO('GroupMembershipDAO');

			$allGroups =& $groupDao->getGroups(ASSOC_TYPE_PRESS, $press->getId());
			$publishEmail = false;
			while ($group =& $allGroups->next()) {
				if (!$group->getAboutDisplayed()) continue;
				$allMemberships =& $groupMembershipDao->getMemberships($group->getId());
				while ($membership =& $allMemberships->next()) {
					if (!$membership->getAboutDisplayed()) continue;
					$potentialUser =& $membership->getUser();
					if ($potentialUser->getId() == $userId) {
						if ($group->getPublishEmail()) $publishEmail = true;
						$user = $potentialUser;
					}
					unset($membership);
				}
				unset($group);
			}
		}

		if (!$user) $request->redirect(null, 'about', 'editorialTeam');

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		if ($user && $user->getCountry() != '') {
			$country = $countryDao->getCountry($user->getCountry());
			$templateMgr->assign('country', $country);
		}

		$templateMgr->assign_by_ref('user', $user);
		$templateMgr->assign_by_ref('publishEmail', $publishEmail);
		$templateMgr->display('about/editorialTeamBio.tpl');

	}

	/**
	 * Display editorialPolicies page.
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function editorialPolicies($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request, true);

		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesEditorsDao =& DAORegistry::getDAO('SeriesEditorsDAO');
		$press =& $request->getPress();

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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function submissions($args, &$request) {
		$this->addCheck(new HandlerValidatorPress($this));
		$this->validate();
		$this->setupTemplate($request, true);

		$settingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$press =& $request->getPress();

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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function siteMap($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

		$templateMgr =& TemplateManager::getManager();

		$pressDao =& DAORegistry::getDAO('PressDAO');

		$user =& $request->getUser();
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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function aboutThisPublishingSystem($args, &$request) {
		$this->validate();
		$this->setupTemplate($request, true);

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
