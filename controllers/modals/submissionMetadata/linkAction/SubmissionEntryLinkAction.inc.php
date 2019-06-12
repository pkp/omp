<?php
/**
 * @file controllers/modals/submissionMetadata/linkAction/SubmissionEntryLinkAction.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University Library
 * Copyright (c) 2003-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SubmissionEntryLinkAction
 * @ingroup controllers_modals_submissionMetadata_linkAction
 *
 * @brief An action to open a modal to allow addition of a submission to the catalog.
 */

import('lib.pkp.classes.linkAction.LinkAction');

class SubmissionEntryLinkAction extends LinkAction {

	/**
	 * Constructor
	 * @param $request Request
	 * @param $submissionId integer The submission to show meta-data for.
	 * @param $stageId integer The stage ID of the viewer's context
	 * @param $selectedFormatId integer The publication format ID that
	 * will be used to open the correspondent publication format tab. If
	 * none is passed, the first catalog entry tab will be opened.
	 * @param $image string
	 */
	function __construct($request, $submissionId, $stageId, $selectedFormatId = null, $image = 'information') {
		$actionArgs = array(
			'submissionId' => $submissionId,
			'stageId' => $stageId,
		);
		if ($selectedFormatId) {
			$actionArgs['selectedFormatId'] = $selectedFormatId;
		}

		$dispatcher = $request->getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		parent::__construct(
			'catalogEntry',
			new AjaxModal(
				$dispatcher->url(
					$request, ROUTE_COMPONENT, null,
					'modals.submissionMetadata.CatalogEntryHandler',
					'fetch', null,
					$actionArgs
				),
				__('submission.catalogEntry'),
				'modal_more_info'
			),
			__('submission.catalogEntry'),
			$image,
			$image == 'completed'?__('grid.action.formatInCatalogEntry'):null
		);
	}
}

?>
