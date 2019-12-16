<?php
/**
 * @file classes/publication/PublicationDAO.inc.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2003-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class PublicationDAO
 * @ingroup core
 * @see DAO
 *
 * @brief Add OMP-specific functions for PKPPublicationDAO
 */

import('lib.pkp.classes.publication.PKPPublicationDAO');
class PublicationDAO extends PKPPublicationDAO {

	/** @copydoc SchemaDao::$primaryTableColumns */
	public $primaryTableColumns = [
		'id' => 'publication_id',
		'accessStatus' => 'access_status',
		'datePublished' => 'date_published',
		'lastModified' => 'last_modified',
		'locale' => 'locale',
		'primaryContactId' => 'primary_contact_id',
		'submissionId' => 'submission_id',
		'status' => 'status',
	];

	/**
	 * @copydoc SchemaDAO::_fromRow()
	 */
	public function _fromRow($primaryRow) {
		$publication = parent::_fromRow($primaryRow);

		$publication->setData('publicationFormats', Application::getRepresentationDao()->getByPublicationId($publication->getId())->toArray());
		$publication->setData('chapters', DAORegistry::getDAO('ChapterDAO')->getByPublicationId($publication->getId())->toArray());

		return $publication;
	}
}