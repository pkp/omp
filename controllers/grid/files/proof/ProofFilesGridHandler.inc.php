<?php

/**
 * @file controllers/grid/files/proof/ProofFilesGridHandler.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesGridHandler
 * @ingroup controllers_grid_files_proof
 *
 * @brief Subclass of file editor/auditor grid for proof files.
 */

// import grid signoff files grid base classes
import('controllers.grid.files.signoff.SignoffFilesGridHandler');

// Import monograph file class which contains the MONOGRAPH_FILE_* constants.
import('classes.monograph.MonographFile');

// Import MONOGRAPH_EMAIL_* constants.
import('classes.mail.MonographMailTemplate');

class ProofFilesGridHandler extends SignoffFilesGridHandler {
	/**
	 * Constructor
	 */
	function ProofFilesGridHandler() {
		parent::SignoffFilesGridHandler(
			WORKFLOW_STAGE_ID_PRODUCTION,
			MONOGRAPH_FILE_PROOF,
			'SIGNOFF_PROOFING',
			MONOGRAPH_EMAIL_PROOFREAD_NOTIFY_AUTHOR,
			ASSOC_TYPE_PUBLICATION_FORMAT
		);
	}

	//
	// Implement template methods from PKPHandler
	//
	/**
	 * Configure the grid
	 * @param PKPRequest $request
	 */
	function initialize(&$request) {
		$publicationFormat =& $this->getPublicationFormat();
		$this->setAssocId($publicationFormat->getId());

		parent::initialize($request);

		// Basic grid configuration
		$this->setId('proofFiles-' . $this->getAssocId());
		$this->setTitle('monograph.proofReading');
		$this->setInstructions('monograph.proofReadingDescription');

		// Rename the editor column to press signoff
		$pressAssistantColumn =& $this->getColumn('editor');
		$pressAssistantColumn->setTitle('editor.pressSignoff');
	}

	/**
	 * @see SignoffFilesGridHandler::getRowInstance()
	 */
	function &getRowInstance() {
		$row =& parent::getRowInstance();
		$row->setRequestArgs($this->getRequestArgs());
		return $row;
	}

	/**
	 * @see GridHandler::getRequestArgs()
	 */
	function getRequestArgs() {
		return array_merge(
			parent::getRequestArgs(),
			array('publicationFormatId' => $this->getAssocId())
		);
	}
}

?>
