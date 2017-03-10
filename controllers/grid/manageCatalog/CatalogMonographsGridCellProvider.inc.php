<?php

/**
 * @file controllers/grid/manageCatalog/CatalogMonographsGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2017 Simon Fraser University
 * Copyright (c) 2000-2017 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class CatalogMonographsGridCellProvider
 * @ingroup controllers_grid_manageCatalog
 *
 * @brief Base class for a cell provider that can retrieve labels and feature checkboxes for
 * catalog monographs.
 */

import('lib.pkp.classes.controllers.grid.GridCellProvider');

class CatalogMonographsGridCellProvider extends GridCellProvider {

	/** @var int */
	private $_assocType;

	/** @var int */
	private $_assocId;

	/** @var array */
	private $_requestArgs;


	/**
	 * Constructor.
	 * @param $assocType int
	 * @param $assocId int
	 * @param $requestArgs array Grid request arguments.
	 */
	function __construct($assocType, $assocId, $requestArgs) {
		parent::__construct();
		$this->_assocType = $assocType;
		$this->_assocId = $assocId;
		$this->_requestArgs = $requestArgs;
	}


	//
	// Template methods from GridCellProvider
	//
	/**
	 * @copydoc GridCellProvider::getTemplateVarsFromRowColumn()
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		$monograph = $row->getData();
		switch ($column->getId()) {
			case 'id':
				return array('label' => $monograph->getId());
			case 'title':
				// Delegate to the submission grid cell provider that holds the logic
				// for the submission title plus author name.
				$user = Application::getRequest()->getUser();
				import('lib.pkp.controllers.grid.submissions.SubmissionsListGridCellProvider');
				$submissionsListCellProvider = new SubmissionsListGridCellProvider($user);
				return $submissionsListCellProvider->getTemplateVarsFromRowColumn($row, $column);
			case 'isFeatured':
				$featureDao = DAORegistry::getDAO('FeatureDAO');
				return array('selected' => $featureDao->isFeatured($monograph->getId(), $this->_assocType, $this->_assocId));
			case 'isNewRelease':
				$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
				return array('selected' => $newReleaseDao->isNewRelease($monograph->getId(), $this->_assocType, $this->_assocId));
		}

		return parent::getTemplateVarsFromRowColumn($row, $column);
	}

	/**
	 * @see GridCellProvider::getCellActions()
	 */
	function getCellActions($request, $row, $column) {
		import('lib.pkp.classes.linkAction.request.AjaxAction');
		$monograph = $row->getData();
		$router = $request->getRouter();
		$currentState = $toggleType = null;
		switch ($column->getId()) {
			case 'isFeatured':
				$featureDao = DAORegistry::getDAO('FeatureDAO');
				$currentState = $featureDao->isFeatured($monograph->getId(), $this->_assocType, $this->_assocId);
				$toggleType = 'setFeatured';
				break;
			case 'isNewRelease':
				$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
				$currentState = $newReleaseDao->isNewRelease($monograph->getId(), $this->_assocType, $this->_assocId);
				$toggleType = 'setNewRelease';
				break;
		}

		if ($toggleType) {
			return array(new LinkAction(
				'toggleFeature',
				new AjaxAction(
					$router->url(
						$request, null, null, 'toggle', null,
						array_merge(array(
							'rowId' => $monograph->getId(),
							'assocType' => $this->_assocType,
							'assocId' => $this->_assocId,
							'toggleType' => $toggleType,
							'newState' => $currentState?0:1,
						), $this->_requestArgs)
					)
				)
			));
		} else {
			return parent::getCellActions($request, $row, $column);
		}
	}
}

?>
