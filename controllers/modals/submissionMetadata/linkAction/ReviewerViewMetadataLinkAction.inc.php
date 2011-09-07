<?php
/**
 * @defgroup controllers_modals_submissionMetadata_linkAction
 */

/**
 * @file controllers/modals/submissionMetadata/linkAction/ReviewerViewMetadataLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ReviewerViewMetadataLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief An action to open the submission meta-data modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ReviewerViewMetadataLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer
	 * @param $reviewAssignmentId integer
	 */
	function ReviewerViewMetadataLinkAction(&$request, $monographId, $reviewAssignmentId) {
		// Instantiate the meta-data modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$modal = new AjaxModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null,
						'modals.submissionMetadata.ReviewerSubmissionMetadataHandler',
						'fetch', null, array('monographId' => $monographId, 'reviewAssignmentId' => $reviewAssignmentId)),
				__('reviewer.step1.viewAllDetails'));

		// Configure the link action.
		parent::LinkAction('viewMetadata', $modal, __('reviewer.step1.viewAllDetails'));
	}
}

?>
