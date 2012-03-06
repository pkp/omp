<?php

/**
 * @file controllers/grid/submissions/unassignedSubmissions/UnassignedSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class UnassignedSubmissionsListGridHandler
 * @ingroup controllers_grid_submissions_pressEditor
 *
 * @brief Handle press manager submissions list grid requests (unassigned submissions).
 */

// Import grid base classes.
import('controllers.grid.submissions.SubmissionsListGridHandler');
import('controllers.grid.submissions.SubmissionsListGridRow');

// Filter editor
define('FILTER_EDITOR_ALL', 0);
define('FILTER_EDITOR_ME', 1);

class UnassignedSubmissionsListGridHandler extends SubmissionsListGridHandler {
	/**
	 * Constructor
	 */
	function UnassignedSubmissionsListGridHandler() {
		parent::SubmissionsListGridHandler();
		$this->addRoleAssignment(array(ROLE_ID_PRESS_MANAGER, ROLE_ID_SERIES_EDITOR), array('fetchGrid'));
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::initialize()
	 */
	function initialize(&$request) {
		parent::initialize($request);

		// Set title.
		$this->setTitle('common.queue.long.submissionsUnassigned');

		// Add editor specific locale component.
		AppLocale::requireComponents(LOCALE_COMPONENT_OMP_EDITOR);
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$monographDao =& DAORegistry::getDAO('MonographDAO'); /* @var $monographDao MonographDAO */

		// Determine whether this is a Series Editor or Press Manager.
		// Press Managers can access all submissions, Series Editors
		// only assigned submissions.
		$user =& $request->getUser();

		// Get all monographs for all presses that user is
		// enrolled in as manager or series editor.
		$roleDao =& DAORegistry::getDAO('RoleDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');
		$presses =& $pressDao->getPresses();

		$accessibleMonographs = array();
		while ($press =& $presses->next()) {
			$isPressManager = $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_PRESS_MANAGER);
			$isSeriesEditor = $roleDao->userHasRole($press->getId(), $userId, ROLE_ID_SERIES_EDITOR);

			if (!$isPressManager && !$isSeriesEditor) {
				continue;
			}

			$monographFactory =& $monographDao->getUnassignedMonographs(
				$press->getId(),
				$isPressManager?null:$userId
			);

			if (!$monographFactory->wasEmpty()) {
				while ($monograph =& $monographFactory->next()) {
					if ($monograph->getDatePublished() == null) {
						$accessibleMonographs[$monograph->getId()] = $monograph;
					}
					unset($monograph);
				}
			}

			unset($press);
			unset($monographs);
			unset($monographFactory);
		}

		return $accessibleMonographs;
	}


	//
	// Overridden methods from GridHandler
	//
	/**
	 * Get the row handler - override the default row handler
	 * @return SubmissionsListGridRow
	 */
	function &getRowInstance() {
		$row = new SubmissionsListGridRow();
		return $row;
	}
}

?>
