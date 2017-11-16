<?php

/**
 * @file controllers/grid/settings/roles/form/UserGroupForm.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupForm
 * @ingroup controllers_grid_settings_roles_form
 *
 * @brief Form to add/edit user group in OMP.
 */

import('lib.pkp.controllers.grid.settings.roles.form.PKPUserGroupForm');

class UserGroupForm extends PKPUserGroupForm {

	/**
	 * @copydoc UserGroupForm::initData()
	 */
	public function initData() {
		parent::initData();

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroup = $userGroupDao->getById($this->getUserGroupId());
		$this->setData('isVolumeEditor', $userGroup->getIsVolumeEditor());
	}

	/**
	 * @copydoc UserGroupForm::readInputData()
	 */
	public function readInputData() {
		$this->readUserVars(array('isVolumeEditor'));
		parent::readInputData();
	}

	/**
	 * @copydoc UserGroupForm::fetch()
	 */
	public function fetch($request) {
		$templateMgr = TemplateManager::getManager($request);
		$templateMgr->assign('isVolumeEditorRoles', $this->getIsVolumeEditorRoles());

		return parent::fetch($request);
	}

	/**
	 * @copydoc PKPUserGroupForm::getGroupOptionRestrictions()
	 */
	public function getGroupOptionRestrictions() {
		return array_merge(parent::getGroupOptionRestrictions(), array(
			'isVolumeEditor' => $this->getIsVolumeEditorRoles(),
		));
	}

	/**
	 * Get a list of roles which can accept the isVolumeEditor setting.
	 *
	 * @return array
	 */
	public function getIsVolumeEditorRoles() {
		return array(ROLE_ID_AUTHOR);
	}

	/**
	 * @copydoc UserGroupForm::execute()
	 */
	public function execute($request) {
		parent::execute($request);

		$userGroupDao = DAORegistry::getDAO('UserGroupDAO');
		$userGroup = $userGroupDao->getById($this->getUserGroupId());

		if (in_array($userGroup->getRoleId(), $this->getIsVolumeEditorRoles())) {
			$userGroup->setIsVolumeEditor($this->getData('isVolumeEditor'));
			$userGroupDao->updateObject($userGroup);
		}
	}
}
