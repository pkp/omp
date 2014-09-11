<?php

/**
 * @file plugins/generic/customBlockManager/controllers/grid/CustomBlockGridRow.inc.php
 *
 * Copyright (c) 2014 Simon Fraser University Library
 * Copyright (c) 2003-2014 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CustomBlockGridRow
 * @ingroup controllers_grid_customBlockManager
 *
 * @brief Handle custom blocks grid row requests.
 */

import('lib.pkp.classes.controllers.grid.GridRow');

class CustomBlockGridRow extends GridRow {
	/**
	 * Constructor
	 */
	function CustomBlockGridRow() {
		parent::GridRow();
	}

	//
	// Overridden template methods
	//
	/**
	 * @see GridRow::initialize()
	 */
	function initialize($request) {
		parent::initialize($request);

		$blockName = $this->getId();

		if (!empty($blockName)) {
			$router = $request->getRouter();

			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editCustomBlock',
					new AjaxModal(
						$router->url($request, null, null, 'editCustomBlock', null, array('blockName' => $blockName)),
						__('grid.action.edit'),
						'modal_edit',
						true),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteCustomBlock',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						__('grid.action.delete'),
						$router->url($request, null, null, 'deleteCustomBlock', null, array('blockName' => $blockName)), 'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}

}

?>
