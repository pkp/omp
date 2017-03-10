<?php

/**
 * @file controllers/grid/manageCatalog/CatalogMonographsGridRow.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogMonographsGridRow
 * @ingroup controllers_grid_manageCatalog
 *
 * Implements CatalogMonographsGridHandler rows.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class CatalogMonographsGridRow extends GridRow {

	/**
	 * Constructor.
	 */
	function __construct() {
		parent::__construct();
	}


	//
	// Extend GridRow methods.
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		parent::initialize($request, $template);
		AppLocale::requireComponents(LOCALE_COMPONENT_APP_SUBMISSION, LOCALE_COMPONENT_PKP_SUBMISSION);

		$requestArgs = array_merge($this->getRequestArgs(), array('submissionId' => $this->getId(), 'rowId' => $this->getId()));

		$dispatcher = Application::getDispatcher();
		import('lib.pkp.classes.linkAction.request.AjaxModal');
		$this->addAction(
			new LinkAction(
				'catalogEntry',
				new AjaxModal(
					$dispatcher->url(
						$request,
						ROUTE_COMPONENT,
						null,
						'modals.submissionMetadata.CatalogEntryHandler',
						'fetch',
						null,
						array_merge($requestArgs, array('stageId' => WORKFLOW_STAGE_ID_PRODUCTION))
					),
					__('submission.catalogEntry'),
					'modal_more_info'
				),
				__('submission.editCatalogEntry')
			)
		);

		import('lib.pkp.classes.linkAction.request.RedirectAction');
		$this->addAction(
			new LinkAction(
				'itemWorkflow',
				new RedirectAction(
					$dispatcher->url(
						$request,
						ROUTE_PAGE,
						null,
						'workflow',
						'access',
						null,
						$requestArgs
					)
				),
				__('submission.submission')
			)
		);
	}
}

?>

