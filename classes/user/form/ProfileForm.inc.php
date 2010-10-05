<?php

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 */

// $Id$


import('lib.pkp.classes.form.Form');

class ProfileForm extends Form {

	/** @var $user object */
	var $user;

	/**
	 * Constructor.
	 */
	function ProfileForm() {
		parent::Form('user/profile.tpl');

		$user =& Request::getUser();
		$this->user =& $user;

		$site =& Request::getSite();

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
		$this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array($user->getId(), true), true));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Deletes a profile image.
	 */
	function deleteProfileImage() {
		$user =& Request::getUser();
		$profileImage = $user->getSetting('profileImage');
		if (!$profileImage) return false;

		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();
		if ($fileManager->removeSiteFile($profileImage['uploadName'])) {
			return $user->updateSetting('profileImage', null);
		} else {
			return false;
		}
	}

	function uploadProfileImage() {
		import('classes.file.PublicFileManager');
		$fileManager = new PublicFileManager();

		$user =& $this->user;

		$type = $fileManager->getUploadedFileType('profileImage');
		$extension = $fileManager->getImageExtension($type);
		if (!$extension) return false;

		$uploadName = 'profileImage-' . (int) $user->getId() . $extension;
		if (!$fileManager->uploadSiteFile('profileImage', $uploadName)) return false;

		$filePath = $fileManager->getSiteFilesPath();
		list($width, $height) = getimagesize($filePath . '/' . $uploadName);

		if ($width > 150 || $height > 150 || $width <= 0 || $height <= 0) {
			$userSetting = null;
			$user->updateSetting('profileImage', $userSetting);
			$fileManager->removeSiteFile($filePath);
			return false;
		}

		$userSetting = array(
			'name' => $fileManager->getUploadedFileName('profileImage'),
			'uploadName' => $uploadName,
			'width' => $width,
			'height' => $height,
			'dateUploaded' => Core::getCurrentDate()
		);

		$user->updateSetting('profileImage', $userSetting);
		return true;
	}

	/**
	 * Display the form.
	 */
	function display() {
		$user =& Request::getUser();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('username', $user->getUsername());

		$site =& Request::getSite();
		$templateMgr->assign('availableLocales', $site->getSupportedLocaleNames());

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupAssignmentDao =& DAORegistry::getDAO('UserGroupAssignmentDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');

		$presses =& $pressDao->getPresses();
		$presses =& $presses->toArray();

		$countryDao =& DAORegistry::getDAO('CountryDAO');
		$countries =& $countryDao->getCountries();

		$templateMgr->assign('genderOptions', $userDao->getGenderOptions());

		$templateMgr->assign_by_ref('presses', $presses);
		$templateMgr->assign_by_ref('countries', $countries);
		$templateMgr->assign('helpTopicId', 'user.registerAndProfile');

 		$interestDao =& DAORegistry::getDAO('InterestDAO');
		// Get all available interests to populate the autocomplete with
		if ($interestDao->getAllUniqueInterests()) {
			$existingInterests = $interestDao->getAllUniqueInterests();
		} else $existingInterests = null;
		$templateMgr->assign('existingInterests', $existingInterests);

		$press =& Request::getPress();
		if ($press) {
			// get all this user's userGroups
			// TODO: maybe this needs to be part of the userGroupAssignmentDAO.
			$userGroupAssignments =& $userGroupAssignmentDao->getByUserId($user->getId(), $press->getId());
			$userGroupIds = array();
			foreach ($userGroupAssignments->toArray() as $assignment) {
				$userGroupIds[] = $assignment->getUserGroupId();
			}
			$templateMgr->assign('allowRegReviewer', $press->getSetting('allowRegReviewer'));
			$templateMgr->assign_by_ref('reviewerUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_REVIEWER));
			$templateMgr->assign('allowRegAuthor', $press->getSetting('allowRegAuthor'));
			$templateMgr->assign_by_ref('authorUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR));
			$templateMgr->assign('allowRegReader', $press->getSetting('allowRegReader'));
			$templateMgr->assign_by_ref('readerUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_READER));
			$templateMgr->assign('userGroupIds', $userGroupIds);

		}
		$templateMgr->assign('profileImage', $user->getSetting('profileImage'));

		parent::display();
	}

	function getLocaleFieldNames() {
		$userDao =& DAORegistry::getDAO('UserDAO');
		return $userDao->getLocaleFieldNames();
	}

	/**
	 * Initialize form data from current settings.
	 */
	function initData(&$args, &$request) {
		$user =& Request::getUser();
		$interestDao =& DAORegistry::getDAO('InterestDAO');

		// Get all available interests to populate the autocomplete with
		if ($interestDao->getAllUniqueInterests()) {
			$existingInterests = $interestDao->getAllUniqueInterests();
		} else $existingInterests = null;
		// Get the user's current set of interests
		if ($interestDao->getInterests($user->getId())) {
			$currentInterests = $interestDao->getInterests($user->getId());
		} else $currentInterests = null;

		$this->_data = array(
			'salutation' => $user->getSalutation(),
			'firstName' => $user->getFirstName(),
			'middleName' => $user->getMiddleName(),
			'initials' => $user->getInitials(),
			'lastName' => $user->getLastName(),
			'gender' => $user->getGender(),
			'affiliation' => $user->getAffiliation(null), // Localized
			'signature' => $user->getSignature(null), // Localized
			'email' => $user->getEmail(),
			'userUrl' => $user->getUrl(),
			'phone' => $user->getPhone(),
			'fax' => $user->getFax(),
			'mailingAddress' => $user->getMailingAddress(),
			'country' => $user->getCountry(),
			'biography' => $user->getBiography(null), // Localized
			'userLocales' => $user->getLocales(),
			'existingInterests' => $existingInterests,
			'interestsKeywords' => $currentInterests
		);
	}

	/**
	 * Assign form data to user-submitted data.
	 */
	function readInputData() {
		$this->readUserVars(array(
			'salutation',
			'firstName',
			'middleName',
			'lastName',
			'gender',
			'initials',
			'affiliation',
			'signature',
			'email',
			'userUrl',
			'phone',
			'fax',
			'mailingAddress',
			'country',
			'biography',
			'interestsKeywords',
			'userLocales'
		));

		if ($this->getData('userLocales') == null || !is_array($this->getData('userLocales'))) {
			$this->setData('userLocales', array());
		}
	}

	/**
	 * Save profile settings.
	 */
	function execute() {
		$user =& Request::getUser();

		$user->setSalutation($this->getData('salutation'));
		$user->setFirstName($this->getData('firstName'));
		$user->setMiddleName($this->getData('middleName'));
		$user->setLastName($this->getData('lastName'));
		$user->setGender($this->getData('gender'));
		$user->setInitials($this->getData('initials'));
		$user->setAffiliation($this->getData('affiliation'), null); // Localized
		$user->setSignature($this->getData('signature'), null); // Localized
		$user->setEmail($this->getData('email'));
		$user->setUrl($this->getData('userUrl'));
		$user->setPhone($this->getData('phone'));
		$user->setFax($this->getData('fax'));
		$user->setMailingAddress($this->getData('mailingAddress'));
		$user->setCountry($this->getData('country'));
		$user->setBiography($this->getData('biography'), null); // Localized

		// Add reviewing interests to interests table
		$interestDao =& DAORegistry::getDAO('InterestDAO');
		$interests = Request::getUserVar('interestsKeywords');
		$interestTextOnly = Request::getUserVar('interests');
		if(!empty($interestsTextOnly)) {
			// If JS is disabled, this will be the input to read
			$interestsTextOnly = explode(",", $interestTextOnly);
		} else $interestsTextOnly = null;
		if ($interestsTextOnly && !isset($interests)) {
			$interests = $interestsTextOnly;
		} elseif (isset($interests) && !is_array($interests)) {
			$interests = array($interests);
		}
		$interestDao->insertInterests($interests, $user->getId(), true);


		$site =& Request::getSite();
		$availableLocales = $site->getSupportedLocales();

		$locales = array();
		foreach ($this->getData('userLocales') as $locale) {
			if (Locale::isLocaleValid($locale) && in_array($locale, $availableLocales)) {
				array_push($locales, $locale);
			}
		}
		$user->setLocales($locales);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userDao->updateObject($user);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$notificationStatusDao =& DAORegistry::getDAO('NotificationStatusDAO');

		// Roles
		$press =& Request::getPress();
		if ($press) {
			if ($press->getSetting('allowRegReviewer')) {
				foreach ($this->getData('reviewerGroup') as $groupId => $wantsGroup ) {
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if ($inGroup && !$wantsGroup) $userGroupDao->removeUserFromGroup($user->getId(), $groupId);
					if (!$hasRole && $wantsRole) $userGroupDao->assignUserToGroup($user->getId(), $groupId);
				}
			}
			if ($press->getSetting('allowRegAuthor')) {
				foreach ($this->getData('authorGroup') as $groupId => $wantsGroup ) {
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if ($inGroup && !$wantsGroup) $userGroupDao->removeUserFromGroup($user->getId(), $groupId);
					if (!$hasRole && $wantsRole) $userGroupDao->assignUserToGroup($user->getId(), $groupId);
				}
			}
			if ($press->getSetting('allowRegReader')) {
				foreach ($this->getData('readerGroup') as $groupId => $wantsGroup ) {
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if ($inGroup && !$wantsGroup) $userGroupDao->removeUserFromGroup($user->getId(), $groupId);
					if (!$hasRole && $wantsRole) $userGroupDao->assignUserToGroup($user->getId(), $groupId);
				}
			}
		}

		$presses =& $pressDao->getPresses();
		$presses =& $presses->toArray();
		$pressNotifications = $notificationStatusDao->getPressNotifications($user->getId());

		$readerNotify = Request::getUserVar('pressNotify');

		foreach ($presses as $thisPress) {
			$thisPressId = $thisPress->getId();
			$currentlyReceives = !empty($pressNotifications[$thisPressId]);
			$shouldReceive = !empty($readerNotify) && in_array($thisPress->getId(), $readerNotify);
			if ($currentlyReceives != $shouldReceive) {
				$notificationStatusDao->setPressNotifications($thisPressId, $user->getId(), $shouldReceive);
			}
		}

		$userSettingsDao =& DAORegistry::getDAO('UserSettingsDAO');

		if ($user->getAuthId()) {
			$authDao =& DAORegistry::getDAO('AuthSourceDAO');
			$auth =& $authDao->getPlugin($user->getAuthId());
		}

		if (isset($auth)) {
			$auth->doSetUserInfo($user);
		}
	}
}

?>
