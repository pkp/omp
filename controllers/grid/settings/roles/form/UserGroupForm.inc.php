<?php

/**
 * @file controllers/grid/settings/roles/form/UserGroupForm.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupForm
 * @ingroup controllers_grid_settings_roles_form
 *
 * @brief Form to add/edit user group.
 */

import('lib.pkp.classes.form.Form');

class UserGroupForm extends Form {

	/** @var Id of the user group being edited */
	var $_userGroupId;

	/** @var The press of the user group being edited */
	var $_pressId;


	/**
	 * Constructor.
	 * @param $pressId Press id.
	 * @param $userGroupId User group id.
	 */
	function UserGroupForm($pressId, $userGroupId = null) {
		parent::Form('controllers/grid/settings/roles/form/userGroupForm.tpl');
		$this->_pressId = $pressId;
		$this->_userGroupId = $userGroupId;

		// Validation checks for this form
		$this->addCheck(new FormValidatorLocale($this, 'name', 'required', 'settings.roles.nameRequired'));
		$this->addCheck(new FormValidatorLocale($this, 'abbrev', 'required', 'settings.roles.abbrevRequired'));
		if ($this->getUserGroupId() == null) {
			$this->addCheck(new FormValidator($this, 'roleId', 'required', 'settings.roles.roleIdRequired'));
		}
		$this->addCheck(new FormValidatorPost($this));
	}


	//
	// Getters and Setters
	//
	/**
	 * Get the user group id.
	 * @return int userGroupId
	 */
	function getUserGroupId() {
		return $this->_userGroupId;
	}

	/**
	 * Get the press id.
	 * @return int pressId
	 */
	function getPressId() {
		return $this->_pressId;
	}


	//
	// Implement template methods from Form.
	//
	/**
	 * Get all locale field names
	 */
	function getLocaleFieldNames() {
		return array('name', 'abbrev');
	}

