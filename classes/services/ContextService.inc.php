<?php
/**
 * @file classes/services/ContextService.php
 *
 * Copyright (c) 2014-2019 Simon Fraser University
 * Copyright (c) 2000-2019 John Willinsky
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @class ContextService
 * @ingroup services
 *
 * @brief Extends the base context service class with app-specific
 *  requirements.
 */
namespace APP\Services;

use \Services;

class ContextService extends \PKP\Services\PKPContextService {
	/** @copydoc \PKP\Services\PKPContextService::$contextsFileDirName */
	var $contextsFileDirName = 'presses';

	/**
	 * Initialize hooks for extending PKPContextService
	 */
	public function __construct() {
		$this->installFileDirs = array(
			\Config::getVar('files', 'files_dir') . '/%s/%d',
			\Config::getVar('files', 'files_dir'). '/%s/%d/monographs',
			\Config::getVar('files', 'public_files_dir') . '/%s/%d',
		);

		\HookRegistry::register('Context::edit', array($this, 'afterEditContext'));
		\HookRegistry::register('Context::delete::before', array($this, 'beforeDeleteContext'));
		\HookRegistry::register('Context::delete', array($this, 'afterDeleteContext'));
		\HookRegistry::register('Context::validate', array($this, 'validateContext'));
	}

	/**
	 * Update press-specific settings when a context is edited
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Press The new context
	 *		@option Press The current context
	 *		@option array The params to edit
	 *		@option Request
	 * ]
	 */
	public function afterEditContext($hookName, $args) {
		$newContext = $args[0];
		$currentContext = $args[1];
		$params = $args[2];
		$request = $args[3];

		// If the context is enabled or disabled, create or delete publication
		// format tombstones for all published submissions
		if ($newContext->getData('enabled') !== $currentContext->getData('enabled')) {
			import('classes.publicationFormat.PublicationFormatTombstoneManager');
			$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
			if ($newContext->getData('enabled')) {
				$publicationFormatTombstoneMgr->deleteTombstonesByPressId($newContext->getId());
			} else {
				$publicationFormatTombstoneMgr->insertTombstonesByPress($newContext);
			}
		}

		// If the cover image sizes have been changed, resize existing images
		if (($newContext->getData('coverThumbnailsMaxWidth') !== $currentContext->getData('coverThumbnailsMaxWidth'))
				|| ($newContext->getData('coverThumbnailsMaxHeight') !== $currentContext->getData('coverThumbnailsMaxHeight'))) {
			$this->resizeCoverThumbnails($newContext, $newContext->getData('coverThumbnailsMaxWidth'), $newContext->getData('coverThumbnailsMaxHeight'));
		}
	}

	/**
	 * Perform actions before a context has been deleted
	 *
	 * This should only be used in cases where you need the context to still exist
	 * in the database to complete the actions. Otherwise, use
	 * ContextService::afterDeleteContext().
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Context The new context
	 *		@option Request
 	 * ]
	 */
	public function beforeDeleteContext($hookName, $args) {
		$context = $args[0];

		// Create publication format tombstones for all published submissions
		import('classes.publicationFormat.PublicationFormatTombstoneManager');
		$publicationFormatTombstoneMgr = new \PublicationFormatTombstoneManager();
		$publicationFormatTombstoneMgr->insertTombstonesByPress($context);
	}

	/**
	 * Perform additional actions after a context has been deleted
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Context The new context
	 *		@option Request
 	 * ]
	 */
	public function afterDeleteContext($hookName, $args) {
		$context = $args[0];

		$seriesDao = \DAORegistry::getDAO('SeriesDAO');
		$seriesDao->deleteByPressId($context->getId());

		$submissionDao = \DAORegistry::getDAO('SubmissionDAO');
		$submissionDao->deleteByContextId($context->getId());

		$featureDao = \DAORegistry::getDAO('FeatureDAO');
		$featureDao->deleteByAssoc(ASSOC_TYPE_PRESS, $context->getId());

		$newReleaseDao = \DAORegistry::getDAO('NewReleaseDAO');
		$newReleaseDao->deleteByAssoc(ASSOC_TYPE_PRESS, $context->getId());

		import('classes.file.PublicFileManager');
		$publicFileManager = new \PublicFileManager();
		$publicFileManager->rmtree($publicFileManager->getContextFilesPath($context->getId()));
	}

	/**
	 * Make additional validation checks
	 *
	 * @param $hookName string
	 * @param $args array [
	 *		@option Context The new context
	 *		@option Request
	 * ]
	 */
	public function validateContext($hookName, $args) {
		$errors =& $args[0];
		$props = $args[2];

		if (!empty($props['codeType'])) {
			if (!\DAORegistry::getDAO('ONIXCodelistItemDAO')->codeExistsInList($props['codeType'], 'List44')) {
				$errors['codeType'] = [__('manager.settings.publisherCodeType.invalid')];
			}
		}
	}

