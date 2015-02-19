<?php

/**
 * @file classes/press/PressSettingsDAO.inc.php
 *
 * Copyright (c) 2014-2015 Simon Fraser University Library
 * Copyright (c) 2003-2015 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressSettingsDAO
 * @ingroup press
 *
 * @brief Operations for retrieving and modifying press settings.
 */

import('lib.pkp.classes.db.SettingsDAO');

class PressSettingsDAO extends SettingsDAO {
	/**
	 * Constructor
	 */
	function PressSettingsDAO() {
		parent::SettingsDAO();
	}

	/**
	 * Get the settings table name.
	 * @return string
	 */
	protected function _getTableName() {
		return 'press_settings';
	}

	/**
	 * Get the primary key column name.
	 */
	protected function _getPrimaryKeyColumn() {
		return 'press_id';
	}

	/**
	 * Get the cache name.
	 */
	protected function _getCacheName() {
		return 'pressSettings';
	}

}

?>
