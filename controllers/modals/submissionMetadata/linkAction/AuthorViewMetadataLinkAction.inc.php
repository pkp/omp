<?php
/**
 * @file controllers/modals/submissionMetadata/linkAction/AuthorViewMetadataLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class AuthorViewMetadataLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief An action to open the submission meta-data modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class AuthorViewMetadataLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $monographId integer The submission to show meta-data for.
	 */
	function AuthorViewMetadataLinkAction(&$request, $monographId) {
		// Instantiate the meta-data modal.
		$dispatcher =& $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$modal = new AjaxModal(
				$dispatcher->url($request, ROUTE_COMPONENT, null,
						'modals.submissionMetadata.AuthorSubmissionMetadataHandler',
						'fetch', null, array('monographId' => $monographId)),
				__('submission.viewMetadata'));

		// Configure the link action.
		parent::LinkAction('viewMetadata', $modal, __('submission.viewMetadata'));
	}
}

?>
