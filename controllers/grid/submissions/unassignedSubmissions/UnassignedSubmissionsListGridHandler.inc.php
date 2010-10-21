<?php

/**
 * @file controllers/grid/submissions/pressEditor/UnassignedSubmissionsListGridHandler.inc.php
 *
 * Copyright (c) 2000-2009 John Willinsky
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
		parent::GridHandler();
		$this->addRoleAssignment(array(ROLE_ID_SITE_ADMIN, ROLE_ID_PRESS_MANAGER), array('fetchGrid'));
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

		$cellProvider = new SubmissionsListGridCellProvider();
		$this->addColumn(
			new GridColumn(
				'title',
				'monograph.title',
				null,
				'controllers/grid/gridCell.tpl',
				$cellProvider
			)
		);

		// Add editor specific locale component.
		Locale::requireComponents(array(LOCALE_COMPONENT_OMP_EDITOR));
	}


	//
	// Implement template methods from SubmissionListGridHandler
	//
	/**
	 * @see SubmissionListGridHandler::getSubmissions()
	 */
	function getSubmissions(&$request, $userId) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$pressDao =& DAORegistry::getDAO('PressDAO');

		$editorUserGroupIds = array_merge($userGroupDao->getUserGroupIdsByRoleId(ROLE_ID_PRESS_MANAGER), $userGroupDao->getUserGroupIdsByRoleId(ROLE_ID_SERIES_EDITOR));

		$data = array();
		// Get monographs with only one signoff, make sure that signoff isn't an editor usergroup
		$presses =& $pressDao->getPresses();
		while($press =& $presses->next()) { // Iterate over all presses
			$monographs =& $monographDao->getMonographsByPressId($press->getId()); // Get all monographs for each press
			while($monograph =& $monographs->next()) {
				// Get all signoffs in stage 1
				$signoffs =& $signoffDao->getAllBySymbolic('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monograph->getId(), null, 1);
				if($signoffs->getCount() == 1) { // Check that there is only one stage participant (the author, as set by default)
					$signoff = $signoffs->next();
					if(!in_array($signoff->getUserGroupId(), $editorUserGroupIds)) {
						$data[$signoff->getAssocId()] = $monographDao->getMonograph($signoff->getAssocId());
					}
				}
				unset($monograph);
			}
			unset($press);
		}

		return $data;
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