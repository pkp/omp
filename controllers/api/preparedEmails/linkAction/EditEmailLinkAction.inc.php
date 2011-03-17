<?php
/**
 * @file controllers/api/preparedEmails/linkAction/EditEmailLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class EditEmailLinkAction
 * @ingroup controllers_api_preparedEmails_linkAction
 *
 * @brief Add/Edit a prepared email.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class EditEmailLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $emailKey string
	 */
	function EditEmailLinkAction(&$request, $emailKey = null) {
		// Create the action arguments array.
		$actionArgs = array();
		if($emailKey) $actionArgs['emailKey'] = $emailKey;

		// Instantiate the file upload modal.
		$router =& $request->getRouter();
		$dispatcher =& $router->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');

		$title = $emailKey ? 'manager.emails.editEmail' : 'manager.emails.addEmail';
		$action = $emailKey ? 'editPreparedEmail' : 'addPreparedEmail';
		$icon = $emailKey ? 'edit' : 'add_item';

		$modal = new AjaxModal(
			$dispatcher->url($request, ROUTE_COMPONENT, null,
				'modals.preparedEmails.PreparedEmailsModalHandler', $action,
				null, $actionArgs),
			__($title), $icon);

		// Configure the link action.
		parent::LinkAction($action, $modal, __($title), $icon);
	}


}

?>
