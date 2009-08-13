<?php

/**
 * @file classes/signoff/SignoffEntityDAO.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffEntityDAO
 * @ingroup signoff
 * @see SignoffEntity
 *
 * @brief Operations for retrieving and modifying SignoffEntity objects.
 */

// $Id$

define('SIGNOFF_ENTITY_SIGNED_FALSE', 0);
define('SIGNOFF_ENTITY_SIGNED_TRUE', 1);

import('signoff.SignoffEntity');

class SignoffEntityDAO extends DAO {

	/**
	 * Retrieve a signoff entity by ID.
	 * @param $signoffEntityId int
	 * @return SignoffEntity
	 */
	function remove($eventType, $eventId, $pressId, $entityType, $entityId) {
		$signoffEntity = $this->newDataObject();
		$signoffEntity->setEntityType($entityType);
		$signoffEntity->setEntityId($entityId);
		$signoffEntity->setPressId($pressId);
		$signoffEntity->setEventType($eventType);
		$signoffEntity->setEventId($eventId);

		$this->deleteObject($signoffEntity);

	}
	/**
	 * Retrieve a signoff entity by ID.
	 * @param $signoffEntityId int
	 * @return SignoffEntity
	 */
	function &get($eventType, $eventId, $pressId, $entityType = null, $entityId = null) {

		$sqlParams = array($eventType, $eventId, $pressId);
		$queryExtra = '';

		if (isset($entityType)) {
			$sqlParams[] = $entityType;
			$queryExtra .= ' AND entity_type = ?';
		}
		if (isset($entityId)) {
			$sqlParams[] = $entityId;
			$queryExtra .= ' AND entity_id = ?';
		}

		$result =& $this->retrieve(
			'SELECT * 
			FROM signoff_entities
			WHERE event_type = ? AND
				event_id = ? AND
				press_id = ?'.$queryExtra.'
			ORDER BY entity_type, entity_id',
				$sqlParams
		);

		$returner = null;
		while (!$result->EOF) {
			$returner[] =& $this->_fromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Fetch a list of the entities associated with a process for a press.
	 * @param $eventType int
	 * @param $eventId int
	 * @param $pressId int
	 * @return array
	 */
	function getEntitiesForEvent($eventType, $eventId, $pressId) {
		$entries =& $this->get($eventType, $eventId, $pressId);

		$groupDao =& DAORegistry::getDAO('GroupDAO');
		$userDao =& DAORegistry::getDAO('UserDAO');
		$roleDao =& DAORegistry::getDAO('RoleDAO');

		$returner = array(
				SIGNOFF_ENTITY_TYPE_GROUP => array(),
				SIGNOFF_ENTITY_TYPE_USER => array(),
				SIGNOFF_ENTITY_TYPE_ROLE => array()
				);
		if (isset($entries))
		foreach ($entries as $entry) {
			switch($entry->getEntityType()) {
			case SIGNOFF_ENTITY_TYPE_GROUP:
				$returner[SIGNOFF_ENTITY_TYPE_GROUP][] =& $groupDao->getGroup($entry->getEntityId(), ASSOC_TYPE_PRESS, $pressId);
				break;
			case SIGNOFF_ENTITY_TYPE_USER:
				$returner[SIGNOFF_ENTITY_TYPE_USER][] =& $userDao->getUser($entry->getEntityId(), ASSOC_TYPE_PRESS, $pressId);
				break;
			case SIGNOFF_ENTITY_TYPE_ROLE:
				$returner[SIGNOFF_ENTITY_TYPE_ROLE][] =& $roleDao->getRoleName($entry->getEntityId());
				break;
			}
		}
		return $returner;
	}

	/**
	 * Fetch a signoff by symbolic info, building it if needed.
	 * @param $symbolic string
	 * @param $assocType int
	 * @param $assocId int
	 * @return $signoff
	 */
	function build($eventType, $eventId, $pressId, $entityType, $entityId) {
		// If one exists, fetch and return.
		$signoffEntity =& $this->get($eventType, $eventId, $pressId, $entityType, $entityId);
		if ($signoffEntity) return $signoffEntity;

		// Otherwise, build one.
		unset($signoff);
		$signoffEntity = $this->newDataObject();
		$signoffEntity->setEntityType($entityType);
		$signoffEntity->setEntityId($entityId);
		$signoffEntity->setPressId($pressId);
		$signoffEntity->setEventType($eventType);
		$signoffEntity->setEventId($eventId);
		$this->insertObject($signoffEntity);
		return $signoffEntity;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SignoffEntity
	 */
	function newDataObject() {
		return new SignoffEntity();
	}

	/**
	 * Internal function to return an SignoffEntity object from a row.
	 * @param $row array
	 * @return SignoffEntity
	 */
	function _fromRow(&$row) {
		$signoffEntity = $this->newDataObject();

		$signoffEntity->setEntityType($row['entity_type']);
		$signoffEntity->setEntityId($row['entity_id']);
		$signoffEntity->setPressId($row['press_id']);
		$signoffEntity->setEventType($row['event_type']);
		$signoffEntity->setEventId($row['event_id']);
		$signoffEntity->setVote($row['vote']);

		return $signoffEntity;
	}

	/**
	 * Insert a new Signoff.
	 * @param $signoff Signoff
	 * @return int 
	 */
	function insertObject(&$signoffEntity) {
		$this->update(
				'INSERT INTO signoff_entities
				(entity_type, entity_id, press_id, event_type, event_id, vote)
				VALUES
				(?, ?, ?, ?, ?, ?)',
			array(
				$signoffEntity->getEntityType(),
				$signoffEntity->getEntityId(),
				$signoffEntity->getPressId(),
				$signoffEntity->getEventType(),
				$signoffEntity->getEventId(),
				$signoffEntity->getVote()
			)
		);
		return true;
	}

	/**
	 * Update an existing signoff entity entry.
	 * @param $signoffEntity SignoffEntity
	 * @return boolean
	 */
	function updateObject(&$signoffEntity) {
		$returner = $this->update(
			sprintf(
				'UPDATE signoffs
				SET entity_type = ?,
					entity_id = ?,
					press_id = ?,
					event_type = ?,
					event_id = ?,
					vote = ?,
				WHERE signoff_id = ?',
				$this->datetimeToDB($signoffEntity->getDateNotified()),
				$this->datetimeToDB($signoffEntity->getDateUnderway()),
				$this->datetimeToDB($signoffEntity->getDateCompleted()),
				$this->datetimeToDB($signoffEntity->getDateAcknowledged())
			),
			array(
				$signoffEntity->getSymbolic(),
				(int) $signoffEntity->getAssocType(),
				(int) $signoffEntity->getAssocId(),
				(int) $signoffEntity->getUserId(),
				$this->nullOrInt($signoffEntity->getFileId()),
				$this->nullOrInt($signoffEntity->getFileRevision()),
				(int) $signoffEntity->getId()
			)
		);
		return $returner;
	}

	function &getRequiredSignoffsByProcess($eventType, $eventId, $pressId) {

		$sqlParams = array($eventType, $eventId, $pressId);

		$sql = 'SELECT u.*
			FROM signoff_entities se
			LEFT JOIN group_memberships grp ON (grp.group_id = se.entity_id AND se.entity_type = '. SIGNOFF_ENTITY_TYPE_GROUP .')
			LEFT JOIN users u ON (grp.user_id=u.user_id OR (se.entity_id=u.user_id AND se.entity_type = '. SIGNOFF_ENTITY_TYPE_USER .'))
			LEFT JOIN signoff_processes sp ON (sp.event_id = se.event_id)
			LEFT JOIN workflow_signoffs ws ON (sp.process_id=ws.process_id AND u.user_id = ws.user_id)
			WHERE se.event_type = ? AND
				se.event_id = ? AND
				se.press_id = ? AND
				ws.user_id IS NULL
			ORDER BY u.last_name, u.first_name';

		$result =& $this->retrieve($sql, $sqlParams);

		$userDao =& DAORegistry::getDAO('UserDAO');

		$returner = null;

		while (!$result->EOF) {
			$returner[] =& $userDao->_returnUserFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}

		$result->Close();
		unset($result);

		return $returner;;
	}

	/**
	 * Get signoff entities entry.
	 * @param $signoffEntity SignoffEntity
	 * @return boolean
	 */
	function OLDgetSignoffUsers() {
	
		$sql = 'SELECT u.* 
			FROM signoff_entities se, users u, group_memberships grp
			WHERE (u.user_id = grp.user_id AND
				grp.group_id = se.entity_id AND
				se.entity_type='.SIGNOFF_ENTITY_TYPE_GROUP.') OR 
				(u.user_id = se.entity_id AND
				se.entity_type='.SIGNOFF_ENTITY_TYPE_USER.')
			ORDER BY u.last_name, u.first_name';

		$result =& $this->retrieve($sql);
		$userDao =& DAORegistry::getDAO('UserDAO');

		$returner = null;
		while (!$result->EOF) {
			$returner[] =& $userDao->_returnUserFromRow($result->GetRowAssoc(false));
			$result->moveNext();
		}
		$result->Close();
		unset($result);

		return $returner;;
	}

	/**
	 * Get signoff entities entry.
	 * @param $signoffEntity SignoffEntity
	 * @return boolean
	 */
	function getSignoffUsers($pressId, $reviewTypeId) {
		$result =& $this->retrieve('SELECT u.* 
			FROM signoff_entities se JOIN users u ON u.user_id = se.entity_id 
			WHERE se.entity_type='.SIGNOFF_ENTITY_TYPE_USER. '
				AND press_id = ? AND event_id = ? 
			ORDER BY u.last_name, u.first_name', 
			array($pressId, $reviewTypeId)
			);
		$userDao =& DAORegistry::getDAO('UserDAO');

		$returner = new DAOResultFactory($result, $userDao, '_returnUserFromRowWithData');
		return $returner;
	}
	
	/**
	 * Get signoff entities entry.
	 * @param $signoffEntity SignoffEntity
	 * @return boolean
	 */
	function getSignoffGroups($pressId, $reviewTypeId) {
		$result =& $this->retrieve('SELECT g.* 
			FROM signoff_entities se JOIN groups g ON g.group_id = se.entity_id 
			WHERE se.entity_type='.SIGNOFF_ENTITY_TYPE_GROUP . '
				AND press_id = ? AND event_id = ?', 
			array($pressId, $reviewTypeId)
			);
		$groupDao =& DAORegistry::getDAO('GroupDAO');

		$returner = new DAOResultFactory($result, $groupDao, '_returnGroupFromRow');
		return $returner;
	}	

	/**
	 * Delete a signoff entity entry.
	 * @param $signoffEntity SignoffEntity
	 * @return boolean
	 */
	function deleteObject($signoffEntity) {
		return $this->update('DELETE FROM signoff_entities 
					WHERE entity_id = ? AND 
						entity_type = ? AND
						press_id = ? AND
						event_type = ? AND
						event_id = ?',
					array(
						$signoffEntity->getEntityId(),
						$signoffEntity->getEntityType(),
						$signoffEntity->getPressId(),
						$signoffEntity->getEventType(),
						$signoffEntity->getEventId()
					));
	}

}
?>
