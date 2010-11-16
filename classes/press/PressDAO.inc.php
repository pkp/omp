<?php
/**
 * @file classes/press/PressDAO.inc.php
 *
 * Copyright (c) 2003-2010 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressDAO
 * @ingroup press
 * @see Press
 *
 * @brief Operations for retrieving and modifying Press objects.
 */

import('classes.press.Press');

class PressDAO extends DAO
{
	/**
	 * Retrieve a press by press ID.
	 * @param $pressId int
	 * @return Press
	 */
	function getPress($pressId){
		$result =& $this->retrieve('SELECT * FROM presses WHERE press_id = ?', $pressId);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnPressFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve the IDs and names of all presses in an associative array.
	 * @return array
	 */
	function &getPressNames() {
		$presses = array();

		$pressIterator =& $this->getPresses();
		while ($press =& $pressIterator->next()) {
			$presses[$press->getId()] = $press->getLocalizedName();
			unset($press);
		}
		unset($pressIterator);

		return $presses;
	}

	/**
	 * Internal function to return a Press object from a row.
	 * @param $row array
	 * @return Press
	 */
	function &_returnPressFromRow(&$row) {
		$press = new Press();
		$press->setId($row['press_id']);
		$press->setPath($row['path']);
		$press->setSequence($row['seq']);
		$press->setEnabled($row['enabled']);
		$press->setPrimaryLocale($row['primary_locale']);

		HookRegistry::call('PressDAO::_returnPressFromRow', array(&$press, &$row));

		return $press;
	}

	/**
	 * Check if a press exists with a specified path.
	 * @param $path the path for the press
	 * @return boolean
	 */
	function pressExistsByPath($path) {
		$result =& $this->retrieve(
			'SELECT COUNT(*) FROM presses WHERE path = ?', $path
		);
		$returner = isset($result->fields[0]) && $result->fields[0] == 1 ? true : false;

		$result->Close();
		unset($result);

		return $returner;
	}


	/**
	 * Retrieve a press by path.
	 * @param $path string
	 * @return Press
	 */
	function &getPressByPath($path) {
		$returner = null;
		$result =& $this->retrieve(
			'SELECT * FROM presses WHERE path = ?', $path
		);

		if ($result->RecordCount() != 0) {
			$returner =& $this->_returnPressFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}
	function getPrimaryLocale(){
		return 'en_US';
	}

	/**
	 * Retrieve all presses.
	 * @return DAOResultFactory containing matching presses
	 */
	function &getPresses($rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM presses ORDER BY seq',
			false, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnPressFromRow');
		return $returner;
	}

	/**
	 * Insert a new press.
	 * @param $press Press
	 */
	function insertPress(&$press) {
		$this->update(
			'INSERT INTO presses
				(path, seq, enabled, primary_locale)
				VALUES
				(?, ?, ?, ?)',
			array(
				$press->getPath(),
				$press->getSequence() == null ? 0 : $press->getSequence(),
				$press->getEnabled() ? 1 : 0,
				$press->getPrimaryLocale()
			)
		);

		$press->setId($this->getInsertPressId());
		return $press->getId();
	}

	/**
	 * Update an existing press.
	 * @param $press Press
	 */
	function updatePress(&$press) {
		return $this->update(
			'UPDATE presses
				SET
					path = ?,
					seq = ?,
					enabled = ?,
					primary_locale = ?
				WHERE press_id = ?',
			array(
				$press->getPath(),
				$press->getSequence(),
				$press->getEnabled() ? 1 : 0,
				$press->getPrimaryLocale(),
				$press->getId()
			)
		);
	}

	/**
	 * Retrieve all enabled presses
	 * @return array Presses ordered by sequence
	 */
	function &getEnabledPresses() {
		$result =& $this->retrieve(
			'SELECT * FROM presses WHERE enabled=1 ORDER BY seq'
		);

		$resultFactory = new DAOResultFactory($result, $this, '_returnPressFromRow');
		return $resultFactory;
	}

	/**
	 * Get the ID of the last inserted press.
	 * @return int
	 */
	function getInsertPressId() {
		return $this->getInsertId('presses', 'press_id');
	}

	/**
	 * Delete a press by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $pressId int
	 */
	function deletePressById($pressId) {
		$pressSettingsDao =& DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->deleteSettingsByPress($pressId);

		$seriesDao =& DAORegistry::getDAO('SeriesDAO');
		$seriesDao->deleteByPressId($pressId);

		$emailTemplateDao =& DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->deleteEmailTemplatesByPress($pressId);

		$monographDAO =& DAORegistry::getDAO('MonographDAO');
		$monographDAO->deleteMonographsByPressId($pressId);

		$userGroupDao =& DAORegistry::getDAO('UserGroupDAO');
		$userGroupDao->deleteAssignmentsByContextId($pressId);
		$userGroupDao->deleteByContextId($pressId);

		$pluginSettingsDao =& DAORegistry::getDAO('PluginSettingsDAO');
		$pluginSettingsDao->deleteSettingsByPressId($pressId);

		$reviewFormDao =& DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormDao->deleteByAssocId(ASSOC_TYPE_PRESS, $pressId);

		return $this->update(
			'DELETE FROM presses WHERE press_id = ?', $pressId
		);
	}

	/**
	 * Sequentially renumber each press according to their sequence order.
	 */
	function resequencePresses() {
		$result =& $this->retrieve(
			'SELECT press_id FROM presses ORDER BY seq'
		);

		for ($i=1; !$result->EOF; $i++) {
			list($pressId) = $result->fields;
			$this->update(
				'UPDATE presses SET seq = ? WHERE press_id = ?',
				array(
					$i,
					$pressId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

}

?>
