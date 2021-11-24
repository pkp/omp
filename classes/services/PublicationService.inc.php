<?php
/**
 * @file classes/services/PublicationService.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class PublicationService
 * @ingroup services
 *
 * @brief Extends the base publication service class with app-specific
 *  requirements.
 */
namespace APP\Services;

use \Application;
use \DAORegistry;
use \Services;
use \PKP\Services\PKPPublicationService;

class PublicationService extends PKPPublicationService {

	/**
	 * Initialize hooks for extending PKPPublicationService
	 */
	public function __construct() {
		\HookRegistry::register('Publication::getProperties', [$this, 'getPublicationProperties']);
		\HookRegistry::register('Publication::validate', [$this, 'validatePublication']);
		\HookRegistry::register('Publication::add', [$this, 'addPublication']);
		\HookRegistry::register('Publication::edit', [$this, 'editPublication']);
		\HookRegistry::register('Publication::version', [$this, 'versionPublication']);
		\HookRegistry::register('Publication::publish::before', [$this, 'publishPublicationBefore']);
		\HookRegistry::register('Publication::publish', [$this, 'publishPublication']);
		\HookRegistry::register('Publication::unpublish', [$this, 'unpublishPublication']);
		\HookRegistry::register('Publication::delete::before', [$this, 'deletePublicationBefore']);
	}

	/**
	 * Add values when retrieving an object's properties
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option array Property values
	 *		@option Publication
	 *		@option array The props requested
	 *		@option array Additional arguments (such as the request object) passed
	 * ]
	 */
	public function getPublicationProperties($hookName, $args) {
		$values =& $args[0];
		$publication = $args[1];
		$props = $args[2];
		$dependencies = $args[3];
		$request = $dependencies['request'];
		$dispatcher = $request->getDispatcher();

		// Get required submission and context
		$submission = !empty($args['submission'])
			? $args['submission']
			: $args['submission'] = Services::get('submission')->get($publication->getData('submissionId'));

		$submissionContext = !empty($dependencies['context'])
			? $dependencies['context']
			: $dependencies['context'] = Services::get('context')->get($submission->getData('contextId'));

		foreach ($props as $prop) {
			switch ($prop) {
				case 'chapters':
					$values[$prop] = array_map(
						function($chapter) use ($publication) {
							$data = $chapter->_data;
							$data['authors'] = array_map(
								function($chapterAuthor) {
									return $chapterAuthor->_data;
								},
								DAORegistry::getDAO('ChapterAuthorDAO')->getAuthors($publication->getId(), $chapter->getId())->toArray()
							);
							return $data;
						},
						$publication->getData('chapters')
					);
					break;
				case 'publicationFormats':
					$values[$prop] = array_map(
						function($publicationFormat) {
							return $publicationFormat->_data;
						},
						$publication->getData('publicationFormats')
					);
					break;
				case 'urlPublished':
					$values[$prop] = $dispatcher->url(
						$request,
						ROUTE_PAGE,
						$submissionContext->getData('urlPath'),
						'catalog',
						'book',
						[$submission->getBestId(), 'version', $publication->getId()]
					);
					break;
			}
		}
	}

	/**
	 * Make additional validation checks
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option array Validation errors already identified
	 *		@option string One of the VALIDATE_ACTION_* constants
	 *		@option array The props being validated
	 *		@option array The locales accepted for this object
	 *    @option string The primary locale for this object
	 * ]
	 */
	public function validatePublication($hookName, $args) {
		$errors =& $args[0];
		$props = $args[2];

		// Ensure that the specified series exists
		if (isset($props['seriesId'])) {
			$series = Application::get()->getSectionDAO()->getById($props['seriesId']);
			if (!$series) {
				$errors['seriesId'] = [__('publication.invalidSeries')];
			}
		}
	}

