<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFilesGridDataProvider
 * @ingroup controllers_grid_files_proof
 *
 * @brief Approved proof files grid data provider.
 */


import('lib.pkp.classes.controllers.grid.GridDataProvider');

class ApprovedProofFilesGridDataProvider extends GridDataProvider {
	/**
	 * Constructor
	 */
	function ApprovedProofFilesGridDataProvider() {
		parent::GridDataProvider();
	}


	//
	// Overridden methods
	//

	/**
	 * @see GridDataProvider::getAuthorizationPolicy
	 */
	function getAuthorizationPolicy($request, $args, $roleAssignments) {
		import('lib.pkp.classes.security.authorization.SubmissionAccessPolicy');
		$policy = new SubmissionAccessPolicy($request, $args, $roleAssignments);
		import('lib.pkp.classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$policy->addPolicy(new WorkflowStageRequiredPolicy(WORKFLOW_STAGE_ID_PRODUCTION));
		import('lib.pkp.classes.security.authorization.internal.RepresentationRequiredPolicy');
		$policy->addPolicy(new RepresentationRequiredPolicy($request, $args));
		return $policy;
	}	

	/**
	 * @see GridDataProvider::loadData
	 */
	function loadData() {
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		return $submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_PUBLICATION_FORMAT, $publicationFormat->getId(), $monograph->getId(), SUBMISSION_FILE_PROOF);
	}

	/**
	 * @see GridDataProvider::getRequestArgs
	 */
	function getRequestArgs() {
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		return array(
			'representationId' => $publicationFormat->getId(),
			'submissionId' => $monograph->getId(),
		);
	}
}

?>
