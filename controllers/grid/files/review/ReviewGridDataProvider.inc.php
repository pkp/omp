<?php

/**
 * @file controllers/grid/files/review/ReviewGridDataProvider.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewGridDataProvider
 * @ingroup controllers_grid_files_review
 *
 * @brief Provide access to review file data for grids.
 */


import('controllers.grid.files.FilesGridDataProvider');

class ReviewGridDataProvider extends FilesGridDataProvider {

	/** @var integer */
	var $_stageId;

	/** @var integer */
	var $_round;


	/**
	 * Constructor
	 */
	function ReviewGridDataProvider() {
		parent::FilesGridDataProvider();
	}


	//
	// Implement template methods from GridDataProvider
	//
	/**
	 * @see GridDataProvider::getAuthorizationPolicy()
	 */
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		$this->setUploaderRoles($roleAssignments);

		// FIXME: Need to authorize review round, see #6200.
		// Get the review round and review stage id (internal/external) from the request
		$stageId = $request->getUserVar('stageId');
		$round = $request->getUserVar('round');
		assert(!empty($stageId) && !empty($round));
		$this->_round = (int)$round;

		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$policy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', $stageId);
		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'stageId' => $this->_getStageId(),
			'round' => $this->_getRound()
		);
	}


	//
	// Private helper methods
	//
	/**
	 * Get the review stage id.
	 * @return integer
	 */
	function _getStageId() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
	}

	/**
	 * Get the review round number.
	 * @return integer
	 */
	function _getRound() {
		return $this->_round;
	}
}

?>
