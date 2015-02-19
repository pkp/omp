<?php

/**
 * @file controllers/grid/files/signoff/SignoffFilesGridHandler.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffFilesGridHandler
 * @ingroup controllers_grid_files_signoff
 *
 * @brief Base grid for providing a list of files as categories and the requested signoffs on that file as rows.
 */

// import grid base class
import('lib.pkp.controllers.grid.files.signoff.PKPSignoffFilesGridHandler');


class SignoffFilesGridHandler extends PKPSignoffFilesGridHandler {

	/**
	 * Constructor
	 */
	function SignoffFilesGridHandler($stageId, $fileStage, $symbolic, $eventType, $assocType = null, $assocId = null) {
		parent::PKPSignoffFilesGridHandler($stageId, $fileStage, $symbolic, $eventType, $assocType, $assocId);
	}


	//
	// Implement template methods from PKPHandler
	//
	/**
	 * @see PKPHandler::authorize()
	 * @param $request PKPRequest
	 * @param $args array
	 * @param $roleAssignments array
	 */
	function authorize($request, &$args, $roleAssignments) {

		// If a publication ID was specified, authorize it.
		if ($request->getUserVar('publicationFormatId')) {
			import('classes.security.authorization.internal.PublicationFormatRequiredPolicy');
			$this->addPolicy(new PublicationFormatRequiredPolicy($request, $args));
		}

		return parent::authorize($request, $args, $roleAssignments);
	}

	//
	// Getters and Setters
	//
	/**
	 * Get publication format, if any.
	 * @return PublicationFormat
	 */
	function &getPublicationFormat() {
		return $this->getAuthorizedContextObject(ASSOC_TYPE_PUBLICATION_FORMAT);
	}

	/*
	 * return a context-specific instance of the form for this grid.
	* @return AuditorReminderForm
	*/
	function _getAuditorReminderForm() {
		import('controllers.grid.files.fileSignoff.form.AuditorReminderForm');
		$signoff = $this->getAuthorizedContextObject(ASSOC_TYPE_SIGNOFF);
		$submission = $this->getSubmission();
		$publicationFormat = $this->getPublicationFormat();
		$publicationFormatId = null;
		if (is_a($publicationFormat, 'PublicationFormat')) {
			$publicationFormatId = $publicationFormat->getId();
		}
		$auditorReminderForm = new AuditorReminderForm($signoff, $submission->getId(), $this->getStageId(), $publicationFormatId);
		return $auditorReminderForm;
	}

	/**
	 * return a context-specific instance of the file auditor form for this grid.
	 * @return FileAuditorForm
	 */
	function _getFileAuditorForm() {
		import('controllers.grid.files.signoff.form.FileAuditorForm');
		$publicationFormat = $this->getPublicationFormat();
		$publicationFormatId = null;
		if (is_a($publicationFormat, 'PublicationFormat')) {
			$publicationFormatId = $publicationFormat->getId();
		}
		$auditorForm = new FileAuditorForm($this->getSubmission(), $this->getFileStage(), $this->getStageId(), $this->getSymbolic(), $this->getEventType(), $this->getAssocId(), $publicationFormatId);
		return $auditorForm;
	}
}

?>
