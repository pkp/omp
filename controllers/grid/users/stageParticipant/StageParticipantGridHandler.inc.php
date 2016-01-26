<?php

/**
 * @file controllers/grid/users/stageParticipant/StageParticipantGridHandler.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2000-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantGridHandler
 * @ingroup controllers_grid_users_stageParticipant
 *
 * @brief Handle stageParticipant grid requests.
 */

// import grid base classes
import('lib.pkp.controllers.grid.users.stageParticipant.PKPStageParticipantGridHandler');

class StageParticipantGridHandler extends PKPStageParticipantGridHandler {
	/**
	 * Constructor
	 */
	function StageParticipantGridHandler() {
		parent::PKPStageParticipantGridHandler();
		$this->setTitle('editor.submission.stageParticipants');
	}

	/**
	 * return the app-specific ID for this series.
	 * @return int
	 */
	function _getIdForSubEditorFilter($submission) {
		$seriesId = $submission->getSeriesId();
		return $seriesId;
	}

	/**
	 * Log an event for this file
	 * @param $request PKPRequest
	 * @param $eventType SUBMISSION_LOG_...
	 */
	function _logEvent ($request, $eventType) {
		// Get the log event message
		switch($eventType) {
			case SUBMISSION_LOG_MESSAGE_SENT:
				$logMessage = 'informationCenter.history.messageSent';
				break;
			default:
				$logMessage = null; // Suppress warn
				assert(false);
		}

		import('lib.pkp.classes.log.SubmissionLog');
		SubmissionLog::logEvent($request, $this->getSubmission(), $eventType, $logMessage);
	}
}

?>
