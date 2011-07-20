<?php

/**
 * @file ProfileHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProfileHandler
 * @ingroup pages_user
 *
 * @brief Handle requests for modifying user profiles.
 */


import('pages.user.UserHandler');

class ProfileHandler extends UserHandler {
	/**
	 * Constructor
	 */
	function ProfileHandler() {
		parent::UserHandler();

		$this->addRoleAssignment(array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_AUTHOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
				array('profile', 'saveProfile', 'changePassword', 'savePassword'));
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.PKPSiteAccessPolicy');
		$this->addPolicy(new PKPSiteAccessPolicy($request, null, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/**
	 * Display form to edit user's profile.
	 */
	function profile($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		import('classes.user.form.ProfileForm');
		$profileForm = new ProfileForm($user);
		if ($profileForm->isLocaleResubmit()) {
			$profileForm->readInputData();
		} else {
			$profileForm->initData($args, $request);
		}
		$profileForm->display($args, $request);
	}

	/**
	 * Validate and save changes to user's profile.
	 */
	function saveProfile($args, &$request) {
		$this->setupTemplate();
		$dataModified = false;
		$user =& $request->getUser();

		import('classes.user.form.ProfileForm');
		$profileForm = new ProfileForm($user);
		$profileForm->readInputData();

		if ($request->getUserVar('uploadProfileImage')) {
			if (!$profileForm->uploadProfileImage()) {
				$profileForm->addError('profileImage', Locale::translate('user.profile.form.profileImageInvalid'));
			}
			$dataModified = true;
		} else if ($request->getUserVar('deleteProfileImage')) {
			$profileForm->deleteProfileImage();
			$dataModified = true;
		}

		if (!$dataModified && $profileForm->validate()) {
			$profileForm->execute($request);
			$request->redirect(null, $request->getRequestedPage());
		} else {
			$profileForm->display($args, $request);
		}
	}

	/**
	 * Display form to change user's password.
	 */
	function changePassword($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		$site =& $request->getSite();

		import('classes.user.form.ChangePasswordForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$passwordForm = new ChangePasswordForm($user, $site);
		} else {
			$passwordForm =& new ChangePasswordForm($user, $site);
		}
		$passwordForm->initData($args, $request);
		$passwordForm->display($args, $request);
	}

	/**
	 * Save user's new password.
	 */
	function savePassword($args, &$request) {
		$this->setupTemplate(true);

		$user =& $request->getUser();
		$site =& $request->getSite();

		import('classes.user.form.ChangePasswordForm');
		if (checkPhpVersion('5.0.0')) { // WARNING: This form needs $this in constructor
			$passwordForm = new ChangePasswordForm($user, $site);
		} else {
			$passwordForm =& new ChangePasswordForm($user, $site);
		}
		$passwordForm->readInputData();

		$this->setupTemplate(true);
		if ($passwordForm->validate()) {
			$passwordForm->execute($request);
			$request->redirect(null, $request->getRequestedPage());

		} else {
			$passwordForm->display($args, $request);
		}
	}

}

?>
