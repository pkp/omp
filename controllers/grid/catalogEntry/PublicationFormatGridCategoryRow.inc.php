<?php

/**
 * @file controllers/grid/catalogEntry/PublicationFormatGridCategoryRow.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationFormatGridCategoryRow
 * @ingroup controllers_grid_representations
 *
 * @brief Representations grid row definition
 */

import('lib.pkp.classes.controllers.grid.GridCategoryRow');

class PublicationFormatGridCategoryRow extends GridCategoryRow {

	/** @var Submission **/
	var $_submission;

	/** @var boolean */
	protected $_canManage;

	/** @var Publication **/
	var $_publication;

	/**
	 * Constructor
	 * @param $submission Submission
	 * @param $cellProvider GridCellProvider
	 * @param $canManage boolean
	 * @param $publication Publication
	 */
	function __construct($submission, $cellProvider, $canManage, $publication) {
		$this->_submission = $submission;
		$this->_canManage = $canManage;
		$this->_publication = $publication;
		parent::__construct();
		$this->setCellProvider($cellProvider);
	}

	//
	// Overridden methods from GridCategoryRow
	//
	/**
	 * @copydoc GridCategoryRow::getCategoryLabel()
	 */
	function getCategoryLabel() {
		return $this->getData()->getLocalizedName();
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

		// Retrieve the submission from the request
		$submission = $this->getSubmission();

		// Is this a new row or an existing row?
		$representation = $this->getData();
		if ($representation && is_numeric($representation->getId()) && $this->_canManage) {
			$router = $request->getRouter();
			$actionArgs = array(
				'submissionId' => $submission->getId(),
				'representationId' => $representation->getId(),
				'publicationId' => $this->getPublication()->getId(),
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
						$request->getSession(),
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
	 * Get the submission for this row (already authorized)
	 * @return Submission
	 */
	function getSubmission() {
		return $this->_submission;
	}

	/**
	 * Get the publication for this row (already authorized)
	 * @return Publication
	 */
	function getPublication() {
		return $this->_publication;
	}
}

