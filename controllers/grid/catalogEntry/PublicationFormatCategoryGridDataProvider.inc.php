<?php

/**
 * @file controllers/grid/representations/PublicationFormatCategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2003-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesGridDataProvider
 * @ingroup controllers_grid_files_final
 *
 * @brief Provide access to proof files management.
 */


import('lib.pkp.controllers.grid.files.SubmissionFilesCategoryGridDataProvider');

class PublicationFormatCategoryGridDataProvider extends SubmissionFilesCategoryGridDataProvider {

	/** @var RepresentationsGridHandler this data provider is used in */
	var $_gridHandler;

	/**
	 * Constructor
	 */
	function __construct($gridHandler) {
		$this->_gridHandler = $gridHandler;
		import('lib.pkp.classes.submission.SubmissionFile');
		parent::__construct(SUBMISSION_FILE_PROOF);
		$this->setStageId(WORKFLOW_STAGE_ID_PRODUCTION);
	}


	//
	// Getters/setters
	//
	/**
	 * Get the representation associated with this grid
	 * @return Representation
	 */
	function getRepresentation() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_REPRESENTATION);
	}

	/**
	 * Get the submission associated with this grid
	 * @return Submission
	 */
	function getSubmission() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_SUBMISSION);
	}


	//
	// Overridden public methods from FilesGridDataProvider
	//
	/**
	 * @copydoc GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		$representation = $this->getRepresentation();
		return array_merge(
			parent::getRequestArgs(),
			array(
				'representationId' => $representation->getId(),
				'assocType' => ASSOC_TYPE_REPRESENTATION,
				'assocId' => $representation->getId(),
			)
		);
	}

	/**
	 * @see GridHandler::loadData
	 */
	function loadData($request, $filter = null) {
		$submission = $this->getSubmission();
		$representationDao = Application::getRepresentationDAO();
		$representations = $representationDao->getBySubmissionId($submission->getId());
		return $representations->toAssociativeArray();
	}

	/**
	 * @copydoc GridDataProvider::loadData()
	 */
	function loadCategoryData($request, $categoryDataElement, $filter = null, $reviewRound = null) {
		assert(is_a($categoryDataElement, 'Representation'));

		// Retrieve all submission files for the given file stage.
		$submission = $this->getSubmission();
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
		$submissionFiles = $submissionFileDao->getLatestRevisionsByAssocId(
			ASSOC_TYPE_REPRESENTATION,
			$categoryDataElement->getId(),
			$submission->getId(),
			$this->getFileStage()
		);

		// if it is a remotely hosted content, don't provide the files rows
		$remoteURL = $categoryDataElement->getRemoteURL();
		if ($remoteURL) {
			$this->_gridHandler->setEmptyCategoryRowText('grid.remotelyHostedItem');
			return array();
		}
		$this->_gridHandler->setEmptyCategoryRowText('grid.noItems');
		return $this->getDataProvider()->prepareSubmissionFileData($submissionFiles, false, $filter);

	}
}


