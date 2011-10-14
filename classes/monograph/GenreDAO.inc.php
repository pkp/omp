<?php
/**
 * @file classes/monograph/GenreDAO.inc.php
 *
 * Copyright (c) 2003-2011 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class GenreDAO
 * @ingroup monograph
 * @see Genre
 *
 * @brief Operations for retrieving and modifying Genre objects.
 */


import('classes.monograph.Genre');
import('classes.press.DefaultSettingDAO');

class GenreDAO extends DefaultSettingDAO {
	/**
	 * @see DefaultSettingsDAO::getPrimaryKeyColumnName()
	 */
	function getPrimaryKeyColumnName() {
		return 'genre_id';
	}

	/**
	 * Retrieve a genre by type id.
	 * @param $genreId int
	 * @return Genre
	 */
	function &getById($genreId, $pressId = null){
		$sqlParams = array((int)$genreId);
		if ($pressId) {
			$sqlParams[] = (int)$pressId;
		}

		$result =& $this->retrieve('SELECT * FROM genres WHERE genre_id = ?'. ($pressId ? ' AND press_id = ?' : ''), $sqlParams);
		$returner = null;
		if ($result->RecordCount() != 0) {
			$returner =& $this->_fromRow($result->GetRowAssoc(false));
		}
		$result->Close();
		return $returner;
	}

	/**
	 * Retrieve all genres
	 * @param $pressId int
	 * @param $enabledOnly boolean optional
	 * @param $rangeInfo object optional
	 * @return DAOResultFactory containing matching genres
	 */
	function &getEnabledByPressId($pressId, $rangeInfo = null) {
		$result =& $this->retrieveRange(
			'SELECT * FROM genres WHERE enabled = ? AND press_id = ?', array(1, $pressId), $rangeInfo
		);

		$returner = new DAOResultFactory($result, $this, '_fromRow', array('id'));
		return $returner;
	}

	/**
	 * Get a list of field names for which data is localized.
	 * @return array
	 */
	function getLocaleFieldNames() {
		return array('name', 'designation');
	}

	/**
	 * Update the settings for this object
	 * @param $genre object
	 */
	function updateLocaleFields(&$genre) {
		$this->updateDataObjectSettings('genre_settings', $genre, array(
			'genre_id' => $genre->getId()
		));
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Genre
	 */
	function newDataObject() {
		return new Genre();
	}

	/**
	 * Internal function to return a Genre object from a row.
	 * @param $row array
	 * @return Genre
	 */
	function &_fromRow(&$row) {
		$genre = $this->newDataObject();
		$genre->setId($row['genre_id']);
		$genre->setPressId($row['press_id']);
		$genre->setSortable($row['sortable']);
		$genre->setCategory($row['category']);

		$this->getDataObjectSettings('genre_settings', 'genre_id', $row['genre_id'], $genre);

		HookRegistry::call('GenreDAO::_fromRow', array(&$genre, &$row));

		return $genre;
	}

	/**
	 * Insert a new genre.
	 * @param $genre Genre
	 */
	function insertObject(&$genre) {
		$this->update(
			'INSERT INTO genres
				(sortable, press_id, category)
			VALUES
				(?, ?, ?)',
			array(
				$genre->getSortable() ? 1 : 0,
				(int) $genre->getPressId(),
				$genre->getCategory()
			)
		);

		$genre->setId($this->getInsertGenreId());

		$this->updateLocaleFields($genre);

		return $genre->getId();
	}

	/**
	 * Update an existing genre.
	 * @param $genre Genre
	 */
	function updateObject(&$genre) {
		$this->updateLocaleFields($genre);
	}

	/**
	 * Delete a genre by id.
	 * @param $genre Genre
	 */
	function deleteObject($genre) {
		return $this->deleteById($genre->getId());
	}

	/**
	 * Soft delete a genre by id.
	 * @param $entryId int
	 */
	function deleteById($entryId) {
		return $this->update(
			'UPDATE genres SET enabled = ? WHERE genre_id = ?', array(0, (int) $entryId)
		);
	}

	/**
	 * Get the ID of the last inserted genre.
	 * @return int
	 */
	function getInsertGenreId() {
		return $this->getInsertId('genres', 'genre_id');
	}

	/**
	 * Get the name of the settings table.
	 * @return string
	 */
	function getSettingsTableName() {
		return 'genre_settings';
	}

	/**
	 * Get the name of the main table for this setting group.
	 * @return string
	 */
	function getTableName() {
		return 'genres';
	}

	/**
	 * Get the default type constant.
	 * @return int
	 */
	function getDefaultType() {
		return DEFAULT_SETTING_GENRES;
	}

	/**
	 * Get the path of the setting data file.
	 * @return string
	 */
	function getDefaultBaseFilename() {
		return 'registry/genres.xml';
	}

	/**
	 * Install genres from an XML file.
	 * @param $pressId int
	 * @return boolean
	 */
	function installDefaultBase($pressId) {
		$xmlDao = new XMLDAO();

		$data = $xmlDao->parseStruct($this->getDefaultBaseFilename(), array('genre'));
		if (!isset($data['genre'])) return false;

		foreach ($data['genre'] as $entry) {
			$attrs = $entry['attributes'];
			$this->update(
				'INSERT INTO genres
				(entry_key, sortable, press_id, category)
				VALUES
				(?, ?, ?, ?)',
				array($attrs['key'], $attrs['sortable'] ? 1 : 0, $pressId, $attrs['category'])
			);
		}
		return true;
	}

	/**
	 * Get setting names and values.
	 * @param $node XMLNode
	 * @param $locale string
	 * @return array
	 */
	function &getSettingAttributes($node = null, $locale = null) {

		if ($node == null) {
			$settings = array('name', 'designation');
		} else {
			$localeKey = $node->getAttribute('localeKey');
			$sortable = $node->getAttribute('sortable');

			$designation = $sortable ? GENRE_SORTABLE_DESIGNATION : Locale::translate($localeKey.'.designation', array(), $locale);

			$settings = array(
				'name' => Locale::translate($localeKey, array(), $locale),
				'designation' => $designation
			);
		}
		return $settings;
	}
}

?>
