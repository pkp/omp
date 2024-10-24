<?php
/**
 * @file classes/services/ContextService.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2000-2021 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @class ContextService
 *
 * @ingroup services
 *
 * @brief Extends the base context service class with app-specific
 *  requirements.
 */

namespace APP\services;

use APP\codelist\ONIXCodelistItemDAO;
use APP\core\Application;
use APP\facades\Repo;
use APP\file\PublicFileManager;
use APP\press\FeatureDAO;
use APP\press\NewReleaseDAO;
use APP\press\Press;
use APP\publicationFormat\PublicationFormatTombstoneManager;
use APP\submission\Submission;
use PKP\config\Config;
use PKP\db\DAORegistry;
use PKP\file\ContextFileManager;
use PKP\file\FileManager;
use PKP\plugins\Hook;
use PKP\submission\GenreDAO;

class ContextService extends \PKP\services\PKPContextService
{
    /** @copydoc \PKP\services\PKPContextService::$contextsFileDirName */
    public $contextsFileDirName = 'presses';

    /**
     * Initialize hooks for extending PKPContextService
     */
    public function __construct()
    {
        $this->installFileDirs = [
            Config::getVar('files', 'files_dir') . '/%s/%d',
            Config::getVar('files', 'files_dir') . '/%s/%d/monographs',
            Config::getVar('files', 'public_files_dir') . '/%s/%d',
        ];

        Hook::add('Context::edit', [$this, 'afterEditContext']);
        Hook::add('Context::delete::before', [$this, 'beforeDeleteContext']);
        Hook::add('Context::delete', [$this, 'afterDeleteContext']);
        Hook::add('Context::validate', [$this, 'validateContext']);
    }

