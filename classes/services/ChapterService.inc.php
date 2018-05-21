<?php

/**
 * @file classes/services/ChapterService.inc.php
 *
 * Copyright (c) 2014-2018 Simon Fraser University
 * Copyright (c) 2000-2018 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ChapterService
 * @ingroup services
 *
 * @brief Helper class that encapsulates chapter business logic
 */

namespace OMP\Services;

use \PKP\Services\EntityProperties\PKPBaseEntityPropertyService;
use \DBResultRange;
use \DAORegistry;
use \DAOResultFactory;

class ChapterService extends PKPBaseEntityPropertyService {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct($this);

		\HookRegistry::register('Submission::getProperties::summaryProperties', array($this, 'modifyProperties'));
		\HookRegistry::register('Submission::getProperties::fullProperties', array($this, 'modifyProperties'));
		\HookRegistry::register('Submission::getProperties::values', array($this, 'modifyPropertyValues'));
	}

	/**
	 * @copydoc \PKP\Services\EntityProperties\EntityPropertyInterface::getProperties()
	 */
	public function getProperties($chapter, $props, $args = null) {
		$request = $args['request'];
		$context = $request->getContext();
		$dispatcher = $request->getDispatcher();
		$publishedMonograph = $args['parent'];
		$submissionFileDao = DAORegistry::getDAO('SubmissionFileDAO');

		// publication formats
		$publicationFormats = $publishedMonograph->getPublicationFormats(true);
		$availablePublicationFormats = array();
		foreach ($publicationFormats as $format) {
			if ($format->getIsAvailable()) {
				$availablePublicationFormats[] = $format;
			}
		}

		$values = array();
		foreach ($props as $prop) {
			switch ($prop) {
				case 'id':
					$values[$prop] = (int) $chapter->getId();
					break;
				case 'title':
					$values[$prop] = $chapter->getLocalizedTitle();
					break;
				case 'subTitle':
					$values[$prop] = $chapter->getLocalizedSubtitle();
					break;
				case 'fullTitle':
					$values[$prop] = $chapter->getLocalizedTitle();
					break;
				case 'seq':
					$values[$prop] = $chapter->getSequence();
					break;
				case 'authors':
					$values[$prop] = $chapter->getAuthorNamesAsString();
					break;
				case 'files':
					$chapterFiles = array_filter(
						$submissionFileDao->getLatestRevisions($publishedMonograph->getId()),
						function($a) use ($chapter) {
							return $a->getDirectSalesPrice() !== null 
								&& $a->getAssocType() == ASSOC_TYPE_PUBLICATION_FORMAT
								&& $a->getChapterId() == $chapter->getId();
						}
					);
					// Only pass files in pub formats that are also available
					$filteredAvailableFiles = array();
					foreach ($chapterFiles as $file) {
						foreach ($availablePublicationFormats as $format) {
							if ($file->getAssocId() == $format->getId()) {
								$filteredAvailableFiles[] = $file;
								break;
							}
						}
					}
					$items = array();
					foreach ($filteredAvailableFiles as $file) {
						$items[] = array(
							'id' => $file->getFileId(),
							'fileName' => $file->getOriginalFileName(),
						);
					}
					$values[$prop] = $items;
					break;
			}
		}
		\HookRegistry::call('Chapter::getProperties::values', array(&$values, $chapter, $props, $args));
		return $values;
	}

	/**
	 * Returns summary properties for a chapter
	 * @param Chapter $chapter
	 * @param array extra arguments
	 *		$args['request'] PKPRequest Required
	 *		$args['parent'] PublishedMonograph Required
	 *		$args['slimRequest'] SlimRequest
	 * @return array
	 */
	public function getSummaryProperties($chapter, $args = null) {
		$props = array('id','title','subTitle','seq','authors','files');
		\HookRegistry::call('Chapter::getProperties::summaryProperties', array(&$props, $chapter, $args));
		return $this->getProperties($chapter, $props, $args);
	}

	/**
	 * Returns full properties for a chapter
	 * @param Chapter $chapter
	 * @param array extra arguments
	 *		$args['request'] PKPRequest Required
	 *		$args['parent'] PublishedMonograph Required
	 *		$args['slimRequest'] SlimRequest
	 * @return array
	 */
	public function getFullProperties($chapter, $args = null) {
		$props = array('id','title','subTitle','fullTitle','seq','authors');
		\HookRegistry::call('Chapter::getProperties::fullProperties', array(&$props, $chapter, $args));
		return $this->getProperties($chapter, $props, $args);
	}
}