	/**
	 * @see Form::initData()
	 */
	function initData() {
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroup = $userGroupDao->getById($this->getUserGroupId());
		if ($userGroup) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
			$assignedStages =& $userGroupDao->getAssignedStagesByUserGroupId($this->getPressId(), $this->getUserGroupId());
			$assignedStages = array_flip($assignedStages);

			$data = array(
				'userGroupId' => $userGroup->getId(),
				'roleId' => $userGroup->getRoleId(),
				'name' => $userGroup->getName(null), //Localized
				'abbrev' => $userGroup->getAbbrev(null), //Localized
				'assignedStages' => $assignedStages
			);
			foreach ($data as $field => $value) {
				$this->setData($field, $value);
			}
		}
	}

	/**
	 * @see Form::readInputData()
	 */
	function readInputData() {
		$this->readUserVars(array('roleId', 'name', 'abbrev', 'assignedStages'));
	}

	/**
	 * @see Form::validate()
	 */
	function validate($callHooks = true) {
		// Name and abbrev data validation.
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); // @var $userGroupDao UserGroupDAO
		$userGroupsFromDb = $userGroupDao->getByContextId($this->getPressId());

		$userGroupId = $this->getUserGroupId();
		$nameData = $this->getData('name');
		$abbrevData = $this->getData('abbrev');

		$nameLocales = array_keys($nameData);
		$abbrevLocales = array_keys($abbrevData);

		while($group =& $userGroupsFromDb->next()) {
			// Avoid checking for singleness with itself.
			if($group->getId() == $userGroupId) continue;

			foreach($nameLocales as $locale) {
				if(strtolower($nameData[$locale]) == strtolower($group->getName($locale))) {
					$this->addError('name[' . $locale . ']', 'settings.roles.uniqueName');
				};
			};
			foreach($abbrevLocales as $locale) {
				if(strtolower($abbrevData[$locale]) == strtolower($group->getAbbrev($locale))) {
					$this->addError('abbrev[' . $locale . ']', 'settings.roles.uniqueAbbrev');
				};
			}
			unset($group);
		}

		// Assigned stages data validation.
		$assignedStagesData = $this->getData('assignedStages');
		if(!is_null($assignedStagesData)) {
			$userGroupDao =& DAORegistry::getDAO('UserGroupDAO'); // @var $userGroupDao UserGroupDAO
			$workflowStages = array_flip($userGroupDao->getWorkflowStageTranslationKeys());
			foreach($assignedStagesData as $key => $stageId) {
				$assignedStagesData[$key] = (int)$assignedStagesData[$key];
				if(!in_array($assignedStagesData[$key], $workflowStages)) {
					fatalError('Assigned stages data is not valid!');
				}
			}
		}

		return parent::validate($callHooks);
	}

	/**
	 * @see Form::fetch()
	 */
	function fetch($request) {
		$templateMgr =& TemplateManager::getManager();

		import('classes.security.RoleDAO');
		$roleOptions = RoleDAO::getRoleNames(true);
		$templateMgr->assign('roleOptions', $roleOptions);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$stages = $userGroupDao->getWorkflowStageTranslationKeys();
		$templateMgr->assign('stageOptions', $stages);

		// Users can't edit the role once user group is created.
		$disableRoleSelect = (!is_null($this->getUserGroupId())) ? true : false;
		$templateMgr->assign('disableRoleSelect', $disableRoleSelect);

		return parent::fetch($request);
	}

	/**
	 * @see Form::execute()
	 */
	function execute($request) {
		$userGroupId = $this->getUserGroupId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		// Check if we are editing an existing user group or creating another one.
		if ($userGroupId == null) {
			$userGroup = $userGroupDao->newDataObject();
			$role = new Role($this->getData('roleId'));
			$userGroup->setRoleId($role->getId());
			$userGroup->setContextId($this->getPressId());
			$userGroup->setPath($role->getPath());
			$userGroup->setDefault(false);
			$userGroup = $this->_setUserGroupLocaleFields($userGroup, $request);
			$userGroupId = $userGroupDao->insertUserGroup($userGroup);
		} else {
			$userGroup = $userGroupDao->getById($userGroupId);
			$userGroup = $this->_setUserGroupLocaleFields($userGroup, $request);
			$userGroupDao->updateLocaleFields($userGroup);
		}

		// After we have created/edited the user group, we assign/update its stages.
		if($this->getData('assignedStages')) {
			$this->_setOnDbUserGroupStageAssignment($userGroupId);
		}
	}


	//
	// Private helper methods
	//
	/**
	 * Set locale fields on a User Group object.
	 * @param UserGroup
	 * @param Request
	 * @return UserGroup
	 */
	function _setUserGroupLocaleFields($userGroup, $request) {
		$router = $request->getRouter();
		$press = $router->getContext($request);
		$supportedLocales = $press->getSupportedLocaleNames();

		if (!empty($supportedLocales)) {
			foreach ($press->getSupportedLocaleNames() as $localeKey => $localeName) {
				$name = $this->getData('name');
				$abbrev = $this->getData('abbrev');
				$userGroup->setName($name[$localeKey], $localeKey);
				$userGroup->setAbbrev($abbrev[$localeKey], $localeKey);
			}
		} else {
			$localeKey = Locale::getLocale();
			$userGroup->setName($this->getData('name'), $localeKey);
			$userGroup->setAbbrev($this->getData('abbrev'), $localeKey);
		}

		return $userGroup;
	}

	/**
	 * Setup the stages assignments to a user group in database.
	 * @param array Assigned stages to the user group.
	 */
	function _setOnDbUserGroupStageAssignment($userGroupId) {
		$pressId = $this->getPressId();
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');

		// Remove all previous assigned stages.
		$userGroupDao->removeAllStagesFromGroup($pressId, $userGroupId);

		// Assign the ones that came from user input.
		$userAssignedStages = $this->getData('assignedStages');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		foreach ($userAssignedStages as $stageId) {
			$userGroupDao->assignGroupToStage($pressId, $userGroupId, $stageId);
		}
	}
}

