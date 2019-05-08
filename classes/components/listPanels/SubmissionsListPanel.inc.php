<?php
/**
 * @file components/listPanels/SubmissionsListPanel.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionsListPanel
 * @ingroup classes_components_listPanels
 *
 * @brief Instantiates and manages a UI component to list submissions.
 */

namespace APP\components\listPanels;
use \PKP\components\listPanels\PKPSubmissionsListPanel;

class SubmissionsListPanel extends PKPSubmissionsListPanel {

	/**
	 * Get an array of workflow stages supported by the current app
	 *
	 * @return array
	 */
	public function getWorkflowStages() {
		return array(
			array(
				'param' => 'stageIds',
				'value' => WORKFLOW_STAGE_ID_SUBMISSION,
				'title' => __('manager.publication.submissionStage'),
			),
			array(
				'param' => 'stageIds',
				'value' => WORKFLOW_STAGE_ID_INTERNAL_REVIEW,
				'title' => __('workflow.review.internalReview'),
			),
			array(
				'param' => 'stageIds',
				'value' => WORKFLOW_STAGE_ID_EXTERNAL_REVIEW,
				'title' => __('workflow.review.externalReview'),
			),
			array(
				'param' => 'stageIds',
				'value' => WORKFLOW_STAGE_ID_EDITING,
				'title' => __('submission.copyediting'),
			),
			array(
				'param' => 'stageIds',
				'value' => WORKFLOW_STAGE_ID_PRODUCTION,
				'title' => __('manager.publication.productionStage'),
			),
		);
	}
}