	/**
	 * Perform OMP-specific steps when adding a publication
	 *
	 * @param string $hookName
	 * @param array $args [
	 *  @option Publication
	 *  @option Request
	 * ]
	 */
	public function addPublication($hookName, $args) {
		$publication = $args[0];
		$request = $args[1];

		// Create a thumbnail for the cover image
		if ($publication->getData('coverImage')) {

			$submission = Services::get('submission')->get($publication->getData('submissionId'));
			$submissionContext = $request->getContext();
			if ($submissionContext->getId() !== $submission->getData('contextId')) {
				$submissionContext = Services::get('context')->get($submission->getData('contextId'));
			}

			$supportedLocales = $submissionContext->getSupportedSubmissionLocales();
			foreach ($supportedLocales as $localeKey) {
				if (!array_key_exists($localeKey, $publication->getData('coverImage'))) {
					continue;
				}

				import('classes.file.PublicFileManager');
				$publicFileManager = new \PublicFileManager();
				$coverImage = $publication->getData('coverImage', $localeKey);
				$coverImageFilePath = $publicFileManager->getContextFilesPath($submissionContext->getId()) . '/' . $coverImage['uploadName'];
				$this->makeThumbnail(
					$coverImageFilePath,
					$this->getThumbnailFileName($coverImage['uploadName']),
					$submissionContext->getData('coverThumbnailsMaxWidth'),
					$submissionContext->getData('coverThumbnailsMaxHeight')
				);
			}
		}
	}

	/**
	 * Perform OMP-specific steps when editing a publication
	 *
	 * @param string $hookName
	 * @param array $args [
	 * 	@option Publication The new publication details
	 * 	@option Publication The old publication details
	 *  @option array The params with the edited values
	 *  @option Request
	 * ]
	 */
	public function editPublication($hookName, $args) {
		$newPublication = $args[0];
		$oldPublication = $args[1];
		$params = $args[2];

		// Create or delete the thumbnail of a cover image
		if (array_key_exists('coverImage', $params)) {
			import('classes.file.PublicFileManager');
			$publicFileManager = new \PublicFileManager();
			$submission = Services::get('submission')->get($newPublication->getData('submissionId'));

			// Get the submission context
			$submissionContext = \Application::get()->getRequest()->getContext();
			if ($submissionContext->getId() !== $submission->getData('contextId')) {
				$submissionContext = Services::get('context')->get($submission->getData('contextId'));
			}

			foreach ($params['coverImage'] as $localeKey => $value) {

				// Delete the thumbnail if the cover image has been deleted
				if (is_null($value)) {
					$oldCoverImage = $oldPublication->getData('coverImage', $localeKey);
					if (!$oldCoverImage) {
						continue;
					}

					$coverImageFilePath = $publicFileManager->getContextFilesPath($submission->getData('contextId')) . '/' . $oldCoverImage['uploadName'];
					if (!file_exists($coverImageFilePath)) {
						$publicFileManager->removeContextFile($submission->getData('contextId'), $this->getThumbnailFileName($oldCoverImage['uploadName']));
					}

				// Otherwise generate a new thumbnail if a cover image exists
				} else {
					$newCoverImage = $newPublication->getData('coverImage', $localeKey);
					if (!$newCoverImage) {
						continue;
					}

					$coverImageFilePath = $publicFileManager->getContextFilesPath($submission->getData('contextId')) . '/' . $newCoverImage['uploadName'];
					$this->makeThumbnail(
						$coverImageFilePath,
						$this->getThumbnailFileName($newCoverImage['uploadName']),
						$submissionContext->getData('coverThumbnailsMaxWidth'),
						$submissionContext->getData('coverThumbnailsMaxHeight')
					);
				}
			}
		}
	}

