<?php
/**
 * @defgroup controllers_api_proof_linkAction
 */

/**
 * @file controllers/api/proof/linkAction/ApproveProofsLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	function ApproveProofsLinkAction(&$request, $monographId, $publicationFormatId, $image = null) {

		// Create the actionArgs array
		$actionArgs = array();
		$actionArgs['monographId'] = $monographId;
		$actionArgs['stageId'] = WORKFLOW_STAGE_ID_PRODUCTION;
		$actionArgs['publicationFormatId'] = $publicationFormatId;

		$dispatcher =& $request->getDispatcher();
		$modal = new AjaxModal(
			$dispatcher->url(
				$request, ROUTE_COMPONENT, null,
				'modals.editorDecision.EditorDecisionHandler',
				'approveProofs', null,
				$actionArgs),
			__('editor.monograph.decision.approveProofs')
		);

		// Configure the link action.
		parent::LinkAction('approveProofs-' . $publicationFormatId, $modal, __('editor.monograph.decision.approveProofs'), $image);
	}
}

?>
