<?php

/**
 * @file classes/file/SimpleMonographFileManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SimpleMonographFileManager
 * @ingroup file
 *
 * @brief Helper class for simple monograph file management tasks. Simple
 * 	monograph files are not backed in the usual monograph_files manner
 * 	and are not versioned.
 *
 * Monograph directory structure:
 * [monograph id]/simple
 */

import('classes.file.BaseMonographFileManager');

class SimpleMonographFileManager extends BaseMonographFileManager {
	/**
	 * Constructor.
	 */
	function SimpleMonographFileManager($pressId, $monographId) {
		parent::BaseMonographFileManager($pressId, $monographId);
	}

	/**
	 * Get the storage directory for simple monograph files.
	 */
	function getBasePath() {
		return parent::getBasePath() . 'simple/';
	}
}

?>
