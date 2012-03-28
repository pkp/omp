<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridDataProvider.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
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
	function getAuthorizationPolicy(&$request, $args, $roleAssignments) {
		import('classes.security.authorization.OmpSubmissionAccessPolicy');
		$policy = new OmpSubmissionAccessPolicy($request, $args, $roleAssignments);
		import('classes.security.authorization.internal.WorkflowStageRequiredPolicy');
		$policy->addPolicy(new WorkflowStageRequiredPolicy(WORKFLOW_STAGE_ID_PRODUCTION));
		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$policy->addPolicy(new PublicationFormatRequiredPolicy($request, $args));
		return $policy;
	}	

	/**
	 * @see GridDataProvider::loadData
	 */
	function &loadData() {
		$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO');
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		$submissionFiles = array_filter(
			$submissionFileDao->getLatestRevisionsByAssocId(ASSOC_TYPE_PUBLICATION_FORMAT, $publicationFormat->getId(), $monograph->getId(), MONOGRAPH_FILE_PROOF),
			create_function('$a', 'return $a->getViewable();')
		);

		return $submissionFiles;
	}

	/**
	 * @see GridDataProvider::getRequestArgs
	 */
	function getRequestArgs() {
		$monograph =& $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormat =& $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		return array(
			'publicationFormatId' => $publicationFormat->getId(),
			'monographId' => $monograph->getId(),
		);
	}
}

?>
