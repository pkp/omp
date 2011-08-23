<?php
/**
 * @defgroup controllers_modals_submissionMetadata_linkAction
 */

/**
 * @file controllers/modals/submissionMetadata/linkAction/WorkflowViewMetadataLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class WorkflowViewMetadataLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief An action to open the submission meta-data modal in workflow process.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class WorkflowViewMetadataLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The submission to show meta-data for.
	 * @param $readOnly integer Define if metadata will be editable.
	 */
	function WorkflowViewMetadataLinkAction(&$request, $monographId, $stageId, $readOnly = false) {
		// Instantiate the meta-data modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');

		$modal = new AjaxModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null,
						'modals.submissionMetadata.SubmissionDetailsSubmissionMetadataHandler',
						'fetch', null, array(
							'monographId' => $monographId,
							'stageId' => $stageId,
							'readOnly' => $readOnly)),
				__('submission.viewMetadata'));

		// Configure the link action.
		parent::LinkAction('viewMetadata', $modal, __('submission.viewMetadata'), 'information');
	}
}

?>