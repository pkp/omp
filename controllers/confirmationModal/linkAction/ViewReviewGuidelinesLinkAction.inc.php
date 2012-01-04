<?php
/**
 * @defgroup controllers_confirmationModal_linkAction
 */

/**
 * @file controllers/modals/submissionMetadata/linkAction/ViewReviewGuidelinesLinkAction.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewReviewGuidelinesLinkAction
 * @ingroup controllers_confirmationModal_linkAction
 *
 * @brief An action to open the review guidelines confirmation modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ViewReviewGuidelinesLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function ViewReviewGuidelinesLinkAction(&$request) {
		$press =& $request->getPress();
		// Instantiate the view review guidelines confirmation modal.
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$viewGuidelinesModal = new ConfirmationModal(
								$press->getLocalizedSetting('reviewGuidelines'),
								__('reviewer.monograph.guidelines'),
								null, null,
								false
							);

		// Configure the link action.
		parent::LinkAction('viewReviewGuidelines', $viewGuidelinesModal, __('reviewer.monograph.guidelines'));
	}
}

?>
