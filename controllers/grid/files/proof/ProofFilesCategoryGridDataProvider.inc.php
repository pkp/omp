<?php

/**
 * @file controllers/grid/files/proof/ProofFilesCategoryGridDataProvider.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ProofFilesCategoryDataProvider
 * @ingroup controllers_grid_files_proof
 *
 * @brief Provide access to proof files data for category grids.
 */

import('lib.pkp.controllers.grid.files.SubmissionFilesCategoryGridDataProvider');

class ProofFilesCategoryGridDataProvider extends SubmissionFilesCategoryGridDataProvider {
	/**
	 * @copydoc SubmissionFilesCategoryGridDataprovider()
	 */
	function ProofFilesCategoryGridDataProvider() {
		parent::SubmissionFilesCategoryGridDataProvider(SUBMISSION_FILE_PROOF);
	}


	//
	// Implement template methods from CategoryGridDataProvider
	//
	/**
	 * @copydoc CategoryGridDataProvider::loadCategoryData()
	 */
	function &loadCategoryData($request, $categoryDataElement, $filter = null, $reviewRound = null) {
		$categoryData = parent::loadCategoryData($request, $categoryDataElement, $filter, $reviewRound);
		return $categoryData;
	}
}

?>
