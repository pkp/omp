<?php

/**
 * @file controllers/grid/admin/press/PressGridHandler.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressGridHandler
 * @ingroup controllers_grid_admin_press
 *
 * @brief Handle press grid requests.
 */

import('lib.pkp.controllers.grid.admin.context.ContextGridHandler');
import('controllers.grid.admin.press.form.PressSiteSettingsForm');

class PressGridHandler extends ContextGridHandler {
	/**
	 * Constructor
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Public grid actions.
	//
	/**
	 * Edit an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
	 */
	function editContext($args, $request) {

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');

		// Form handling.
		$settingsForm = new PressSiteSettingsForm(!isset($pressId) || empty($pressId) ? null : $pressId);
		$settingsForm->initData();
		return new JSONMessage(true, $settingsForm->fetch($request));
	}

	/**
	 * Update an existing press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage JSON object
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
			$newPressPath = $settingsForm->execute();

			// Create the notification.
			$notificationMgr = new NotificationManager();
			$user = $request->getUser();
			$notificationMgr->createTrivialNotification($user->getId());

			// Check for the two cases above.
			if ($newPressPath) {
				$context = $request->getContext();

				if (is_null($pressId)) {
					// CASE 1: new press created.
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
			return new JSONMessage(false);
		}
	}

	/**
	 * Delete a press.
	 * @param $args array
	 * @param $request PKPRequest
	 * @return JSONMessage
	 */
	function deleteContext($args, $request) {
		// Identify the current context.
		$context = $request->getContext();

		// Identify the press Id.
		$pressId = $request->getUserVar('rowId');
		$pressDao = DAORegistry::getDAO('PressDAO');
		$press = $pressDao->getById($pressId);

		if ($press && $request->checkCSRF()) {
			$pressDao->deleteById($pressId);
			// Add publication formats tombstones for all press published monographs.
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
			$publicationFormatTombstoneMgr->insertTombstonesByPress($press);

			// Delete press file tree
			// FIXME move this somewhere better.
			import('lib.pkp.classes.file.ContextFileManager');
			$pressFileManager = new ContextFileManager($pressId);
			$pressFileManager->rmtree($pressFileManager->getBasePath());

			import('classes.file.PublicFileManager');
			$publicFileManager = new PublicFileManager();
			$publicFileManager->rmtree($publicFileManager->getPressFilesPath($pressId));

			// If user is deleting the same press where he is...
			if($context && $context->getId() == $pressId) {
				// return a redirect js event to index handler.
				$dispatcher = $request->getDispatcher();
				$url = $dispatcher->url($request, ROUTE_PAGE, null, 'index');
				return $request->redirectUrlJson($url);
			}

			return DAO::getDataChangedEvent($pressId);
		}

		return new JSONMessage(false);
	}
}


