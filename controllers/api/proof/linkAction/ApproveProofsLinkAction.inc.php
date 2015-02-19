<?php

/**
 * @defgroup controllers_api_proof_linkAction Proof API link action
 */

/**
 * @file controllers/api/proof/linkAction/ApproveProofsLinkAction.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApproveProofsLinkAction
 * @ingroup controllers_api_proof_linkAction
 *
 * @brief Class for approving proof files.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ApproveProofsLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer
	 * @param $publicationFormatId integer
	 * @param $title string Locale key
	 * @param $image string
	 */
	function ApproveProofsLinkAction($request, $monographId, $publicationFormatId, $image = null) {

		// Create the actionArgs array
		$actionArgs = array();
		$actionArgs['submissionId'] = $monographId;
		$actionArgs['stageId'] = WORKFLOW_STAGE_ID_PRODUCTION;
		$actionArgs['publicationFormatId'] = $publicationFormatId;

		$dispatcher = $request->getDispatcher();
		$modal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'modals.editorDecision.EditorDecisionHandler',
				'approveProofs', null,
				$actionArgs),
			__('editor.submission.decision.approveProofs'),
			'modal_approve_proofs'
		);

		$toolTip = ($image == 'completed') ? __('grid.action.proofApproved') : null;
		// Configure the link action.
		parent::LinkAction('approveProofs-' . $publicationFormatId, $modal, __('editor.submission.decision.approveProofs'), $image, $toolTip);
	}
}

?>
