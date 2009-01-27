<?php

/**
 * @file classes/group/GroupDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GroupDAO
 * @ingroup group
 * @see Group
 *
 * @brief Operations for retrieving and modifying Group objects.
 */

// $Id$


import ('group.Group');

class GroupDAO extends DAO {
	/**
	 * Retrieve a group by ID.
	 * @param $groupId int
	 * @return Group
	 */
	function &getGroup($groupId) {
		$result = &$this->retrieve(
			'SELECT * FROM groups WHERE group_id = ?', $groupId
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner = &$this->_returnGroupFromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		unset($result);
		return $returner;
	}

	/**
	 * Get all groups for a press.
	 * @param $pressId int
	 * @param $context int (optional)
	 * @param $rangeInfo object RangeInfo object (optional)
	 * @return array
	 */
	function &getGroups($pressId, $context = null, $rangeInfo = null) {
		$params = array($pressId);
		if ($context !== null) $params[] = $context;

		$result =& $this->retrieveRange(
			'SELECT * FROM groups WHERE press_id = ? ' . ($context!==null?'AND context = ? ':'') . 'ORDER BY context, seq',
			$params, $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_returnGroupFromRow');
		return $returner;
	}

	/**
	 * Get the list of fields for which locale data is stored.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('title');
	}

	/**
	 * Internal function to return a Group object from a row.
	 * @param $row array
	 * @return Group
	 */
	function &_returnGroupFromRow(&$row) {
		$group = new Group();
		$group->setGroupId($row['group_id']);
		$group->setAboutDisplayed($row['about_displayed']);
		$group->setSequence($row['seq']);
		$group->setContext($row['context']);
		$group->setPressId($row['press_id']);
		$this->getDataObjectSettings('group_settings', 'group_id', $row['group_id'], $group);

		HookRegistry::call('GroupDAO::_returnGroupFromRow', array(&$group, &$row));

		return $group;
	}

	/**
	 * Update the settings for this object
	 * @param $group object
	 */
	function updateLocaleFields(&$group) {
		$this->updateDataObjectSettings('group_settings', $group, array(
			'group_id' => $group->getGroupId()
		));
	}

	/**
	 * Insert a new board group.
	 * @param $group Group
	 */	
	function insertGroup(&$group) {
		$this->update(
			'INSERT INTO groups
				(seq, press_id, about_displayed, context)
				VALUES
				(?, ?, ?, ?)',
			array(
				$group->getSequence() == null ? 0 : $group->getSequence(),
				$group->getPressId(),
				$group->getAboutDisplayed(),
				$group->getContext()
			)
		);

		$group->setGroupId($this->getInsertGroupId());
		$this->updateLocaleFields($group);
		return $group->getGroupId();
	}

	/**
	 * Update an existing board group.
	 * @param $group Group
	 */
	function updateGroup(&$group) {
		$returner = $this->update(
			'UPDATE groups
				SET
					seq = ?,
					press_id = ?,
					about_displayed = ?,
					context = ?
				WHERE group_id = ?',
			array(
				$group->getSequence(),
				$group->getPressId(),
				$group->getAboutDisplayed(),
				$group->getContext(),
				$group->getGroupId()
			)
		);
		$this->updateLocaleFields($group);
		return $returner;
	}

	/**
	 * Delete a board group, including membership info
	 * @param $press Group
	 */
	function deleteGroup(&$group) {
		return $this->deleteGroupById($group->getGroupId());
	}

	/**
	 * Delete a board group, including membership info
	 * @param $groupId int
	 */
	function deleteGroupById($groupId) {
		$groupMembershipDao = &DAORegistry::getDAO('GroupMembershipDAO');
		$groupMembershipDao->deleteMembershipByGroupId($groupId);
		$this->update('DELETE FROM group_settings WHERE group_id = ?', $groupId);
		return $this->update('DELETE FROM groups WHERE group_id = ?', $groupId);
	}

	/**
	 * Delete board groups by press ID, including membership info
	 * @param $pressId int
	 */
	function deleteGroupsByPressId($pressId) {
		$groups =& $this->getGroups($pressId);
		while ($group =& $groups->next()) {
			$this->deleteGroup($group);
		}
	}

	/**
	 * Sequentially renumber board groups in their sequence order, optionally by press.
	 * @param $pressId int
	 */
	function resequenceGroups($pressId = null) {
		$result = &$this->retrieve(
			'SELECT group_id FROM groups ' .
			($pressId !== null?'WHERE press_id = ?':'') .
			'ORDER BY seq',
			$pressId
		);

		for ($i=1; !$result->EOF; $i++) {
			list($groupId) = $result->fields;
			$this->update(
				'UPDATE groups SET seq = ? WHERE group_id = ?',
				array(
					$i,
					$groupId
				)
			);

			$result->moveNext();
		}

		$result->close();
		unset($result);
	}

	/**
	 * Get the ID of the last inserted board group.
	 * @return int
	 */
	function getInsertGroupId() {
		return $this->getInsertId('groups', 'group_id');
	}
}

?>