    /**
     * Update press-specific settings when a context is edited
     *
     * @param string $hookName
     * @param array $args [
     *
     *		@option Press The new context
     *		@option Press The current context
     *		@option array The params to edit
     *		@option Request
     * ]
     */
    public function afterEditContext($hookName, $args)
    {
        $newContext = $args[0];
        $currentContext = $args[1];
        $params = $args[2];
        $request = $args[3];

        // If the context is enabled or disabled, create or delete publication
        // format tombstones for all published submissions
        if ($newContext->getData('enabled') !== $currentContext->getData('enabled')) {
            $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
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

        // Move an uploaded press thumbnail and set the updated data
        if (!empty($params['pressThumbnail'])) {
            $supportedLocales = $newContext->getSupportedFormLocales();
            foreach ($supportedLocales as $localeKey) {
                if (!array_key_exists($localeKey, $params['pressThumbnail'])) {
                    continue;
                }
                $localeValue = $this->_saveFileParam(
                    $newContext,
                    $params['pressThumbnail'][$localeKey],
                    'pressThumbnail',
                    $request->getUser()->getId(),
                    $localeKey,
                    true
                );
                $newContext->setData('pressThumbnail', $localeValue, $localeKey);
            }
        }
    }

    /**
     * Perform actions before a context has been deleted
     *
     * This should only be used in cases where you need the context to still exist
     * in the database to complete the actions. Otherwise, use
     * ContextService::afterDeleteContext().
     *
     * @param string $hookName
     * @param array $args [
     *
     *		@option Context The new context
     *		@option Request
     * ]
     */
    public function beforeDeleteContext($hookName, $args)
    {
        $context = $args[0];

        // Create publication format tombstones for all published submissions
        $publicationFormatTombstoneMgr = new PublicationFormatTombstoneManager();
        $publicationFormatTombstoneMgr->insertTombstonesByPress($context);

        /** @var GenreDAO */
        $genreDao = DAORegistry::getDAO('GenreDAO');
        $genreDao->deleteByContextId($context->getId());
    }

    /**
     * Perform additional actions after a context has been deleted
     *
     * @param string $hookName
     * @param array $args [
     *
     *		@option Context The new context
     *		@option Request
     * ]
     */
    public function afterDeleteContext($hookName, $args)
    {
        $context = $args[0];

        Repo::section()->deleteMany(
            Repo::section()
                ->getCollector()
                ->filterByContextIds([$context->getId()])
        );

        Repo::submission()->deleteByContextId($context->getId());

        /** @var FeatureDAO */
        $featureDao = DAORegistry::getDAO('FeatureDAO');
        $featureDao->deleteByAssoc(Application::ASSOC_TYPE_PRESS, $context->getId());

        /** @var NewReleaseDAO */
        $newReleaseDao = DAORegistry::getDAO('NewReleaseDAO');
        $newReleaseDao->deleteByAssoc(Application::ASSOC_TYPE_PRESS, $context->getId());

        $publicFileManager = new PublicFileManager();
        $publicFileManager->rmtree($publicFileManager->getContextFilesPath($context->getId()));
    }

    /**
     * Make additional validation checks
     *
     * @param string $hookName
     * @param array $args [
     *
     *		@option Context The new context
     *		@option Request
     * ]
     */
    public function validateContext($hookName, $args)
    {
        $errors = & $args[0];
        $props = $args[2];

        if (!empty($props['codeType'])) {
            /** @var ONIXCodelistItemDAO */
            $onixCodelistItemDao = DAORegistry::getDAO('ONIXCodelistItemDAO');
            if (!$onixCodelistItemDao->codeExistsInList($props['codeType'], '44')) {
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
     * @param Press $context
     * @param int $maxWidth The maximum width allowed for a cover image
     * @param int $maxHeight The maximum width allowed for a cover image
     */
    public function resizeCoverThumbnails($context, $maxWidth, $maxHeight)
    {
        $fileManager = new FileManager();
        $publicFileManager = new PublicFileManager();
        $contextFileManager = new ContextFileManager($context->getId());

        $objectDaos = [
            Repo::submission()->dao,
            Repo::section()->dao,
            Repo::submission()->dao,
        ];
        foreach ($objectDaos as $objectDao) {
            if ($objectDao instanceof \PKP\submission\DAO) {
                $objects = Repo::submission()
                    ->getCollector()
                    ->filterByContextIds([$context->getId()])
                    ->getMany()
                    ->toArray();
            } elseif ($objectDao instanceof \PKP\category\DAO) {
                $objects = Repo::category()->getCollector()
                    ->filterByContextIds([$context->getId()])
                    ->getMany()
                    ->toArray();
            } elseif ($objectDao instanceof \PKP\section\DAO) {
                $objects = Repo::section()
                    ->getCollector()
                    ->filterByContextIds([$context->getId()])
                    ->getMany()
                    ->toArray();
            } else {
                throw new \Exception('Unknown DAO ' . get_class($objectDao) . ' for cover image!');
            }

            foreach ($objects as $object) {
                if ($object instanceof Submission) {
                    foreach ($object->getData('publications') as $publication) {
                        foreach ((array) $publication->getData('coverImage') as $coverImage) {
                            $coverImageFilePath = $publicFileManager->getContextFilesPath($context->getId()) . '/' . $coverImage['uploadName'];
                            Repo::publication()->makeThumbnail(
                                $coverImageFilePath,
                                Repo::publication()->getThumbnailFileName($coverImage['uploadName']),
                                $maxWidth,
                                $maxHeight
                            );
                        }
                    }
                    continue;
                }

                // $object is a category or section
                $cover = $object->getImage();
                if ($object instanceof \APP\section\Section) {
                    $basePath = $contextFileManager->getBasePath() . 'series/';
                } elseif ($object instanceof \PKP\category\Category) {
                    $basePath = $contextFileManager->getBasePath() . 'categories/';
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
                        case 'jpg': $coverImage = imagecreatefromjpeg($basePath . $cover['name']);
                            break;
                        case 'png': $coverImage = imagecreatefrompng($basePath . $cover['name']);
                            break;
                        case 'gif': $coverImage = imagecreatefromgif($basePath . $cover['name']);
                            break;
                        default: $coverImage = null; // Suppress warn
                    }
                    assert($coverImage);

                    // copy the cover image to the thumbnail
                    imagecopyresampled($thumbnail, $coverImage, 0, 0, 0, 0, $thumbnailWidth, $thumbnailHeight, $cover['width'], $cover['height']);

                    // create the thumbnail file
                    switch ($coverExtension) {
                        case 'jpg': imagejpeg($thumbnail, $basePath . $cover['thumbnailName']);
                            break;
                        case 'png': imagepng($thumbnail, $basePath . $cover['thumbnailName']);
                            break;
                        case 'gif': imagegif($thumbnail, $basePath . $cover['thumbnailName']);
                            break;
                    }

                    imagedestroy($thumbnail);
                    if ($object instanceof Submission) {
                        $object->setData('coverImage', [
                            'name' => $cover['name'],
                            'width' => $cover['width'],
                            'height' => $cover['height'],
                            'thumbnailName' => $cover['thumbnailName'],
                            'thumbnailWidth' => $thumbnailWidth,
                            'thumbnailHeight' => $thumbnailHeight,
                            'uploadName' => $cover['uploadName'],
                            'dateUploaded' => $cover['dateUploaded'],
                        ]);
                    } else {
                        $object->setImage([
                            'name' => $cover['name'],
                            'width' => $cover['width'],
                            'height' => $cover['height'],
                            'thumbnailName' => $cover['thumbnailName'],
                            'thumbnailWidth' => $thumbnailWidth,
                            'thumbnailHeight' => $thumbnailHeight,
                            'uploadName' => $cover['uploadName'],
                            'dateUploaded' => $cover['dateUploaded'],
                        ]);
                    }
                    // Update category object to store new thumbnail information.
                    $objectDao->update($object);
                }
            }
        }
    }
}
