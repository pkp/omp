<?php
/**
 * @defgroup controllers_confirmationModal_linkAction
 */

/**
 * @file controllers/modals/submissionMetadata/linkAction/ViewCompetingInterestGuidelinesLinkAction.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ViewCompetingInterestGuidelinesLinkAction
 * @ingroup controllers_confirmationModal_linkAction
 *
 * @brief An action to open the review guidelines confirmation modal.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class ViewCompetingInterestGuidelinesLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 */
	function ViewCompetingInterestGuidelinesLinkAction(&$request) {
		$press =& $request->getPress();
		// Instantiate the view review guidelines confirmation modal.
		import('lib.pkp.classes.linkAction.request.ConfirmationModal');
		$viewGuidelinesModal = new ConfirmationModal(
								$press->getLocalizedSetting('competingInterestPolicy'),
								__('reviewer.monograph.guidelines'),
								null, null,
								false
							);

		// Configure the link action.
		parent::LinkAction('viewCompetingInterestGuidelines', $viewGuidelinesModal, __('reviewer.monograph.guidelines'));
	}
}

?>
