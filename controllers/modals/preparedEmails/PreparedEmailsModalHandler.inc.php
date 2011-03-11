<?php

/**
 * @file controllers/modals/preparedEmails/PreparedEmailsModalHandler.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PreparedEmailsModalHandler
 * @ingroup controllers_modals_preparedEmails
 *
 * @brief Handle requests for prepared template management modals.
 */

import('classes.handler.Handler');

// import JSON class for use with all AJAX requests
import('lib.pkp.classes.core.JSON');

class PreparedEmailsModalHandler extends Handler {
	/**
	 * Constructor.
	 */
	function PreparedEmailsModalHandler() {
		parent::Handler();

		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array('addPreparedEmail', 'editPreparedEmail', 'updatePreparedEmail')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Public handler actions
	//
	/**
	 * Create a new prepared email
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function addPreparedEmail($args, &$request) {
		return $this->editPreparedEmail($args, $request);
	}

	/**
	 * Edit a prepared email
	 * Will create a new prepared email if their is no emailKey in the request
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editPreparedEmail($args, &$request) {
		$press =& $request->getPress();
		$emailKey = $request->getUserVar('emailKey');

		import('controllers.modals.preparedEmails.form.PreparedEmailForm');
		$preparedEmailForm = new PreparedEmailForm($emailKey, $press);
		$preparedEmailForm->initData($request);

		$json = new JSON(true, $preparedEmailForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the email editing form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updatePreparedEmail($args, &$request) {
		$press =& Request::getPress();
		$emailKey = $request->getUserVar('emailKey');

		import('controllers.modals.preparedEmails.form.PreparedEmailForm');
		$preparedEmailForm = new PreparedEmailForm($emailKey, $press);
		$preparedEmailForm->readInputData();

		if ($preparedEmailForm->validate()) {
			$preparedEmailForm->execute();

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSON(false);
			return $json->getString();
		}
	}

}
?>