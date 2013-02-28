<?php

/**
 * @file controllers/grid/admin/press/PressGridHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridHandler
 * @ingroup controllers_grid_admin_press
 *
 * @brief Handle press grid requests.
 */

import('lib.pkp.controllers.grid.admin.context.ContextGridHandler');

import('controllers.grid.admin.press.PressGridRow');
import('controllers.grid.admin.press.form.PressSiteSettingsForm');

class PressGridHandler extends ContextGridHandler {
	/**
	 * Constructor
	 */
	function PressGridHandler() {
		parent::ContextGridHandler();
	}


	//
	// Implement template methods from PKPHandler.
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		// Load user-related translations.
		AppLocale::requireComponents(
			LOCALE_COMPONENT_APP_ADMIN,
			LOCALE_COMPONENT_APP_MANAGER,
			LOCALE_COMPONENT_APP_COMMON
		);

		parent::initialize($request);

		// Basic grid configuration.
		$this->setTitle('press.presses');
	}


	//
	// Implement methods from GridHandler.
	//
	/**
	 * @see GridHandler::getRowInstance()
	 * @return UserGridRow
	 */
	function &getRowInstance() {
		$row = new PressGridRow();
		return $row;
	}

	/**
	 * @see GridHandler::loadData()
	 * @param $request PKPRequest
	 * @return array Grid data.
	 */
	function loadData(&$request) {
		// Get all presses.
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses = $pressDao->getAll();

		return $presses->toAssociativeArray();
	}

	/**
	 * @see lib/pkp/classes/controllers/grid/GridHandler::setDataElementSequence()
	 */
	function setDataElementSequence(&$request, $rowId, &$press, $newSequence) {
		$pressDao = DAORegistry::getDAO('PressDAO'); /* @var $pressDao PressDAO */
		$press->setSequence($newSequence);
		$pressDao->updateObject($press);
	}


	//
	// Public grid actions.
	//
	/**
	 * Edit an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function editContext($args, &$request) {

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');

		// Form handling.
		$settingsForm = new PressSiteSettingsForm(!isset($pressId) || empty($pressId) ? null : $pressId);
		$settingsForm->initData();
		$json = new JSONMessage(true, $settingsForm->fetch($args, $request));

		return $json->getString();
	}

	/**
	 * Update an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function updateContext($args, $request) {
		// Identify the press Id.
		$pressId = $request->getUserVar('contextId');

		// Form handling.
		$settingsForm = new PressSiteSettingsForm($pressId);
		$settingsForm->readInputData();

		if ($settingsForm->validate()) {
			PluginRegistry::loadCategory('blocks');

			// The press settings form will return a press path in two cases:
			// 1 - if a new press was created;
			// 2 - if a press path of an existing press was edited.
			$newPressPath = $settingsForm->execute($request);

			// Create the notification.
			$notificationMgr = new NotificationManager();
			$user =& $request->getUser();
			$notificationMgr->createTrivialNotification($user->getId());

			// Check for the two cases above.
			if ($newPressPath) {
				$context = $request->getContext();

				if (is_null($pressId)) {
					// CASE 1: new press created.
					// Create notification related to payment method configuration.
					$pressDao =& DAORegistry::getDAO('PressDAO');
					$newPress =& $pressDao->getByPath($newPressPath);
					$notificationMgr->createNotification($request, null, NOTIFICATION_TYPE_CONFIGURE_PAYMENT_METHOD,
						$newPress->getId(), ASSOC_TYPE_PRESS, $newPress->getId(), NOTIFICATION_LEVEL_NORMAL);

					// redirect and set the parameter to open the press
					// setting wizard modal after redirection.
					return $this->_getRedirectEvent($request, $newPressPath, true);
				} else {
					// CASE 2: check if user is in the context of
					// the press being edited.
					if ($context->getId() == $pressId) {
						return $this->_getRedirectEvent($request, $newPressPath, false);
					}
				}
			}
			return DAO::getDataChangedEvent($pressId);
		} else {
			$json = new JSONMessage(false);
		}
		return $json->getString();
	}

	/**
	 * Delete a press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return string Serialized JSON object
	 */
	function deleteContext($args, &$request) {
		// Identify the current context.
		$context =& $request->getContext();

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$press =& $pressDao->getById($pressId);

		$json = new JSONMessage();

		if ($pressId) {
			$pressDao->deleteById($pressId);
			// Add publication formats tombstones for all press published monographs.
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
			$publicationFormatTombstoneMgr->insertTombstonesByPress($press);

			// Delete press file tree
			// FIXME move this somewhere better.
			import('classes.file.PressFileManager');
			$pressFileManager = new PressFileManager($pressId);
			$pressFileManager->rmtree($pressFileManager->getBasePath());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$publicFileManager->rmtree($publicFileManager->getPressFilesPath($pressId));

			// If user is deleting the same press where he is...
			if($context->getId() == $pressId) {
				// return a redirect js event to index handler.
				$dispatcher =& $request->getDispatcher();
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'index');
				return $request->redirectUrlJson($url);
			}

			return DAO::getDataChangedEvent($pressId);
		}

		return $json->getString();
	}


	//
	// Private helper methods.
	//
	/**
	 * Return a redirect event.
	 * @param $request Request
	 * @param $newPressPath string
	 * @param $openWizard boolean
	 */
	function _getRedirectEvent(&$request, $newPressPath, $openWizard) {
		$dispatcher =& $request->getDispatcher();

		$url = $dispatcher->url($request, ROUTE_PAGE, $newPressPath, 'admin', 'contexts', null, array('openWizard' => $openWizard));
		return $request->redirectUrlJson($url);
	}

	/**
	 * Get the "add context" locale key
	 * @return string
	 */
	protected function _getAddContextKey() {
		return 'admin.presses.addPress';
	}

	/**
	 * Get the context name locale key
	 * @return string
	 */
	protected function _getContextNameKey() {
		return 'manager.setup.contextName';
	}
}

?>
