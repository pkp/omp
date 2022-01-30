<?php

/**
 * @file controllers/modals/editorDecision/EditorDecisionHandler.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class EditorDecisionHandler
 * @ingroup controllers_modals_editorDecision
 *
 * @brief Handle requests for editors to make a decision
 */

use APP\workflow\EditorDecisionActionsManager;
use PKP\controllers\modals\editorDecision\PKPEditorDecisionHandler;
use PKP\core\JSONMessage;
use PKP\notification\PKPNotification;
use PKP\security\authorization\EditorDecisionAccessPolicy;
use PKP\security\Role;

use PKP\workflow\WorkflowStageDAO;

class EditorDecisionHandler extends PKPEditorDecisionHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addRoleAssignment(
            [Role::ROLE_ID_SUB_EDITOR, Role::ROLE_ID_MANAGER],
            array_merge([
                'internalReview', 'saveInternalReview',
                'externalReview', 'saveExternalReview',
                'sendReviews', 'saveSendReviews',
                'promote', 'savePromote',
                'revertDecline', 'saveRevertDecline',
            ], $this->_getReviewRoundOps())
        );
    }


    //
    // Implement template methods from PKPHandler
    //
    /**
     * @see PKPHandler::authorize()
     */
    public function authorize($request, &$args, $roleAssignments)
    {
        $stageId = (int) $request->getUserVar('stageId');
        $this->addPolicy(new EditorDecisionAccessPolicy($request, $args, $roleAssignments, 'submissionId', $stageId));

        return parent::authorize($request, $args, $roleAssignments);
    }


    //
    // Public handler actions
    //
    /**
     * Start a new review round
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage
     */
    public function saveNewReviewRound($args, $request)
    {
        // FIXME: this can probably all be managed somewhere.
        $stageId = $this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE);
        if ($stageId == WORKFLOW_STAGE_ID_INTERNAL_REVIEW) {
            $redirectOp = WorkflowStageDAO::WORKFLOW_STAGE_PATH_INTERNAL_REVIEW;
        } elseif ($stageId == WORKFLOW_STAGE_ID_EXTERNAL_REVIEW) {
            $redirectOp = WorkflowStageDAO::WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
        } else {
            $redirectOp = null; // Suppress warn
            assert(false);
        }

        return $this->_saveEditorDecision($args, $request, 'NewReviewRoundForm', $redirectOp, EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_NEW_ROUND);
    }

    /**
     * Start a new review round
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return string Serialized JSON object
     */
    public function internalReview($args, $request)
    {
        return $this->_initiateEditorDecision($args, $request, 'InitiateInternalReviewForm');
    }

    /**
     * Start a new review round
     *
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage
     */
    public function saveInternalReview($args, $request)
    {
        assert($this->getAuthorizedContextObject(ASSOC_TYPE_WORKFLOW_STAGE) == WORKFLOW_STAGE_ID_SUBMISSION);
        return $this->_saveEditorDecision(
            $args,
            $request,
            'InitiateInternalReviewForm',
            WorkflowStageDAO::WORKFLOW_STAGE_PATH_INTERNAL_REVIEW,
            EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW
        );
    }


    //
    // Protected helper methods
    //
    /**
     * @param array $args
     * @param PKPRequest $request
     *
     * @return JSONMessage
     */
    protected function _saveGeneralPromote($args, $request)
    {
        // Redirect to the next workflow page after
        // promoting the submission.
        $decision = (int)$request->getUserVar('decision');

        $redirectOp = null;

        if ($decision == EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_ACCEPT) {
            $redirectOp = WorkflowStageDAO::WORKFLOW_STAGE_PATH_EDITING;
        } elseif ($decision == EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW) {
            $redirectOp = WorkflowStageDAO::WORKFLOW_STAGE_PATH_EXTERNAL_REVIEW;
        } elseif ($decision == EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION) {
            $redirectOp = WorkflowStageDAO::WORKFLOW_STAGE_PATH_PRODUCTION;
        }

        // Make sure user has access to the workflow stage.
        $redirectWorkflowStage = WorkflowStageDAO::getIdFromPath($redirectOp);
        $userAccessibleWorkflowStages = $this->getAuthorizedContextObject(ASSOC_TYPE_ACCESSIBLE_WORKFLOW_STAGES);
        if (!array_key_exists($redirectWorkflowStage, $userAccessibleWorkflowStages)) {
            $redirectOp = null;
        }

        return $this->_saveEditorDecision($args, $request, 'PromoteForm', $redirectOp);
    }

    /**
     * Get editor decision notification type and level by decision.
     *
     * @param int $decision
     *
     * @return int
     */
    protected function _getNotificationTypeByEditorDecision($decision)
    {
        switch ($decision) {
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_INTERNAL_REVIEW:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_INTERNAL_REVIEW;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_ACCEPT:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_ACCEPT;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_EXTERNAL_REVIEW:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_EXTERNAL_REVIEW;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_PENDING_REVISIONS:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_PENDING_REVISIONS;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_RESUBMIT:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_RESUBMIT;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_NEW_ROUND:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_NEW_ROUND;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_DECLINE:
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_INITIAL_DECLINE:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_DECLINE;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_REVERT_DECLINE:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_REVERT_DECLINE;
            case EditorDecisionActionsManager::SUBMISSION_EDITOR_DECISION_SEND_TO_PRODUCTION:
                return PKPNotification::NOTIFICATION_TYPE_EDITOR_DECISION_SEND_TO_PRODUCTION;
        }
        throw new Exception('Unknown editor decision.');
    }

    /**
     * Get review-related stage IDs.
     *
     * @return array
     */
    protected function _getReviewStages()
    {
        return [WORKFLOW_STAGE_ID_INTERNAL_REVIEW, WORKFLOW_STAGE_ID_EXTERNAL_REVIEW];
    }

    /**
     * Get review-related decision notifications.
     */
    protected function _getReviewNotificationTypes()
    {
        return [PKPNotification::NOTIFICATION_TYPE_PENDING_INTERNAL_REVISIONS, PKPNotification::NOTIFICATION_TYPE_PENDING_EXTERNAL_REVISIONS];
    }

    /**
     * Get the fully-qualified import name for the given form name.
     *
     * @param string $formName Class name for the desired form.
     *
     * @return string
     */
    protected function _resolveEditorDecisionForm($formName)
    {
        switch ($formName) {
            case 'InitiateInternalReviewForm':
            case 'InitiateExternalReviewForm':
                return "controllers.modals.editorDecision.form.${formName}";
            default:
                return parent::_resolveEditorDecisionForm($formName);
        }
    }
}
