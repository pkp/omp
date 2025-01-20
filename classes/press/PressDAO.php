<?php

/**
 * @file classes/press/PressDAO.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PressDAO
 *
 * @ingroup press
 *
 * @see Press
 *
 * @brief Operations for retrieving and modifying Press objects.
 */

namespace APP\press;

use APP\core\Application;
use APP\facades\Repo;
use APP\monograph\ChapterDAO;
use APP\publicationFormat\PublicationFormatDAO;
use PKP\context\ContextDAO;
use PKP\db\DAORegistry;
use PKP\metadata\MetadataTypeDescription;

/**
 * @extends ContextDAO<Press>
 */
class PressDAO extends ContextDAO
{
    /** @copydoc SchemaDAO::$schemaName */
    public $schemaName = 'context';

    /** @copydoc SchemaDAO::$tableName */
    public $tableName = 'presses';

    /** @copydoc SchemaDAO::$settingsTableName */
    public $settingsTableName = 'press_settings';

    /** @copydoc SchemaDAO::$primaryKeyColumn */
    public $primaryKeyColumn = 'press_id';

    /** @var array Maps schema properties for the primary table to their column names */
    public $primaryTableColumns = [
        'id' => 'press_id',
        'urlPath' => 'path',
        'enabled' => 'enabled',
        'seq' => 'seq',
        'primaryLocale' => 'primary_locale',
    ];

    /**
     * Construct a new data object corresponding to this DAO.
     *
     * @return Press
     */
    public function newDataObject()
    {
        return new Press();
    }

    /**
     * Delete the public IDs of all publishing objects in a press.
     *
     * @param string $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     */
    public function deleteAllPubIds(int $pressId, $pubIdType)
    {
        $pubObjectDaos = ['ChapterDAO', 'PublicationFormatDAO'];
        foreach ($pubObjectDaos as $daoName) {
            /** @var ChapterDAO|PublicationFormatDAO */
            $dao = DAORegistry::getDAO($daoName);
            $dao->deleteAllPubIds($pressId, $pubIdType);
        }

        Repo::submissionFile()->dao->deleteAllPubIds($pressId, $pubIdType);
        Repo::publication()->dao->deleteAllPubIds($pressId, $pubIdType);
    }


    /**
     * Check whether the given public ID exists for any publishing
     * object in a press.
     *
     * @param string $pubIdType One of the NLM pub-id-type values or
     * 'other::something' if not part of the official NLM list
     * (see <http://dtd.nlm.nih.gov/publishing/tag-library/n-4zh0.html>).
     * @param string $pubId
     * @param int $assocType The object type of an object to be excluded from
     *  the search. Identified by one of the Application::ASSOC_TYPE_* constants.
     * @param int $assocId The id of an object to be excluded from the search.
     * @param bool $forSameType Whether only the same objects should be considered.
     *
     * @return bool
     */
    public function anyPubIdExists(
        int $pressId,
        $pubIdType,
        $pubId,
        $assocType = MetadataTypeDescription::ASSOC_TYPE_ANY,
        $assocId = 0,
        $forSameType = false
    ) {
        $pubObjectDaos = [
            Application::ASSOC_TYPE_PUBLICATION => Repo::publication()->dao,
            Application::ASSOC_TYPE_CHAPTER => DAORegistry::getDAO('ChapterDAO'),
            Application::ASSOC_TYPE_REPRESENTATION => Application::getRepresentationDAO(),
            Application::ASSOC_TYPE_SUBMISSION_FILE => Repo::submissionFile()->dao,
        ];
        if ($forSameType) {
            $dao = $pubObjectDaos[$assocType];
            $excludedId = $assocId;
            if ($dao->pubIdExists($pubIdType, $pubId, $excludedId, $pressId)) {
                return true;
            }
            return false;
        }
        foreach ($pubObjectDaos as $daoAssocType => $dao) {
            if ($assocType == $daoAssocType) {
                $excludedId = $assocId;
            } else {
                $excludedId = 0;
            }
            if ($dao->pubIdExists($pubIdType, $pubId, $excludedId, $pressId)) {
                return true;
            }
        }
        return false;
    }
}
