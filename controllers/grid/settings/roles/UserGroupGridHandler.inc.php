<?php
/**
 * @file controllers/grid/settings/roles/UserGroupGridHandler.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UserGroupGridHandler
 * @ingroup controllers_grid_settings
 *
 * @brief Handle operations for user group management operations in OMP.
 */
import('lib.pkp.controllers.grid.settings.roles.PKPUserGroupGridHandler');

class UserGroupGridHandler extends PKPUserGroupGridHandler {

	/**
	 * @copydoc PKPUserGroupGridHandler::_getUserGroupForm()
	 */
	public function _getUserGroupForm($request) {
		import('controllers.grid.settings.roles.form.UserGroupForm');
		return new UserGroupForm($this->_getContextId(), (int) $request->getUserVar('userGroupId'));
	}

}
