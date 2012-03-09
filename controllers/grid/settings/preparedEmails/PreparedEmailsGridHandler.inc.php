<?php

/**
 * @file controllers/grid/settings/preparedEmails/PreparedEmailsGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PreparedEmailsGridHandler
 * @ingroup controllers_grid_settings_preparedEmails
 *
 * @brief Handle preparedEmails grid requests.
 */

// Import grid base classes
import('lib.pkp.classes.controllers.grid.GridHandler');

// Import classes specific to this grid handler
import('controllers.grid.settings.preparedEmails.PreparedEmailsGridRow');

class PreparedEmailsGridHandler extends GridHandler {
	/**
	 * Constructor
	 */
	function PreparedEmailsGridHandler() {
		parent::GridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_PRESS_MANAGER),
			array('fetchRow', 'fetchGrid', 'addPreparedEmail', 'editPreparedEmail', 'updatePreparedEmail',
				'resetEmail', 'resetAllEmails', 'disableEmail', 'enableEmail', 'deleteCustomEmail')
		);
	}

	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpPressAccessPolicy');
		$this->addPolicy(new OmpPressAccessPolicy($request, $roleAssignments));
		return parent::authorize($request, $args, $roleAssignments);
	}

	/*
	 * Configure the grid
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		parent::initialize($request);
		// Basic grid configuration
		$this->setId('preparedEmailsGrid');

		AppLocale::requireComponents(LOCALE_COMPONENT_PKP_MANAGER);

		// Elements to be displayed in the grid
		$press =& $request->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplates =& $emailTemplateDao->getEmailTemplates(AppLocale::getLocale(), $press->getId());
		$rowData = array();
		foreach ($emailTemplates as $emailTemplate) {
			$rowData[$emailTemplate->getEmailKey()] = $emailTemplate;
		}
		$this->setGridDataElements($rowData);

		// Grid actions
		import('lib.pkp.classes.linkAction.LinkAction');
		import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'resetAll',
				new RemoteActionConfirmationModal(
					__('manager.emails.resetAll.message'), null,
					$router->url($request, null,
						'grid.settings.preparedEmails.PreparedEmailsGridHandler', 'resetAllEmails')
				),
				__('manager.emails.resetAll'),
				'delete'
			)
		);

		import('controllers.grid.settings.preparedEmails.linkAction.EditEmailLinkAction');
		$addEmailLinkAction = & new EditEmailLinkAction($request);
		$this->addAction($addEmailLinkAction);

		// Columns
		import('controllers.grid.settings.preparedEmails.PreparedEmailsGridCellProvider');
		$cellProvider =& new PreparedEmailsGridCellProvider();
		$this->addColumn(new GridColumn('name', 'common.name', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('sender', 'email.sender', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('recipient', 'email.recipient', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('subject', 'common.subject', null, 'controllers/grid/gridCell.tpl', $cellProvider));
		$this->addColumn(new GridColumn('enabled', 'common.enabled', null, 'controllers/grid/common/cell/checkMarkCell.tpl', $cellProvider));
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return PreparedEmailsGridRow
	 */
	function &getRowInstance() {
		$row = new PreparedEmailsGridRow();
		return $row;
	}


	//
	// Public handler methods
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

		import('controllers.grid.settings.preparedEmails.form.PreparedEmailForm');
		$preparedEmailForm = new PreparedEmailForm($emailKey, $press);
		$preparedEmailForm->initData($request);

		$json = new JSONMessage(true, $preparedEmailForm->fetch($request));
		return $json->getString();
	}

	/**
	 * Save the email editing form
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updatePreparedEmail($args, &$request) {
		$press =& $request->getPress();
		$emailKey = $request->getUserVar('emailKey');

		import('controllers.grid.settings.preparedEmails.form.PreparedEmailForm');
		$preparedEmailForm = new PreparedEmailForm($emailKey, $press);
		$preparedEmailForm->readInputData();

		if ($preparedEmailForm->validate()) {
			$preparedEmailForm->execute();

			// Create notification.
			$notificationMgr = new NotificationManager();
			$user =& $request->getUser();
			$notificationMgr->createTrivialNotification($user->getId());

			// Let the calling grid reload itself
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Reset a single email
	 * @param $args array
	 * @param $request Request
	 * @return string a serialized JSON object
	 */
	function resetEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		if ($emailTemplateDao->templateExistsByKey($emailKey, $press->getId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getId());
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Reset all email to stock.
	 * @param $args array
	 * @param $request Request
	 */
	function resetAllEmails($args, &$request) {
		$press =& $request->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplateDao->deleteEmailTemplatesByPress($press->getId());
		return DAO::getDataChangedEvent();
	}

	/**
	 * Disables an email template.
	 * @param $args array
	 * @param $request Request
	 */
	function disableEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($emailKey, $press->getId());

		if (isset($emailTemplate)) {
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(0);

				if ($emailTemplate->getAssocId() == null) {
					$emailTemplate->setAssocId($press->getId());
					$emailTemplate->setAssocType(ASSOC_TYPE_PRESS);
				}

				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
				} else {
					$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
				}

				return DAO::getDataChangedEvent($emailKey);
			}
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}


	/**
	 * Enables an email template.
	 * @param $args array
	 * @param $request Request
	 */
	function enableEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		assert(is_string($emailKey));

		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplate = $emailTemplateDao->getBaseEmailTemplate($emailKey, $press->getId());

		if (isset($emailTemplate)) {
			if ($emailTemplate->getCanDisable()) {
				$emailTemplate->setEnabled(1);

				if ($emailTemplate->getEmailId() != null) {
					$emailTemplateDao->updateBaseEmailTemplate($emailTemplate);
				} else {
					$emailTemplateDao->insertBaseEmailTemplate($emailTemplate);
				}

				return DAO::getDataChangedEvent($emailKey);
			}
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

	/**
	 * Delete a custom email.
	 * @param $args array
	 * @param $request Request
	 */
	function deleteCustomEmail($args, &$request) {
		$emailKey = $request->getUserVar('emailKey');
		$press =& $request->getPress();

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		if ($emailTemplateDao->customTemplateExistsByKey($emailKey, $press->getId())) {
			$emailTemplateDao->deleteEmailTemplateByKey($emailKey, $press->getId());
			return DAO::getDataChangedEvent($emailKey);
		} else {
			$json = new JSONMessage(false);
			return $json->getString();
		}
	}

}

?>