	/**
	 * Copy OMP-specific objects when a new publication version is created
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 *		@option Request
	 * ]
	 */
	public function versionPublication($hookName, $args) {
		$newPublication =& $args[0];
		$oldPublication = $args[1];
		$request = $args[2];

		// Publication Formats (and all associated objects)
		$oldPublicationFormats = $oldPublication->getData('publicationFormats');
		$newSubmissionFiles = [];
		foreach ($oldPublicationFormats as $oldPublicationFormat) {
			$newPublicationFormat = clone $oldPublicationFormat;
			$newPublicationFormat->setData('id', null);
			$newPublicationFormat->setData('publicationId', $newPublication->getId());
			Application::getRepresentationDAO()->insertObject($newPublicationFormat);

			// Duplicate publication format metadata
			$metadataDaos = ['IdentificationCodeDAO', 'MarketDAO', 'PublicationDateDAO', 'SalesRightsDAO'];
			foreach ($metadataDaos as $metadataDao) {
				$result = DAORegistry::getDAO($metadataDao)->getByPublicationFormatId($oldPublicationFormat->getId());
				while (!$result->eof()) {
					$oldObject = $result->next();
					$newObject = clone $oldObject;
					$newObject->setData('id', null);
					$newObject->setData('publicationFormatId', $newPublicationFormat->getId());
					DAORegistry::getDAO($metadataDao)->insertObject($newObject);
				}
			}

			// Duplicate publication format files
			$submissionFiles = Services::get('submissionFile')->getMany([
				'submissionIds' => [$oldPublication->getData('submissionId')],
				'assocTypes' => [ASSOC_TYPE_REPRESENTATION],
				'assocIds' => [$oldPublicationFormat->getId()],
			]);
			foreach ($submissionFiles as $submissionFile) {
				$newSubmissionFile = clone $submissionFile;
				$newSubmissionFile->setData('id', null);
				$newSubmissionFile->setData('assocId', $newPublicationFormat->getId());
				$newSubmissionFile = Services::get('submissionFile')->add($newSubmissionFile, $request);
				$newSubmissionFiles[] = $newSubmissionFile;

				$dependentFiles = Services::get('submissionFile')->getMany([
					'fileStages' => [SUBMISSION_FILE_DEPENDENT],
					'assocTypes' => [ASSOC_TYPE_SUBMISSION_FILE],
					'assocIds' => [$submissionFile->getId()],
					'includeDependentFiles' => true,
				]);
				foreach ($dependentFiles as $dependentFile) {
					$newDependentFile = clone $dependentFile;
					$newDependentFile->setData('id', null);
					$newDependentFile->setData('assocId', $newSubmissionFile->getId());
					Services::get('submissionFile')->add($newDependentFile, $request);
				}
			}
		}

		// Chapters (and all associated objects)
		$oldAuthorsIterator = Services::get('author')->getMany(['publicationIds' => $oldPublication->getId()]);
		$oldAuthors = iterator_to_array($oldAuthorsIterator);
		$newAuthorsIterator = Services::get('author')->getMany(['publicationIds' => $newPublication->getId()]);
		$newAuthors = iterator_to_array($newAuthorsIterator);
		$result = DAORegistry::getDAO('ChapterDAO')->getByPublicationId($oldPublication->getId());
		while (!$result->eof()) {
			$oldChapter = $result->next();
			$newChapter = clone $oldChapter;
			$newChapter->setData('id', null);
			$newChapter->setData('publicationId', $newPublication->getId());
			$newChapterId = DAORegistry::getDAO('ChapterDAO')->insertChapter($newChapter);
			$newChapter = DAORegistry::getDAO('ChapterDAO')->getChapter($newChapterId);

			// Update file chapter associations for new files
			foreach ($newSubmissionFiles as $newSubmissionFile) {
				if ($newSubmissionFile->getChapterId() == $oldChapter->getId()) {
					Services::get('submissionFile')->edit($newSubmissionFile, ['chapterId' => $newChapter->getId()], $request);
				}
			}

			// We need to link new authors to chapters. To do this, we need a way to
			// link old authors to the new ones. We use seq property, which should be
			// unique for each author to determine which new author is a copy of the
			// old one. We then map the old chapter author associations to the new
			// authors.
			$oldChapterAuthors = DAORegistry::getDAO('ChapterAuthorDAO')->getAuthors($oldPublication->getId(), $oldChapter->getId())->toArray();
			foreach ($newAuthors as $newAuthor) {
				foreach ($oldAuthors as $oldAuthor) {
					if ($newAuthor->getData('seq') === $oldAuthor->getData('seq')) {
						foreach ($oldChapterAuthors as $oldChapterAuthor) {
							if ($oldChapterAuthor->getId() === $oldAuthor->getId()) {
								DAORegistry::getDAO('ChapterAuthorDAO')->insertChapterAuthor(
									$newAuthor->getId(),
									$newChapter->getId(),
									$newAuthor->getId() === $newPublication->getData('primaryContactId'),
									$oldChapterAuthor->getData('seq')
								);
							}
						}
					}
				}
			}
		}

		$newPublication = $this->get($newPublication->getId());
	}

