<?php

/**
 * @file controllers/grid/files/proof/ApprovedProofFilesGridCellProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ApprovedProofFilesGridCellProvider
 * @ingroup controllers_grid_files_proof
 *
 * @brief Cell provider to retrieve the approved proof files grid data
 */

import('lib.pkp.classes.controllers.grid.DataObjectGridCellProvider');

class ApprovedProofFilesGridCellProvider extends DataObjectGridCellProvider {
	/** @var $currency string */
	var $currency;

	/**
	 * Constructor
	 */
	function ApprovedProofFilesGridCellProvider($currency) {
		$this->currency = $currency;
		parent::DataObjectGridCellProvider();
	}

	//
	// Template methods from GridCellProvider
	//
	/**
	 * Extracts variables for a given column from a data element
	 * so that they may be assigned to template before rendering.
	 * @param $row GridRow
	 * @param $column GridColumn
	 * @return array
	 */
	function getTemplateVarsFromRowColumn($row, $column) {
		switch ($column->getId()) {
			case 'name':
				$approvedProofFile =& $row->getData();
				return array('label' => $approvedProofFile->getLocalizedName());
			case 'approved':
				$approvedProofFile =& $row->getData();
				return array('status' => $approvedProofFile->getViewable()?'completed':'new');
			case 'price':
				$approvedProofFile =& $row->getData();
				if ($approvedProofFile->getSalesType() == null) {
					return array('label' => __('payment.directSales.notSet'));
				}
				$price = $approvedProofFile->getDirectSalesPrice();
				if ($price === null) $label = __('payment.directSales.notAvailable');
				elseif ($price == '0') $label = __('payment.directSales.openAccess');
				else $label = __('payment.directSales.amount', array('amount' => $price, 'currency' => $this->currency));

				return array('label' => $label);
			default:
				assert(false);
		}
	}
}

?>
