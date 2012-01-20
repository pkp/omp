<?php

/**
 * @file controllers/grid/settings/formats/PublicationFormatsGridRow.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatsGridRow
 * @ingroup controllers_grid_settings_formats
 *
 * @brief Publication Formats grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class PublicationFormatsGridRow extends GridRow {

	/**
	 * Constructor
	 */
	function PublicationFormatsGridRow() {
		parent::GridRow();
	}

	//
	// Overridden methods from GridRow
	//

	/**
	 * @see GridRow::initialize()
	 * @param $request PKPRequest
	 */
	function initialize(&$request) {
		// Do the default initialization
		parent::initialize($request);

		$router =& $request->getRouter();
		$context =& $router->getContext($request);

		// Is this a new row or an existing row?
		$publicationFormat = $this->_data;

		if ($publicationFormat != null && is_numeric($publicationFormat->getId())) {
			$router =& $request->getRouter();
			$actionArgs = array(
				'formatId' => $publicationFormat->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editFormat',
					new AjaxModal(
						$router->url($request, null, null, 'editFormat', null, $actionArgs),
						__('grid.action.edit'),
						'edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			import('lib.pkp.classes.linkAction.request.ConfirmationModal');

			$assignedPublicationFormatDao =& DAORegistry::getDAO('AssignedPublicationFormatDAO');
			$count = $assignedPublicationFormatDao->getCountByPublicationFormatId($publicationFormat->getId());
			$modal = null;
			if ($count == 0) { // this publication format is not in use yet
				$modal = new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						null,
						$router->url($request, null, null, 'deleteFormat', null, $actionArgs)
				);
			} else {
				$modal = new ConfirmationModal(
						__('manager.setup.publicationFormat.inUse'),
						null, null,
						__('common.ok'),
						''
				);
			}

			$this->addAction(
				new LinkAction(
					'deleteFormat', $modal,
					__('grid.action.delete'),
					'delete'
				)
			);

			// Set a non-default template that supports row actions
			$this->setTemplate('controllers/grid/gridRowWithActions.tpl');
		}
	}
}
?>
