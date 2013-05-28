<?php

/**
 * @file controllers/grid/files/copyedit/form/CopyeditingUserForm.inc.php
 *
 * Copyright (c) 2003-2013 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CopyeditingUserForm
 * @ingroup controllers_grid_files_copyedit
 *
 * @brief Form to add files to the final draft files grid
 */

import('lib.pkp.controllers.grid.files.signoff.form.PKPFileAuditorForm');

class FileAuditorForm extends PKPFileAuditorForm {
	/** @var int */
	var $_publicationFormatId;

	/**
	 * Constructor.
	 */
	function FileAuditorForm($submission, $fileStage, $stageId, $symbolic, $eventType, $assocId = null, $publicationFormatId = null) {
		parent::PKPFileAuditorForm($submission, $fileStage, $stageId, $symbolic, $eventType, $assocId);
		$this->_publicationFormatId = $publicationFormatId;
	}

	// Getters and Setters.
	/**
	 * Get the publication format id
	 * @return int
	 */
	function getPublicationFormatId() {
		return $this->_publicationFormatId;
	}

	//
	// Overridden template methods.
	//
	/**
	 * Initialize variables
	 * @param $args array
	 * @param $request PKPRequest
	 */
	function initData($args, $request) {

		parent::initData($args, $request);

		if ($this->getPublicationFormatId()) {
			$this->setData('publicationFormatId', $this->getPublicationFormatId());
		}
	}
}

?>
