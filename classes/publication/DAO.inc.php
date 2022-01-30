<?php
/**
 * @file classes/publication/DAO.inc.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class publication
 *
 * @brief Read and write publications to the database.
 */

namespace APP\publication;

use APP\core\Application;
use PKP\db\DAORegistry;

class DAO extends \PKP\publication\DAO
{
    /** @copydoc EntityDAO::$primaryTableColumns */
    public $primaryTableColumns = [
        'id' => 'publication_id',
        'accessStatus' => 'access_status',
        'datePublished' => 'date_published',
        'lastModified' => 'last_modified',
        'locale' => 'locale',
        'primaryContactId' => 'primary_contact_id',
        'seq' => 'seq',
        'seriesId' => 'series_id',
        'submissionId' => 'submission_id',
        'status' => 'status',
        'urlPath' => 'url_path',
        'version' => 'version',
        'seriesPosition' => 'series_position',
    ];

    /**
     * @copydoc SchemaDAO::_fromRow()
     */
    public function fromRow(object $primaryRow): Publication
    {
        $publication = parent::fromRow($primaryRow);
        $publication->setData('publicationFormats', Application::getRepresentationDao()->getByPublicationId($publication->getId())->toArray());
        $publication->setData('chapters', DAORegistry::getDAO('ChapterDAO')->getByPublicationId($publication->getId())->toArray());
        return $publication;
    }
}