	/**
	 * Modify a publication before it is published
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 * ]
	 */
	public function publishPublicationBefore($hookName, $args) {
		$newPublication = $args[0];
		$oldPublication = $args[1];

		// If the publish date is in the future, set the status to scheduled
		$datePublished = $oldPublication->getData('datePublished');
		if ($datePublished && strtotime($datePublished) > strtotime(\Core::getCurrentDate())) {
			$newPublication->setData('status', STATUS_SCHEDULED);
		}
	}

	/**
	 * Fire events after a publication has been published
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 *		@option Submission The publication's submission
	 * ]
	 */
	public function publishPublication($hookName, $args) {
		$newPublication = $args[0];
		$submission = $args[2];

		// If this is a new current publication (the "version of record"), then
		// tombstones must be updated to reflect the new publication format entries
		// in the OAI feed
		if ($submission->getData('currentPublicationId') === $newPublication->getId()) {
			$context = Application::get()->getRequest()->getContext();
			if (!$context || $context->getId() !== $submission->getData('contextId')) {
				$context = Services::get('context')->get($submission->getData('contextId'));
			}

			// Remove publication format tombstones for this publication
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationId($newPublication->getId());

			// Create publication format tombstones for any other published versions
			foreach ($submission->getData('publications') as $publication) {
				if ($publication->getId() !== $newPublication->getId() && $publication->getData('status') === STATUS_PUBLISHED) {
					$publicationFormatTombstoneMgr->insertTombstonesByPublicationId($publication->getId(), $context);
				}
			}
		}

		// Update notification
		$request = \Application::get()->getRequest();
		$notificationMgr = new \NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$newPublication->getData('submissionId')
		);
	}

	/**
	 * Fire events after a publication has been unpublished
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The new version of the publication
	 *		@option Publication The old version of the publication
	 *		@option Submission The publication's submission
	 * ]
	 */
	public function unpublishPublication($hookName, $args) {
		$newPublication = $args[0];
		$oldPublication = $args[1];
		$submission = Services::get('submission')->get($newPublication->getData('submissionId'));
		$submissionContext = Services::get('context')->get($submission->getData('contextId'));

		// Create tombstones for this publication
		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
		$publicationFormatTombstoneMgr->insertTombstonesByPublicationId($newPublication->getId(), $submissionContext);

		// Delete tombstones for the new current publication
		$currentPublication = null;
		foreach ($submission->getData('publications') as $publication) {
			if ($publication->getId() === $submission->getData('currentPublicationId')) {
				$currentPublication = $publication;
				break;
			}
		}
		if ($currentPublication->getData('status') === STATUS_PUBLISHED) {
			$publicationFormatTombstoneMgr->deleteTombstonesByPublicationId($currentPublication->getId());
		}


		// Update notification
		$request = \Application::get()->getRequest();
		$notificationMgr = new \NotificationManager();
		$notificationMgr->updateNotification(
			$request,
			array(NOTIFICATION_TYPE_APPROVE_SUBMISSION),
			null,
			ASSOC_TYPE_MONOGRAPH,
			$newPublication->getData('submissionId')
		);
	}

	/**
	 * Delete OJS-specific objects before a publication is deleted
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Publication The publication being deleted
	 * ]
	 */
	public function deletePublicationBefore($hookName, $args) {
		$publication = $args[0];
    $submission = Services::get('submission')->get($publication->getData('submissionId'));
		$context = Services::get('context')->get($submission->getData('contextId'));

		// Publication Formats (and all related objects)
		$publicationFormats = $publication->getData('publicationFormats');
		foreach ($publicationFormats as $publicationFormat) {
			Services::get('publicationFormat')->deleteFormat($publicationFormat, $submission, $context);
		}

		// Delete chapters and assigned chapter authors.
		$chapters = DAORegistry::getDAO('ChapterDAO')->getByPublicationId($publication->getId());
		while ($chapter = $chapters->next()) {
			// also removes Chapter Author and file associations
			DAORegistry::getDAO('ChapterDAO')->deleteObject($chapter);
		}
	}

	/**
	 * Derive a thumbnail filename from the cover image filename
	 *
	 * book_1_1_cover.png --> book_1_1_cover_t.png
	 *
	 * @param string $fileName
	 * @return string The thumbnail filename
	 */
	public function getThumbnailFileName($fileName) {
		$pathInfo = pathinfo($fileName);
		return $pathInfo['filename'] . '_t.' . $pathInfo['extension'];
	}

	/**
	 * Generate a thumbnail of an image
	 *
	 * @param string $filePath The full path and name of the file
	 * @param int $maxWidth The maximum allowed width of the thumbnail
	 * @param int $maxHeight The maximum allowed height of the thumbnail
	 */
	public function makeThumbnail($filePath, $thumbFileName, $maxWidth, $maxHeight) {
		$pathParts = pathinfo($filePath);
		$thumbFilePath = $pathParts['dirname'] . '/' . $thumbFileName;

		$cover = null;
		switch ($pathParts['extension']) {
			case 'jpg': $cover = imagecreatefromjpeg($filePath); break;
			case 'png': $cover = imagecreatefrompng($filePath); break;
			case 'gif': $cover = imagecreatefromgif($filePath); break;
			case 'webp': $cover = imagecreatefromwebp($filePath); break;
			case 'svg': $cover = copy($filePath, $thumbFilePath); break;
		}
		if (!isset($cover)) {
			throw new \Exception('Can not build thumbnail because the file was not found or the file extension was not recognized.');
		}
		if ($pathParts['extension'] != 'svg') {
			// Calculate the scaling ratio for each dimension.
			$originalSizeArray = getimagesize($filePath);
			$xRatio = min(1, $maxWidth / $originalSizeArray[0]);
			$yRatio = min(1, $maxHeight / $originalSizeArray[1]);

			// Choose the smallest ratio and create the target.
			$ratio = min($xRatio, $yRatio);

			$thumbWidth = round($ratio * $originalSizeArray[0]);
			$thumbHeight = round($ratio * $originalSizeArray[1]);
			$thumb = imagecreatetruecolor($thumbWidth, $thumbHeight);
			imagecopyresampled($thumb, $cover, 0, 0, 0, 0, $thumbWidth, $thumbHeight, $originalSizeArray[0], $originalSizeArray[1]);

			switch ($pathParts['extension']) {
				case 'jpg': imagejpeg($thumb, $thumbFilePath); break;
				case 'png': imagepng($thumb, $thumbFilePath); break;
				case 'gif': imagegif($thumb, $thumbFilePath); break;
				case 'webp': imagewebp($thumb, $thumbFilePath); break;
			}

			imagedestroy($thumb);
		}

	}
}
