<?php

/**
 * @file controllers/grid/files/signoff/form/FileAuditorForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class FileAuditorForm
 * @ingroup controllers_grid_files_signoff
 *
 * @brief File auditor form.
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
