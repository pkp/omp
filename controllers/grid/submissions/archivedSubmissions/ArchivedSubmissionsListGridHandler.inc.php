<?php

/**
 * @file controllers/grid/submissions/archivedSubmissions/ArchivedSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ArchivedSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_archivedSubmissions
 *
 * @brief Handle archived submissions list grid requests.
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');
import('controllers.grid.submissions.SubmissionsListGridRow');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class ArchivedSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function ArchivedSubmissionsListGridHandler() {
		parent::SubmissionsListGridHandler();
		$this->addRoleAssignment(
			array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR, ROLE_ID_REVIEWER, ROLE_ID_PRESS_ASSISTANT),
			array('fetchGrid', 'fetchRow', 'deleteSubmission')
		);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		// Set title.
		$this->setTitle('common.queue.long.submissionsArchived');

		// Add editor specific locale component.
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions($request, $userId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO');

		// Determine whether this is a Sub Editor or Manager.
		$user = $request->getUser();

		// Get all submissions for all presses that user is
		// enrolled in as manager or series editor.
		$roleDao = DAORegistry::getDAO('RoleDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();

		$archivedMonographs = array();
		while ($press = $presses->next()) {
			$isPressManager = $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_PRESS_MANAGER);
			$isSeriesEditor = $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_SERIES_EDITOR);

			if (!$isPressManager && !$isSeriesEditor) {
				continue;
			}

			$monographFactory =& $monographDao->getByStatus(STATUS_DECLINED, $press->getId());

			if (!$monographFactory->wasEmpty()) {
				while ($monograph =& $monographFactory->next()) {
					$archivedMonographs[$monograph->getId()] = $monograph;
					unset($monograph);
				}
			}

			unset($press);
			unset($monographFactory);
		}

		return $archivedMonographs;
	}
}

?>
