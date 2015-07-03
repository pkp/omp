<?php
/**
 * @file classes/security/authorization/internal/SeriesAssignmentRule.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2000-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SeriesAssignmentRule
 * @ingroup security_authorization_internal
 *
 * @brief Class to check if there is an assignment
 * between user and a serie.
 *
 */

class SeriesAssignmentRule {

	//
	// Public static methods.
	//
	/**
	 * Check if a series editor user is assigned to a series.
	 * @param $pressId
	 * @param $seriesId
	 * @param $userId
	 * @return boolean
	 */
	function effect($pressId, $seriesId, $userId) {
		$seriesEditorsDao = DAORegistry::getDAO('SeriesEditorsDAO');
		if ($seriesEditorsDao->editorExists($pressId, $seriesId, $userId)) {
			return true;
		} else {
			return false;
		}
	}
}

?>
