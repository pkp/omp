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
	var $_reviewType;

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

		// FIXME: Need to authorize review type/round, see #6200.
		// Get the review round and review type (internal/external) from the request
		$reviewType = $request->getUserVar('reviewType');
		$round = $request->getUserVar('round');
		assert(!empty($reviewType) && !empty($round));
		$this->_reviewType = (int)$reviewType;
		$this->_round = (int)$round;

		// FIXME: Need to join internal and external review, see #6244.
		import('classes.security.authorization.OmpWorkflowStageAccessPolicy');
		$policy = new OmpWorkflowStageAccessPolicy($request, $args, $roleAssignments, 'monographId', WORKFLOW_STAGE_ID_INTERNAL_REVIEW);
		return $policy;
	}

	/**
	 * @see GridDataProvider::getRequestArgs()
	 */
	function getRequestArgs() {
		$monograph =& $this->getMonograph();
		return array(
			'monographId' => $monograph->getId(),
			'reviewType' => $this->_getReviewType(),
			'round' => $this->_getRound()
		);
	}


	//
	// Private helper methods
	//
	/**
	 * Get the review type.
	 * @return integer
	 */
	function _getReviewType() {
	    return $this->_reviewType;
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
