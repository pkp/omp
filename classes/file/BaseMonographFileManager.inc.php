<?php

/**
 * @file classes/file/BaseMonographFileManager.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class BaseMonographFileManager
 * @ingroup file
 *
 * @brief Base helper class for monograph file management tasks.
 *
 * Monograph directory structure:
 * [monograph id]/note
 * [monograph id]/public
 * [monograph id]/submission
 * [monograph id]/submission/original
 * [monograph id]/submission/review
 * [monograph id]/submission/review/attachment
 * [monograph id]/submission/editor
 * [monograph id]/submission/copyedit
 * [monograph id]/submission/layout
 * [monograph id]/attachment
 */

import('file.PressFileManager');

class BaseMonographFileManager extends PressFileManager {
	/** @var $_monographId int */
	var $_monographId;

	/**
	 * Constructor.
	 * @param $pressId int
	 * @param $monographId int
	 */
	function BaseMonographFileManager($pressId, $monographId) {
		parent::PressFileManager($pressId);
		$this->_monographId = (int) $monographId;
	}


	//
	// Public methods
	//
	/**
	 * Get the base path for file storage.
	 */
	function getBasePath() {
		return parent::getBasePath() . '/monographs/' . $this->_monographId . '/';
	}

	/**
	 * Get the monograph ID that this manager operates upon.
	 */
	function getMonographId() {
		return $this->_monographId;
	}
}

?>
