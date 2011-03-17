<?php

/**
 * @file controllers/grid/settings/preparedEmailsGrid/PreparedEmailsGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PreparedEmailsGridHandler
 * @ingroup controllers_grid_settings_preparedEmailsGrid
 *
 * @brief Handle preparedEmailsGrid grid requests.
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
			array('fetchRow', 'fetchGrid')
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
		$this->setTitle('grid.preparedEmails.currentList');

		Locale::requireComponents(array(LOCALE_COMPONENT_PKP_MANAGER));

		// Elements to be displayed in the grid
		$press =& $request->getPress();
		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO'); /* @var $emailTemplateDao EmailTemplateDAO */
		$emailTemplates =& $emailTemplateDao->getEmailTemplates(Locale::getLocale(), $press->getId());
		$rowData = array();
		foreach ($emailTemplates as $emailTemplate) {
			$rowData[$emailTemplate->getEmailKey()] = $emailTemplate;
		}
		$this->setGridDataElements($rowData);

		// Grid actions
		import('lib.pkp.classes.linkAction.LinkAction');
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$router =& $request->getRouter();
		$this->addAction(
			new LinkAction(
				'resetAll',
				new ConfirmationModal(
					__('manager.emails.resetAll.message'), null,
					$router->url($request, null,
						'api.preparedEmails.PreparedEmailsApiHandler', 'resetAllEmails')
				),
				__('manager.emails.resetAll'),
				'delete'
			)
		);

		import('controllers.api.preparedEmails.linkAction.EditEmailLinkAction');
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
}

?>
