<?php

/**
 * @file classes/press/SocialMediaDAO.inc.php
 *
 * Copyright (c) 2003-2012 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class SocialMediaDAO
 * @ingroup press
 * @see SocialMedia
 *
 * @brief Operations for retrieving and modifying SocialMedia objects.
 */


import ('classes.press.SocialMedia');

class SocialMediaDAO extends DAO {
	/**
	 * Constructor
	 */
	function SocialMediaDAO() {
		parent::DAO();
	}

	/**
	 * Retrieve a media object by ID.
	 * @param $socialMediaId int
	 * @param $pressId int optional
	 * @return SocialMedia
	 */
	function &getById($socialMediaId, $pressId = null) {
		$params = array((int) $socialMediaId);
		if ($pressId) $params[] = (int) $pressId;

		$result =& $this->retrieve(
			'SELECT *
			FROM social_media
			WHERE social_media_id = ?
			' . ($pressId?' AND press_id = ?':''),
			$params
		);

		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}

		$result->Close();
		unset($result);

		return $returner;
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return SocialMedia
	 */
	function newDataObject() {
		return new SocialMedia();
	}

	/**
	 * Internal function to return a SocialMedia object from a row.
	 * @param $row array
	 * @return SocialMedia
	 */
	function _fromRow(&$row) {
		$socialMedia = $this->newDataObject();

		$socialMedia->setId($row['social_media_id']);
		$socialMedia->setPressId($row['press_id']);
		$socialMedia->setCode($row['code']);

		$this->getDataObjectSettings('social_media_settings', 'social_media_id', $row['social_media_id'], $socialMedia);

		HookRegistry::call('SocialMediaDAO::_fromRow', array(&$socialMedia, &$row));

		return $socialMedia;
	}

	/**
	 * Get the list of fields for which data can be localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('platform');
	}

	/**
	 * Update the localized fields for this object.
	 * @param $socialMedia object
	 */
	function updateLocaleFields(&$socialMedia) {
		$this->updateDataObjectSettings(
			'social_media_settings', $socialMedia,
			array(
				'social_media_id' => $socialMedia->getId()
			)
		);
	}

	/**
	 * Insert a new object.
	 * @param $socialMedia SocialMedia
	 * @return int ID of the inserted link.
	 */
	function insertObject(&$socialMedia) {
		$this->update(
			'INSERT INTO social_media
				(press_id, code)
				VALUES
				(?, ?)',
			array(
				(int) $socialMedia->getPressId(),
				$socialMedia->getCode()
			)
		);

		$socialMedia->setId($this->getInsertSocialMediaId());
		$this->updateLocaleFields($socialMedia);
		return $socialMedia->getId();
	}

	/**
	 * Update an existing link.
	 * @param $socialMedia SocialMedia
	 */
	function updateObject($socialMedia) {
		$returner = $this->update(
			'UPDATE	social_media
			SET	press_id = ?,
				code = ?
			WHERE	social_media_id = ?',
			array(
				(int) $socialMedia->getPressId(),
				$socialMedia->getCode(),
				(int) $socialMedia->getId()
			)
		);
		$this->updateLocaleFields($socialMedia);
		return $returner;
	}

	/**
	 * Delete an object.
	 * @param $socialMedia SocialMedia
	 */
	function deleteObject(&$socialMedia) {
		return $this->deleteById(
			$socialMedia->getId(),
			$socialMedia->getPressId()
		);
	}

	/**
	 * Delete an object by ID.
	 * @param $socialMediaId int
	 * @param $pressId int optional
	 */
	function deleteById($socialMediaId, $pressId = null) {
		$params = array((int) $socialMediaId);
		if ($pressId) $params[] = (int) $pressId;

		$this->update(
			'DELETE FROM social_media
			WHERE social_media_id = ?
				' . ($pressId?' AND press_id = ?':''),
			$params
		);

		if ($this->getAffectedRows()) {
			return $this->update(
				'DELETE FROM social_media_settings WHERE social_media_id = ?',
				array((int) $socialMediaId)
			);
		}
	}

	/**
	 * Delete social media items by press ID
	 * NOTE: This does not delete dependent entries. It is intended
	 * to be called only when deleting a press.
	 * @param $pressId int
	 */
	function deleteByPressId($pressId) {
		$socialMediaObjects =& $this->getByPressId($pressId);
		while ($socialMedia =& $socialMediaObjects->next()) {
			$this->deleteObject($socialMedia, $pressId);
			unset($socialMedia);
		}
	}

	/**
	 * Retrieve all media objects for a press.
	 * @return DAOResultFactory containing SocialMedia objects
	 */
	function &getByPressId($pressId, $rangeInfo = null) {
		$params = array((int) $pressId);

		$result =& $this->retrieveRange(
			'SELECT *
			FROM social_media
			WHERE press_id = ?',
			$params
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow');
		return $returner;
	}

	/**
	 * Get the ID of the last inserted link.
	 * @return int
	 */
	function getInsertSocialMediaId() {
		return $this->getInsertId('social_media', 'social_media_id');
	}
}
?>
