<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCategoryRow.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCategoryRow
 * @ingroup controllers_grid_catalogEntry
 *
 * @brief Publication Format grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class PublicationFormatGridCategoryRow extends GridCategoryRow {
	/** @var Monograph **/
	var $_monograph;

	/**
	 * Constructor
	 * @param $monograph Submission
	 * @param $cellProvider GridCellProvider
	 */
	function PublicationFormatGridCategoryRow($monograph, $cellProvider) {
		$this->_monograph = $monograph;
		parent::GridCategoryRow();
		$this->setCellProvider($cellProvider);
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @copydoc GridCategoryRow::getCategoryLabel()
	 */
	function getCategoryLabel() {
		$publicationFormat = $this->getData();
		return $publicationFormat->getLocalizedName();
	}
	

	//
	// Overridden methods from GridRow
	//
	/**
	 * @copydoc GridRow::initialize()
	 */
	function initialize($request, $template = null) {
		// Do the default initialization
		parent::initialize($request, $template);

		// Retrieve the monograph from the request
		$monograph = $this->getMonograph();

		// Is this a new row or an existing row?
		$publicationFormat = $this->_data;
		if ($publicationFormat && is_numeric($publicationFormat->getId())) {

			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $monograph->getId(),
				'representationId' => $publicationFormat->getId()
			);

			// Add row-level actions
			import('lib.pkp.classes.linkAction.request.AjaxModal');
			$this->addAction(
				new LinkAction(
					'editFormat',
					new AjaxModal(
						$router->url($request, null, null, 'editFormat', null, $actionArgs),
						__('grid.action.edit'),
						'modal_edit'
					),
					__('grid.action.edit'),
					'edit'
				)
			);

			import('lib.pkp.classes.linkAction.request.RemoteActionConfirmationModal');
			$this->addAction(
				new LinkAction(
					'deleteFormat',
					new RemoteActionConfirmationModal(
						__('common.confirmDelete'),
						__('common.delete'),
						$router->url($request, null, null, 'deleteFormat', null, $actionArgs),
						'modal_delete'
					),
					__('grid.action.delete'),
					'delete'
				)
			);
		}
	}

	/**
	 * Get the monograph for this row (already authorized)
	 * @return Monograph
	 */
	function getMonograph() {
		return $this->_monograph;
	}
}
?>
