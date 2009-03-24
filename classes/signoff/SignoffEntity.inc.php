<?php

/**
 * @file classes/signoff/SignoffEntity.inc.php
 *
 * Copyright (c) 2003-2008 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SignoffEntity
 * @ingroup signoff
 * @see SignoffEntityDAO
 *
 * @brief Describes a signoff entity. Signoff entities can be groups, roles, and users.
 */

// $Id$

define('SIGNOFF_ENTITY_TYPE_GROUP',	1);
define('SIGNOFF_ENTITY_TYPE_USER',	2);
define('SIGNOFF_ENTITY_TYPE_ROLE',	4);

class SignoffEntity extends DataObject {
	//
	// Get/set methods
	//

	/**
	 * get the entity type
	 * @return int
	 */
	function getEntityType() {
		return $this->getData('entityType');
	}

	/**
	 * set the entity type
	 * @param $entityType int
	 */
	function setEntityType($entityType) {
		return $this->setData('entityType', $entityType);
	}	

	/**
	 * Get entity id.
	 * @return int
	 */
	function getEntityId() {
		return $this->getData('entityId');
	}

	/**
	 * Set entity id.
	 * @param $entityId int
	 */
	function setEntityId($entityId) {
		return $this->setData('entityId', $entityId);
	}

	/**
	 * Get press id.
	 * @return int
	 */
	function getPressId() {
		return $this->getData('pressId');
	}

	/**
	 * Set press id.
	 * @param $pressId int
	 */
	function setPressId($pressId) {
		return $this->setData('pressId', $pressId);
	}

	/**
	 * Get signoff event type.
	 * @return int
	 */
	function getEventType() {
		return $this->getData('eventType');
	}

	/**
	 * Set signoff event type.
	 * @param $eventType int
	 */
	function setEventType($eventType) {
		return $this->setData('eventType', $eventType);
	}

	/**
	 * Get signoff event id.
	 * @return int
	 */
	function getEventId() {
		return $this->getData('eventId');
	}

	/**
	 * Set signoff event id.
	 * @param $eventId int
	 */
	function setEventId($eventId) {
		return $this->setData('eventId', $eventId);
	}

	/**
	 * Get passing vote percentage.
	 * @return int
	 */
	function getVote() {
		return $this->getData('vote');
	}

	/**
	 * Set passing vote percentage.
	 * @param $vote int
	 */
	function setVote($vote) {
		return $this->setData('vote', $vote);
	}

}

?>
