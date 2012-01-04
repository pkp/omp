<?php

/**
 * @file classes/user/form/ProfileForm.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileForm
 * @ingroup user_form
 *
 * @brief Form to edit user profile.
 */



import('lib.pkp.classes.form.Form');

class ProfileForm extends Form {

	/** @var $user object */
	var $_user;

	/**
	 * Constructor.
	 */
	function ProfileForm($user) {
		parent::Form('user/profile.tpl');

		$this->_user =& $user;

		// Validation checks for this form
		$this->addCheck(new FormValidator($this, 'firstName', 'required', 'user.profile.form.firstNameRequired'));
		$this->addCheck(new FormValidator($this, 'lastName', 'required', 'user.profile.form.lastNameRequired'));
		$this->addCheck(new FormValidatorUrl($this, 'userUrl', 'optional', 'user.profile.form.urlInvalid'));
		$this->addCheck(new FormValidatorEmail($this, 'email', 'required', 'user.profile.form.emailRequired'));
		$this->addCheck(new FormValidatorCustom($this, 'email', 'required', 'user.register.form.emailExists', array(DAORegistry::getDAO('UserDAO'), 'userExistsByEmail'), array($user->getId(), true), true));
		$this->addCheck(new FormValidator($this, 'country', 'required', 'user.profile.form.countryRequired'));
		$this->addCheck(new FormValidatorPost($this));
	}

	/**
	 * Get the user associated with this profile
	 */
	function getUser() {
		return $this->_user;
	}

	/**
	 * Deletes a profile image.
	 */
	function deleteProfileImage() {
		$user =& $this->getUser();
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

		$user =& $this->getUser();

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
	function display($args, &$request) {
		$user =& $this->getUser();

		$templateMgr =& TemplateManager::getManager();
		$templateMgr->assign('username', $user->getUsername());

		$site =& $request->getSite();
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

		$press =& $request->getPress();
		if ($press) {
			$userGroupAssignments =& $userGroupAssignmentDao->getByUserId($user->getId(), $press->getId());
			$userGroupIds = array();
			while ($assignment =& $userGroupAssignments->next()) {
				$userGroupIds[] = $assignment->getUserGroupId();
				unset($assignment);
			}
			$templateMgr->assign('allowRegReviewer', $press->getSetting('allowRegReviewer'));
			$templateMgr->assign_by_ref('reviewerUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_REVIEWER));
			$templateMgr->assign('allowRegAuthor', $press->getSetting('allowRegAuthor'));
			$templateMgr->assign_by_ref('authorUserGroups', $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR));
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
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData(&$args, &$request) {
		$user =& $this->getUser();

		import('lib.pkp.classes.user.InterestManager');
		$interestManager = new InterestManager();

		$this->_data = array(
			'salutation' => $user->getSalutation(),
			'firstName' => $user->getFirstName(),
			'middleName' => $user->getMiddleName(),
			'initials' => $user->getInitials(),
			'lastName' => $user->getLastName(),
			'suffix' => $user->getSuffix(),
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
			'interestsKeywords' => $interestManager->getInterestsForUser($user),
			'interestsTextOnly' => $interestManager->getInterestsString($user)
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
			'suffix',
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
			'reviewerGroup',
			'authorGroup',
			'keywords',
			'interestsTextOnly',
			'userLocales'
		));

		if ($this->getData('userLocales') == null || !is_array($this->getData('userLocales'))) {
			$this->setData('userLocales', array());
		}

		$keywords = $this->getData('keywords');
		if ($keywords != null && is_array($keywords['interests'])) {
			// The interests are coming in encoded -- Decode them for DB storage
			$this->setData('interestsKeywords', array_map('urldecode', $keywords['interests']));
		}
	}

	/**
	 * Save profile settings.
	 */
	function execute($request) {
		$user =& $request->getUser();

		$user->setSalutation($this->getData('salutation'));
		$user->setFirstName($this->getData('firstName'));
		$user->setMiddleName($this->getData('middleName'));
		$user->setLastName($this->getData('lastName'));
		$user->setSuffix($this->getData('suffix'));
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

		// Insert the user interests
		$interests = $this->getData('interestsKeywords') ? $this->getData('interestsKeywords') : $this->getData('interestsTextOnly');
		import('lib.pkp.classes.user.InterestManager');
		$interestManager = new InterestManager();
		$interestManager->setInterestsForUser($user, $interests);

		$site =& $request->getSite();
		$availableLocales = $site->getSupportedLocales();

		$locales = array();
		foreach ($this->getData('userLocales') as $locale) {
			if (AppLocale::isLocaleValid($locale) && in_array($locale, $availableLocales)) {
				array_push($locales, $locale);
			}
		}
		$user->setLocales($locales);

		$userDao =& DAORegistry::getDAO('UserDAO');
		$userDao->updateObject($user);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$notificationStatusDao =& DAORegistry::getDAO('NotificationStatusDAO');

		// User Groups
		$press =& $request->getPress();
		if ($press) {
			if ($press->getSetting('allowRegReviewer')) {
				$reviewerGroup = $this->getData('reviewerGroup');
				if (!$reviewerGroup) $reviewerGroup = array();
				$reviewerUserGroups = $userGroupDao->getByRoleId($press->getId(), ROLE_ID_REVIEWER);
				while ($reviewerUserGroup =& $reviewerUserGroups->next()) {
					$groupId = $reviewerUserGroup->getId();
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if(!$inGroup && array_key_exists($groupId, $reviewerGroup)) {
						$userGroupDao->assignUserToGroup($user->getId(), $groupId, $press->getId());
					} else if($inGroup && !array_key_exists($groupId, $reviewerGroup)) {
						$userGroupDao->removeUserFromGroup($user->getId(), $groupId, $press->getId());
					}
					unset($reviewerUserGroup);
				}
			}

			if ($press->getSetting('allowRegAuthor')) {
				$authorGroup = $this->getData('authorGroup');
				if (!$authorGroup) $authorGroup = array();
				$authorUserGroups = $userGroupDao->getByRoleId($press->getId(), ROLE_ID_AUTHOR);
				while ($authorUserGroup =& $authorUserGroups->next()) {
					$groupId = $authorUserGroup->getId();
					$inGroup = $userGroupDao->userInGroup($user->getId(), $groupId);
					if(!$inGroup && array_key_exists($groupId, $authorGroup)) {
						$userGroupDao->assignUserToGroup($user->getId(), $groupId, $press->getId());
					} else if($inGroup && !array_key_exists($groupId, $authorGroup)) {
						$userGroupDao->removeUserFromGroup($user->getId(), $groupId, $press->getId());
					}
					unset($authorUserGroup);
				}
			}
		}

		$presses =& $pressDao->getPresses();
		$presses =& $presses->toArray();
		$pressNotifications = $notificationStatusDao->getPressNotifications($user->getId());

		$readerNotify = $request->getUserVar('pressNotify');

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
