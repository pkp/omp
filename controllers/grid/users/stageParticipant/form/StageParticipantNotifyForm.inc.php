<?php

/**
 * @file controllers/grid/users/stageParticipant/form/StageParticipantNotifyForm.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantNotifyForm
 * @ingroup grid_users_stageParticipant_form
 *
 * @brief Form to notify a user regarding a file
 */

import('classes.mail.MonographMailTemplate');
import('lib.pkp.controllers.grid.users.stageParticipant.form.PKPStageParticipantNotifyForm');

class StageParticipantNotifyForm extends PKPStageParticipantNotifyForm {

	/**
	 * Constructor.
	 */
	function StageParticipantNotifyForm($itemId, $itemType, $stageId, $template = null) {
		parent::PKPStageParticipantNotifyForm($itemId, $itemType, $stageId, $template);
	}

	/**
	 * Return app-specific stage templates.
	 * @return array
	 */
	protected function _getStageTemplates() {

		return array(
			WORKFLOW_STAGE_ID_SUBMISSION => array(),
			WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => array('EDITOR_ASSIGN'),
			WORKFLOW_STAGE_ID_EDITING => array('COPYEDIT_REQUEST'),
			WORKFLOW_STAGE_ID_PRODUCTION => array('LAYOUT_REQUEST', 'LAYOUT_COMPLETE', 'INDEX_REQUEST', 'INDEX_COMPLETE', 'EDITOR_ASSIGN')
		);
	}

	/**
	 * return app-specific mail template.
	 * @param Submission $submission
	 * @param String $templateKey
	 * @param boolean $includeSignature
	 * @return array
	 */
	protected function _getMailTemplate($article, $templateKey, $includeSignature = true) {
		if ($includeSignature)
			return new MonographMailTemplate($article, $templateKey);
		else
			return new MonographMailTemplate($article, $templateKey, null, null, null, false);
	}
}

?>
