<?php

/**
 * @file classes/signoff/SignoffDAO.inc.php
 *
 * Copyright (c) 2000-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffDAO
 * @ingroup signoff
 * @see Signoff
 *
 * @brief Operations for retrieving and modifying Signoff objects.
 */


import('lib.pkp.classes.signoff.PKPSignoffDAO');

class SignoffDAO extends PKPSignoffDAO {
	/**
	 * Constructor
	 */
	function SignoffDAO() {
		parent::PKPSignoffDAO();
	}


	//
	// Public methods.
	//
	/**
	 * Get the related stage id by symbolic.
	 * @return int
	 */
	function getStageIdBySymbolic($symbolic) {
		switch ($symbolic) {
			case 'SIGNOFF_COPYEDITING':
				return WORKFLOW_STAGE_ID_EDITING;
				break;
			case 'SIGNOFF_PROOFING':
				return WORKFLOW_STAGE_ID_PRODUCTION;
				break;
			default:
				assert(false);
		}
	}
}

?>
