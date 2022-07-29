<?php

/**
 * @file controllers/grid/users/stageParticipant/form/StageParticipantNotifyForm.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class StageParticipantNotifyForm
 * @ingroup grid_users_stageParticipant_form
 *
 * @brief Form to notify a user regarding a file
 */

namespace APP\controllers\grid\users\stageParticipant\form;

use APP\mail\MonographMailTemplate;
use PKP\controllers\grid\users\stageParticipant\form\PKPStageParticipantNotifyForm;

class StageParticipantNotifyForm extends PKPStageParticipantNotifyForm
{
    /**
     * Return app-specific stage templates.
     *
     * @return array
     */
    protected function _getStageTemplates()
    {
        return [
            WORKFLOW_STAGE_ID_SUBMISSION => ['EDITOR_ASSIGN'],
            WORKFLOW_STAGE_ID_EXTERNAL_REVIEW => ['EDITOR_ASSIGN'],
            WORKFLOW_STAGE_ID_EDITING => ['COPYEDIT_REQUEST'],
            WORKFLOW_STAGE_ID_PRODUCTION => ['LAYOUT_REQUEST', 'LAYOUT_COMPLETE', 'INDEX_REQUEST', 'INDEX_COMPLETE', 'EDITOR_ASSIGN']
        ];
    }

    /**
     * @copydoc PKPStageParticipantNotifyForm::_getMailTemplate()
     */
    protected function _getMailTemplate($submission, $templateKey, $includeSignature = true)
    {
        return new MonographMailTemplate($submission, $templateKey, null, null, $includeSignature);
    }
}
