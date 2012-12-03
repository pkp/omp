<?php

/**
 * @file classes/press/PressSettingsDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
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
	 * Get the settings cache for a given press ID
	 * @param $pressId
	 * @return array
	 */
	function &_getCache($pressId) {
		static $settingCache;
		if (!isset($settingCache)) {
			$settingCache = array();
		}
		if (!isset($settingCache[$pressId])) {
			$cacheManager = CacheManager::getManager();
			$settingCache[$pressId] = $cacheManager->getCache(
				'pressSettings', $pressId,
				array($this, '_cacheMiss')
			);
		}
		return $settingCache[$pressId];
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
}

?>