	/**
	 * Resize cover image thumbnails
	 *
	 * Processes all cover images to resize the thumbnails according to the passed
	 * width and height maximums.
	 *
	 * @param $context Context
	 * @param $maxWidth int The maximum width allowed for a cover image
	 * @param $maxHeight int The maximum width allowed for a cover image
	 */
	public function resizeCoverThumbnails($context, $maxWidth, $maxHeight) {
		import('lib.pkp.classes.file.FileManager');
		import('classes.file.PublicFileManager');
		import('lib.pkp.classes.file.ContextFileManager');
		$fileManager = new \FileManager();
		$publicFileManager = new \PublicFileManager();
		$contextFileManager = new \ContextFileManager($context->getId());

		$objectDaos = [
			\DAORegistry::getDAO('CategoryDAO'),
			\DAORegistry::getDAO('SeriesDAO'),
			\DAORegistry::getDAO('SubmissionDAO'),
		];
		foreach ($objectDaos as $objectDao) {
			$objects = $objectDao->getByContextId($context->getId());
			while ($object = $objects->next()) {
				if (is_a($object, 'Submission')) {
					foreach ($object->getData('publications') as $publication) {
						foreach ((array) $publication->getData('coverImage') as $coverImage) {
							$coverImageFilePath = $publicFileManager->getContextFilesPath($context->getId()) . '/' . $coverImage['uploadName'];
							Services::get('publication')->makeThumbnail(
								$coverImageFilePath,
								Services::get('publication')->getThumbnailFileName($coverImage['uploadName']),
								$maxWidth,
								$maxHeight
							);
						}
					}
				} else {
					$cover = $object->getImage();
					if (is_a($object, 'Series')) {
						$basePath = $contextFileManager->getBasePath() . 'series/';
					} elseif (is_a($object, 'Category')) {
						$basePath = $contextFileManager->getBasePath() . 'categories/';
					}
				}
				if ($cover) {
					// delete old cover thumbnail
					$fileManager->deleteByPath($basePath . $cover['thumbnailName']);

					// get settings necessary for the new thumbnail
					$coverExtension = $fileManager->getExtension($cover['name']);
					$xRatio = min(1, $maxWidth / $cover['width']);
					$yRatio = min(1, $maxHeight / $cover['height']);
					$ratio = min($xRatio, $yRatio);
					$thumbnailWidth = round($ratio * $cover['width']);
					$thumbnailHeight = round($ratio * $cover['height']);

					// create a thumbnail image of the defined size
					$thumbnail = imagecreatetruecolor($thumbnailWidth, $thumbnailHeight);

					// generate the image of the original cover
					switch ($coverExtension) {
						case 'jpg': $coverImage = imagecreatefromjpeg($basePath . $cover['name']); break;
						case 'png': $coverImage = imagecreatefrompng($basePath . $cover['name']); break;
						case 'gif': $coverImage = imagecreatefromgif($basePath . $cover['name']); break;
						default: $coverImage = null; // Suppress warn
					}
					assert($coverImage);

					// copy the cover image to the thumbnail
					imagecopyresampled($thumbnail, $coverImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $cover['width'], $cover['height']);

					// create the thumbnail file
					switch ($coverExtension) {
						case 'jpg': imagejpeg($thumbnail, $basePath . $cover['thumbnailName']); break;
						case 'png': imagepng($thumbnail, $basePath . $cover['thumbnailName']); break;
						case 'gif': imagegif($thumbnail, $basePath . $cover['thumbnailName']); break;
					}

					imagedestroy($thumbnail);
					if (is_a($object, 'Submission')) {
						$object->setCoverImage(array(
							'name' => $cover['name'],
							'width' => $cover['width'],
							'height' => $cover['height'],
							'thumbnailName' => $cover['thumbnailName'],
							'thumbnailWidth' => $thumbnailWidth,
							'thumbnailHeight' => $thumbnailHeight,
							'uploadName' => $cover['uploadName'],
							'dateUploaded' => $cover['dateUploaded'],
						));
					} else {
						$object->setImage(array(
							'name' => $cover['name'],
							'width' => $cover['width'],
							'height' => $cover['height'],
							'thumbnailName' => $cover['thumbnailName'],
							'thumbnailWidth' => $thumbnailWidth,
							'thumbnailHeight' => $thumbnailHeight,
							'uploadName' => $cover['uploadName'],
							'dateUploaded' => $cover['dateUploaded'],
						));
					}
					// Update category object to store new thumbnail information.
					$objectDao->updateObject($object);
				}
				unset($object);
			}
		}
	}
}
