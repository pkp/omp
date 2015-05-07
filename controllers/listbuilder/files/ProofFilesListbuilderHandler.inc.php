<?php

/**
 * @file controllers/listbuilder/files/ProofFilesListbuilderHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesListbuilderHandler
 * @ingroup listbuilder
 *
 * @brief Class for selecting files to add a user to for copyediting.
 */

import('lib.pkp.controllers.listbuilder.files.FilesListbuilderHandler');

class ProofFilesListbuilderHandler extends FilesListbuilderHandler {
	/**
	 * Constructor
	 */
	function ProofFilesListbuilderHandler() {
		// Get access to the monograph file constants.
		import('classes.monograph.MonographFile');
		parent::FilesListbuilderHandler(SUBMISSION_FILE_PROOF);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 */
	function authorize($request, &$args, $roleAssignments) {
		import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
		$this->addPolicy(new PublicationFormatRequiredPolicy($request, $args));
		return parent::authorize($request, $args, $roleAssignments, WORKFLOW_STAGE_ID_PRODUCTION);
	}


	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize($request) {
		parent::initialize($request);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_EDITOR);
		$this->setTitle('editor.monograph.selectProofreadingFiles');
	}

	//
	// Implement methods from FilesListbuilderHandler
	//
	/**
	 * @see FilesListbuilderHandler::getOptions()
	 */
	function getOptions() {
		import('classes.monograph.MonographFile');
		$monograph = $this->getAuthorizedContextObject(ASSOC_TYPE_MONOGRAPH);
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);

		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$monographFiles = $submissionFileDao->getLatestRevisionsByAssocId(
				ASSOC_TYPE_PUBLICATION_FORMAT, $publicationFormat->getId(),
				$monograph->getId(), $this->getFileStage()
			);

		return parent::getOptions($monographFiles);
	}

	/**
	 * @see FilesListbuilderHandler::getRequestArgs
	 */
	function getRequestArgs() {
		$publicationFormat = $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
		$args = parent::getRequestArgs();
		$args['representationId'] = $publicationFormat->getId();
		return $args;
	}
}

?>
