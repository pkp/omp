<?php

/**
 * @defgroup submission_common
 */

/**
 * @file classes/submission/common/Action.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class Action
 * @ingroup submission_common
 *
 * @brief Application-specific submission actions.
 */


// Review stage decisions actions.
define('SUBMISSION_EDITOR_DECISION_ACCEPT', 1);
define('SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW', 2);
define('SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS', 3);
define('SUBMISSION_EDITOR_DECISION_RESUBMIT', 4);
define('SUBMISSION_EDITOR_DECISION_DECLINE', 5);

// Copyediting stage decision actions.
define('SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION', 6);

// These constants are used as search fields for the various submission lists.
define('SUBMISSION_FIELD_AUTHOR', 1);
define('SUBMISSION_FIELD_EDITOR', 2);
define('SUBMISSION_FIELD_TITLE', 3);
define('SUBMISSION_FIELD_REVIEWER', 4);
define('SUBMISSION_FIELD_COPYEDITOR', 5);
define('SUBMISSION_FIELD_LAYOUTEDITOR', 6);
define('SUBMISSION_FIELD_PROOFREADER', 7);

define('SUBMISSION_FIELD_DATE_SUBMITTED', 4);
define('SUBMISSION_FIELD_DATE_COPYEDIT_COMPLETE', 5);
define('SUBMISSION_FIELD_DATE_LAYOUT_COMPLETE', 6);
define('SUBMISSION_FIELD_DATE_PROOFREADING_COMPLETE', 7);

import('lib.pkp.classes.submission.common.PKPAction');

class Action extends PKPAction {
	/**
	 * Constructor.
	 */
	function Action() {
		parent::PKPAction();
	}

	//
	// Actions.
	//
	/**
	 * Assign the default participants to a workflow stage.
	 * @param $monographId int
	 * @param $stageId int
	 */
	function assignDefaultStageParticipants($monographId, $stageId) {
		$signoffDao =& DAORegistry::getDAO('SignoffDAO');
		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupStageAssignmentDao =& DAORegistry::getDAO('UserGroupStageAssignmentDAO');
		$monographDao =& DAORegistry::getDAO('MonographDAO');
		$monograph =& $monographDao->getMonograph($monographId);

		// Managerial roles are skipped -- They have access by default and
		//  are assigned for informational purposes only

		// Series editor roles are skipped -- They are assigned by PM roles
		//  or by other series editors

		// Press roles -- For each press role user group assigned to this
		//  stage in setup, iff there is only one user for the group,
		//  automatically assign the user to the stage
		$submissionStageGroups =& $userGroupStageAssignmentDao->getUserGroupsByStage($monograph->getPressId(), $stageId);
		while ($userGroup =& $submissionStageGroups->next()) {
			if($userGroup->getRoleId() == ROLE_ID_PRESS_ASSISTANT) {
				$users =& $userGroupDao->getUsersById($userGroup->getId());
				if($users->getCount() == 1) {
					$user =& $users->next();
					$signoffDao->build('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $user->getId(), $stageId, $userGroup->getId());
				}
			}
		}

		// Author roles -- Assign only the submitter
		// FIXME #6001: If the submission is a monograph, then the user group
		//   assigned for the submitter should be author; If its a volume,
		// 	 it should be a volume editor user group.
		$authorUserGroup =& $userGroupDao->getDefaultByRoleId($monograph->getPressId(), ROLE_ID_AUTHOR);
		$signoffDao->build('SIGNOFF_STAGE', ASSOC_TYPE_MONOGRAPH, $monographId, $monograph->getUserId(), $stageId, $authorUserGroup->getId());

		// Reviewer roles -- Do nothing
		// FIXME #6002: Need to review this -- Not sure if reviewers should be
		//  added as stage participants
	}
}

?>
