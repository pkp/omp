<?php
/**
 * @file classes/press/PressDAO.inc.php
 *
 * Copyright (c) 2014-2016 Simon Fraser University Library
 * Copyright (c) 2003-2016 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PressDAO
 * @ingroup press
 * @see Press
 *
 * @brief Operations for retrieving and modifying Press objects.
 */

import('classes.press.Press');
import('lib.pkp.classes.context.ContextDAO');

class PressDAO extends ContextDAO {
	/**
	 * Constructor
	 */
	function PressDAO() {
		parent::ContextDAO();
	}

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Press
	 */
	function newDataObject() {
		return new Press();
	}

	/**
	 * Internal function to return a Press object from a row.
	 * @param $row array
	 * @return Press
	 */
	function _fromRow($row) {
		$press = parent::_fromRow($row);
		$press->setPrimaryLocale($row['primary_locale']);
		$press->setEnabled($row['enabled']);
		HookRegistry::call('PressDAO::_fromRow', array(&$press, &$row));
		return $press;
	}

	/**
	 * Delete a press by ID, INCLUDING ALL DEPENDENT ITEMS.
	 * @param $pressId int
	 */
	function deleteById($pressId) {
		$pressSettingsDao = DAORegistry::getDAO('PressSettingsDAO');
		$pressSettingsDao->deleteById($pressId);

		$seriesDao = DAORegistry::getDAO('SeriesDAO');
		$seriesDao->deleteByPressId($pressId);

		$emailTemplateDao = DAORegistry::getDAO('EmailTemplateDAO');
		$emailTemplateDao->deleteEmailTemplatesByContext($pressId);

		$notificationStatusDao = DAORegistry::getDAO('NotificationStatusDAO');
		$notificationStatusDao->deleteByPressId($pressId);

		$monographDao = DAORegistry::getDAO('MonographDAO');
		$monographDao->deleteByContextId($pressId);

		$pluginSettingsDao = DAORegistry::getDAO('PluginSettingsDAO');
		$pluginSettingsDao->deleteByContextId($pressId);

		$reviewFormDao = DAORegistry::getDAO('ReviewFormDAO');
		$reviewFormDao->deleteByAssoc(ASSOC_TYPE_PRESS, $pressId);

		$featureDao = DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByAssoc(ASSOC_TYPE_PRESS, $pressId);

		$newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByAssoc(ASSOC_TYPE_PRESS, $pressId);

		parent::deleteById($pressId);
	}

	/**
	 * Delete the public IDs of all publishing objects in a press.
	 * @param $pressId int
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 */
	function deleteAllPubIds($pressId, $pubIdType) {
		// Keep this as a loop in case we add DOI support to other types later on.
		$pubObjectDaos = array('PublicationFormatDAO');
		foreach($pubObjectDaos as $daoName) {
			$dao = DAORegistry::getDAO($daoName);
			$dao->deleteAllPubIds($pressId, $pubIdType);
			unset($dao);
		}
	}

	//
	// Private functions
	//
	/**
	 * Get the table name for this context.
	 * @return string
	 */
	protected function _getTableName() {
		return 'presses';
	}

	/**
	 * Get the table name for this context's settings table.
	 * @return string
	 */
	protected function _getSettingsTableName() {
		return 'press_settings';
	}

	/**
	 * Get the name of the primary key column for this context.
	 * @return string
	 */
	protected function _getPrimaryKeyColumn() {
		return 'press_id';
	}
}

?>
