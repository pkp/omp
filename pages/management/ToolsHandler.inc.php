<?php

/**
 * @file pages/management/ToolsHandler.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ToolsHandler
 * @ingroup pages_management
 *
 * @brief Handle requests for Tool pages.
 */

// Import the base ManagementHandler.
import('lib.pkp.pages.management.PKPToolsHandler');

class ToolsHandler extends PKPToolsHandler {
	/**
	 * Constructor.
	 */
	function ToolsHandler() {
		parent::PKPToolsHandler();
	}

	/**
	 * @see PKPToolsHandler::getObjectTitle()
	 */
	protected function getObjectTitle($assocId, $assocType) {
		parent::getObjectTitle($assocId, $assocType);

		switch($assocType) {
			case ASSOC_TYPE_SUBMISSION_FILE:
				$submissionFileDao =& DAORegistry::getDAO('SubmissionFileDAO'); /* @var $submissionFileDao SubmissionFileDAO */
				$submissionFile =& $submissionFileDao->getLatestRevision($assocId);
				return $submissionFile->getFileLabel();
			default:
				assert(false);
		}
	}
}

?>
