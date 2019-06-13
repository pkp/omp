<?php
/**
 * @file classes/press/PressDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
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
	/** @copydoc SchemaDAO::$schemaName */
	var $schemaName = 'context';

	/** @copydoc SchemaDAO::$tableName */
	var $tableName = 'presses';

	/** @copydoc SchemaDAO::$settingsTableName */
	var $settingsTableName = 'press_settings';

	/** @copydoc SchemaDAO::$primaryKeyColumn */
	var $primaryKeyColumn = 'press_id';

	/** @var array Maps schema properties for the primary table to their column names */
	var $primaryTableColumns = [
		'id' => 'press_id',
		'urlPath' => 'path',
		'enabled' => 'enabled',
		'seq' => 'seq',
		'primaryLocale' => 'primary_locale',
	];

	/**
	 * Construct a new data object corresponding to this DAO.
	 * @return Press
	 */
	function newDataObject() {
		return new Press();
	}

	/**
	 * Delete the public IDs of all publishing objects in a press.
	 * @param $pressId int
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 */
	function deleteAllPubIds($pressId, $pubIdType) {
		$pubObjectDaos = array('SubmissionDAO', 'ChapterDAO', 'PublicationFormatDAO');
		foreach($pubObjectDaos as $daoName) {
			$dao = DAORegistry::getDAO($daoName);
			$dao->deleteAllPubIds($pressId, $pubIdType);
		}
		import('lib.pkp.classes.submission.SubmissionFileDAODelegate');
		$submissionFileDaoDelegate = new SubmissionFileDAODelegate();
		$submissionFileDaoDelegate->deleteAllPubIds($pressId, $pubIdType);
	}


	/**
	 * Check whether the given public ID exists for any publishing
	 * object in a press.
	 * @param $pressId int
	 * @param $pubIdType string One of the NLM pub-id-type values or
	 * 'other::something' if not part of the official NLM list
	 * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
	 * @param $pubId string
	 * @param $assocType int The object type of an object to be excluded from
	 *  the search. Identified by one of the ASSOC_TYPE_* constants.
	 * @param $assocId int The id of an object to be excluded from the search.
	 * @param $forSameType boolean Whether only the same objects should be considered.
	 * @return boolean
	 */
	function anyPubIdExists($pressId, $pubIdType, $pubId,
		$assocType = ASSOC_TYPE_ANY, $assocId = 0, $forSameType = false) {
		$pubObjectDaos = array(
			ASSOC_TYPE_SUBMISSION => Application::getSubmissionDAO(),
			ASSOC_TYPE_CHAPTER => DAORegistry::getDAO('ChapterDAO'),
			ASSOC_TYPE_REPRESENTATION => Application::getRepresentationDAO(),
			ASSOC_TYPE_SUBMISSION_FILE => DAORegistry::getDAO('SubmissionFileDAO')
		);
		if ($forSameType) {
			$dao = $pubObjectDaos[$assocType];
			$excludedId = $assocId;
			if ($dao->pubIdExists($pubIdType, $pubId, $excludedId, $pressId)) return true;
			return false;
		}
		foreach($pubObjectDaos as $daoAssocType => $dao) {
			if ($assocType == $daoAssocType) {
				$excludedId = $assocId;
			} else {
				$excludedId = 0;
			}
			if ($dao->pubIdExists($pubIdType, $pubId, $excludedId, $pressId)) return true;
		}
		return false;
	}
}
